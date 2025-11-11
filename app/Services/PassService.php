<?php

namespace App\Services;

use App\Models\Pass;
use App\Models\PassLog;
use App\Models\PassType;
use App\Models\User;
use App\Notifications\PassStatusChanged;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PassService
{
    protected $qrService;

    public function __construct(QRService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Create a new pass.
     *
     * @param array $data
     * @param User $requester
     * @return Pass
     * @throws \Exception
     */
    public function createPass(array $data, User $requester): Pass
    {
        return DB::transaction(function () use ($data, $requester) {
            // Get pass type
            $passType = PassType::findOrFail($data['pass_type_id']);

            // Calculate validity period
            $validFrom = isset($data['valid_from'])
                ? Carbon::parse($data['valid_from'])
                : now();

            $validTo = isset($data['valid_to'])
                ? Carbon::parse($data['valid_to'])
                : $validFrom->copy()->addHours($passType->default_validity_hours);

            // Determine initial status
            $status = $passType->requiresApproval() ? 'pending' : 'approved';

            // Create pass
            $pass = Pass::create([
                'subdivision_id' => $data['subdivision_id'],
                'pass_type_id' => $data['pass_type_id'],
                'requester_id' => $requester->id,
                'visitor_name' => $data['visitor_name'],
                'visitor_contact' => $data['visitor_contact'] ?? null,
                'visitor_email' => $data['visitor_email'] ?? null,
                'visitor_company' => $data['visitor_company'] ?? null,
                'vehicle_plate' => $data['vehicle_plate'] ?? null,
                'vehicle_model' => $data['vehicle_model'] ?? null,
                'purpose' => $data['purpose'],
                'destination' => $data['destination'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'valid_from' => $validFrom,
                'valid_to' => $validTo,
                'status' => $status,
            ]);

            // Generate QR code
            $qrPath = $this->qrService->generateQRCode($pass);

            // Update pass with QR code path
            $pass->update([
                'qr_code_path' => $qrPath,
            ]);

            // If auto-approved and within validity, activate it
            if ($status === 'approved' && $pass->isValid()) {
                $pass->activate();
            }

            // Log activity
            activity()
                ->performedOn($pass)
                ->causedBy($requester)
                ->log('Pass created');

            return $pass->fresh(['type', 'subdivision', 'requester']);
        });
    }

    /**
     * Update a pass.
     *
     * @param Pass $pass
     * @param array $data
     * @return Pass
     */
    public function updatePass(Pass $pass, array $data): Pass
    {
        return DB::transaction(function () use ($pass, $data) {
            $pass->update([
                'visitor_name' => $data['visitor_name'] ?? $pass->visitor_name,
                'visitor_contact' => $data['visitor_contact'] ?? $pass->visitor_contact,
                'visitor_email' => $data['visitor_email'] ?? $pass->visitor_email,
                'visitor_company' => $data['visitor_company'] ?? $pass->visitor_company,
                'vehicle_plate' => $data['vehicle_plate'] ?? $pass->vehicle_plate,
                'vehicle_model' => $data['vehicle_model'] ?? $pass->vehicle_model,
                'purpose' => $data['purpose'] ?? $pass->purpose,
                'destination' => $data['destination'] ?? $pass->destination,
                'notes' => $data['notes'] ?? $pass->notes,
                'metadata' => $data['metadata'] ?? $pass->metadata,
            ]);

            // If validity period changed, regenerate QR code
            if (isset($data['valid_from']) || isset($data['valid_to'])) {
                if (isset($data['valid_from'])) {
                    $pass->valid_from = Carbon::parse($data['valid_from']);
                }
                if (isset($data['valid_to'])) {
                    $pass->valid_to = Carbon::parse($data['valid_to']);
                }
                $pass->save();

                // Regenerate QR code
                $qrPath = $this->qrService->regenerateQRCode($pass);
                $pass->update(['qr_code_path' => $qrPath]);
            }

            return $pass->fresh(['type', 'subdivision', 'requester']);
        });
    }

    /**
     * Approve a pass.
     *
     * @param Pass $pass
     * @param User $approver
     * @return Pass
     */
    public function approvePass(Pass $pass, User $approver): Pass
    {
        if (!$pass->isPending()) {
            throw new \Exception('Only pending passes can be approved.');
        }

        $previousStatus = $pass->status;

        $pass->approve($approver);

        // Activate if within validity period
        if ($pass->isValid()) {
            $pass->activate();
        }

        // Log activity
        activity()
            ->performedOn($pass)
            ->causedBy($approver)
            ->log('Pass approved');

        $this->recordStatusChange($pass, $previousStatus, $pass->status, $approver, 'Pass approved.');
        $this->notifyStatusChange($pass, $previousStatus, $pass->status, 'Pass approved.');

        return $pass->fresh();
    }

    /**
     * Reject a pass.
     *
     * @param Pass $pass
     * @param User $approver
     * @param string|null $reason
     * @return Pass
     */
    public function rejectPass(Pass $pass, User $approver, string $reason = null): Pass
    {
        if (!$pass->isPending()) {
            throw new \Exception('Only pending passes can be rejected.');
        }

        $previousStatus = $pass->status;

        $pass->reject($approver, $reason);

        // Log activity
        activity()
            ->performedOn($pass)
            ->causedBy($approver)
            ->withProperties(['reason' => $reason])
            ->log('Pass rejected');

        $this->recordStatusChange($pass, $previousStatus, $pass->status, $approver, $reason ?? 'Pass rejected.');
        $this->notifyStatusChange($pass, $previousStatus, $pass->status, $reason ?? 'Pass rejected.');

        return $pass->fresh();
    }

    /**
     * Revoke a pass.
     *
     * @param Pass $pass
     * @param string|null $reason
     * @return Pass
     */
    public function revokePass(Pass $pass, ?User $actor = null, string $reason = null): Pass
    {
        $previousStatus = $pass->status;

        $pass->revoke($reason);

        // Log activity
        activity()
            ->performedOn($pass)
            ->causedBy($actor)
            ->withProperties(['reason' => $reason])
            ->log('Pass revoked');

        $this->recordStatusChange($pass, $previousStatus, $pass->status, $actor, $reason ?? 'Pass revoked.');
        $this->notifyStatusChange($pass, $previousStatus, $pass->status, $reason ?? 'Pass revoked.');

        return $pass->fresh();
    }

    public function terminatePass(Pass $pass, ?User $actor = null, string $reason = null): Pass
    {
        return $this->revokePass($pass, $actor, $reason ?? 'Pass terminated early.');
    }

    /**
     * Extend pass validity.
     *
     * @param Pass $pass
     * @param Carbon $newValidTo
     * @param PassType|null $passType
     * @return Pass
     */
    public function extendPass(Pass $pass, Carbon $newValidTo, PassType $passType = null, ?User $actor = null): Pass
    {
        $passType = $passType ?? $pass->type;

        // Check if extension is allowed
        if ($passType->max_validity_hours) {
            $maxValidTo = $pass->valid_from->copy()->addHours($passType->max_validity_hours);
            if ($newValidTo->greaterThan($maxValidTo)) {
                throw new \Exception('Extension exceeds maximum validity period.');
            }
        }

        $oldValidTo = $pass->valid_to ? $pass->valid_to->toDateTimeString() : null;

        $pass->update(['valid_to' => $newValidTo]);

        // Regenerate QR code
        $qrPath = $this->qrService->regenerateQRCode($pass);
        $pass->update(['qr_code_path' => $qrPath]);

        activity()
            ->performedOn($pass)
            ->causedBy($actor)
            ->withProperties(['new_valid_to' => $newValidTo->toDateTimeString()])
            ->log('Pass extended');

        $this->createPassLog(
            $pass,
            'extended',
            $actor,
            'Pass validity extended.',
            ['valid_to' => $oldValidTo],
            ['valid_to' => $newValidTo->toDateTimeString()],
            []
        );

        return $pass->fresh();
    }

    /**
     * Validate pass by PIN.
     *
     * @param string $pin
     * @param int $subdivisionId
     * @return Pass|null
     */
    public function validateByPIN(string $pin, int $subdivisionId): ?Pass
    {
        return Pass::where('pin', $pin)
            ->where('subdivision_id', $subdivisionId)
            ->where('status', 'active')
            ->whereDate('valid_from', '<=', now())
            ->whereDate('valid_to', '>=', now())
            ->first();
    }

    /**
     * Validate pass by QR data.
     *
     * @param array $qrData
     * @return Pass|null
     */
    public function validateByQR(array $qrData): ?Pass
    {
        // Verify signature
        if (!$this->qrService->verifySignature($qrData)) {
            return null;
        }

        // Find pass by UUID
        $pass = Pass::where('uuid', $qrData['pass_id'])
            ->where('status', 'active')
            ->first();

        return $pass && $pass->isActive() ? $pass : null;
    }

    /**
     * Get pass statistics for a subdivision.
     *
     * @param int $subdivisionId
     * @return array
     */
    public function getStatistics(int $subdivisionId): array
    {
        return [
            'total' => Pass::where('subdivision_id', $subdivisionId)->count(),
            'active' => Pass::where('subdivision_id', $subdivisionId)
                ->where('status', 'active')
                ->count(),
            'pending' => Pass::where('subdivision_id', $subdivisionId)
                ->where('status', 'pending')
                ->count(),
            'expired' => Pass::where('subdivision_id', $subdivisionId)
                ->where('status', 'expired')
                ->count(),
            'today' => Pass::where('subdivision_id', $subdivisionId)
                ->whereDate('created_at', today())
                ->count(),
        ];
    }

    public function recordStatusChange(
        Pass $pass,
        string $fromStatus,
        string $toStatus,
        ?User $actor = null,
        ?string $message = null,
        array $meta = []
    ): void {
        $description = $message ?? "Status changed from {$fromStatus} to {$toStatus}.";

        $this->createPassLog(
            $pass,
            'status_changed',
            $actor,
            $description,
            ['status' => $fromStatus],
            ['status' => $toStatus],
            $meta
        );
    }

    public function notifyStatusChange(
        Pass $pass,
        string $fromStatus,
        string $toStatus,
        ?string $message = null
    ): void {
        $recipient = $pass->requester;

        if ($recipient) {
            $recipient->notify(
                new PassStatusChanged(
                    $pass->fresh(),
                    $fromStatus,
                    $toStatus,
                    $message
                )
            );
        }
    }

    protected function createPassLog(
        Pass $pass,
        string $action,
        ?User $actor = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        array $meta = []
    ): void {
        PassLog::create([
            'pass_id' => $pass->id,
            'user_id' => $actor?->id,
            'gate_id' => $meta['gate_id'] ?? null,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'metadata' => $meta ?: null,
            'ip_address' => $meta['ip_address'] ?? null,
            'user_agent' => $meta['user_agent'] ?? null,
            'logged_at' => now(),
        ]);
    }
}
