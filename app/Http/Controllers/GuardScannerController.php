<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardIssueReportRequest;
use App\Http\Requests\GuardScanRequest;
use App\Http\Requests\GuardShiftRequest;
use App\Models\Gate;
use App\Models\GuardIssueReport;
use App\Models\GuardShift;
use App\Models\PassScan;
use App\Services\PassService;
use App\Services\ValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuardScannerController extends Controller
{
    public function __construct(
        private PassService $passService,
        private ValidationService $validationService
    )
    {
    }

    public function index(Request $request): Response
    {
        $guard = $request->user();
        $gateIds = $this->parseIds($guard->gate_ids);

        $gates = Gate::query()
            ->when(!empty($gateIds), fn ($query) => $query->whereIn('id', $gateIds))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $recentScans = PassScan::with([
                'pass:id,pass_number,visitor_name,status,valid_to',
                'gate:id,name',
                'scannedBy:id,name',
            ])
            ->where('guard_id', $guard->id)
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        $currentShift = GuardShift::with('gate:id,name')
            ->where('guard_id', $guard->id)
            ->active()
            ->latest('started_at')
            ->first();

        $stats = $this->buildStats($guard->id);

        return Inertia::render('Guard/Scanner', [
            'gates' => $gates,
            'defaultGateId' => $gates->first()->id ?? null,
            'recentScans' => $recentScans,
            'currentShift' => $currentShift,
            'issueTypes' => $this->issueTypes(),
            'issueSeverities' => ['low', 'medium', 'high'],
            'stats' => $stats,
            'canApprove' => $guard->can('approve passes'),
            'canReject' => $guard->can('reject passes'),
        ]);
    }

    public function store(GuardScanRequest $request): RedirectResponse
    {
        $guard = $request->user();
        $data = $request->validated();

        $gate = Gate::where('status', 'active')->findOrFail($data['gate_id']);
        $this->ensureGateAssignment($gate->id, $guard->gate_ids);

        $wasOffline = filter_var($request->input('was_offline', false), FILTER_VALIDATE_BOOLEAN);

        try {
            $result = $this->validationService->validate([
                'method' => $data['method'],
                'code' => $data['code'],
                'gate' => $gate,
                'guard' => $guard,
                'scan_type' => $data['scan_type'] ?? 'entry',
                'device_id' => $data['device_id'] ?? null,
                'was_offline' => $wasOffline,
                'ip' => $request->ip(),
            ]);
        } catch (\Throwable $exception) {
            $message = 'Unable to validate pass at the moment.';

            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                $message = collect($exception->errors())->flatten()->first() ?? $message;
            }

            return redirect()
                ->route('guard.scanner')
                ->with('scanResult', [
                    'status' => 'error',
                    'message' => $message,
                ]);
        }

        $pass = $result['pass'] ?? null;

        if ($pass) {
            PassScan::create([
                'pass_id' => $pass->id,
                'gate_id' => $gate->id,
                'guard_id' => $guard->id,
                'scan_type' => $data['scan_type'] ?? 'entry',
                'scan_method' => $data['method'],
                'result' => $this->mapResult($result['status']),
                'result_message' => $result['message'],
                'scan_data' => array_merge(
                    $result['scan_data'] ?? [],
                    ['attempt_id' => $result['attempt_id'] ?? null]
                ),
                'device_id' => $data['device_id'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'location' => null,
                'was_offline' => $wasOffline,
                'scanned_at' => now(),
            ]);

            if ($result['status'] === 'success') {
                $pass->recordScan($gate, $guard);
            }
        }

        return redirect()
            ->route('guard.scanner')
            ->with('scanResult', [
                'status' => $result['status'],
                'message' => $result['message'],
                'pass' => $pass ? [
                    'id' => $pass->id,
                    'pass_number' => $pass->pass_number,
                    'visitor_name' => $pass->visitor_name,
                    'status' => $pass->status,
                    'valid_to' => $pass->valid_to,
                    'uuid' => $pass->uuid,
                ] : null,
                'gate' => [
                    'id' => $gate->id,
                    'name' => $gate->name,
                ],
                'input_code' => $data['code'],
            ]);
    }

    public function validatePin(GuardScanRequest $request): RedirectResponse
    {
        return $this->store($request);
    }

    public function startShift(GuardShiftRequest $request): RedirectResponse
    {
        $guard = $request->user();

        $existing = GuardShift::where('guard_id', $guard->id)
            ->active()
            ->first();

        if ($existing) {
            return redirect()
                ->route('guard.scanner')
                ->with('shiftStatus', [
                    'status' => 'warning',
                    'message' => 'You already have an active shift.',
                ]);
        }

        GuardShift::create([
            'guard_id' => $guard->id,
            'gate_id' => $request->gate_id,
            'notes' => $request->notes,
            'status' => 'active',
            'started_at' => now(),
        ]);

        return redirect()
            ->route('guard.scanner')
            ->with('shiftStatus', [
                'status' => 'success',
                'message' => 'Shift started.',
            ]);
    }

    public function endShift(Request $request): RedirectResponse
    {
        $guard = $request->user();

        $shift = GuardShift::where('guard_id', $guard->id)
            ->active()
            ->latest('started_at')
            ->first();

        if (!$shift) {
            return redirect()
                ->route('guard.scanner')
                ->with('shiftStatus', [
                    'status' => 'error',
                    'message' => 'No active shift found.',
                ]);
        }

        $shift->update([
            'status' => 'completed',
            'ended_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        return redirect()
            ->route('guard.scanner')
            ->with('shiftStatus', [
                'status' => 'success',
                'message' => 'Shift ended.',
            ]);
    }

    public function reportIssue(GuardIssueReportRequest $request): RedirectResponse
    {
        $guard = $request->user();
        $data = $request->validated();

        GuardIssueReport::create([
            'guard_id' => $guard->id,
            'gate_id' => $data['gate_id'] ?? null,
            'pass_id' => $this->resolvePassId($data['pass_code'] ?? null),
            'issue_type' => $data['issue_type'],
            'severity' => $data['severity'],
            'description' => $data['description'],
            'status' => 'open',
        ]);

        return redirect()
            ->route('guard.scanner')
            ->with('issueStatus', [
                'status' => 'success',
                'message' => 'Issue reported. Security will review shortly.',
            ]);
    }

    public function approvePass(Request $request, Pass $pass): RedirectResponse
    {
        $guard = $request->user();
        abort_unless($guard->can('approve passes'), 403);

        $this->passService->approvePass($pass, $guard);

        return redirect()
            ->route('guard.scanner')
            ->with('passActionStatus', [
                'status' => 'success',
                'message' => 'Pass approved successfully.',
            ]);
    }

    public function rejectPass(Request $request, Pass $pass): RedirectResponse
    {
        $guard = $request->user();
        abort_unless($guard->can('reject passes'), 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->passService->rejectPass($pass, $guard, $data['reason']);

        return redirect()
            ->route('guard.scanner')
            ->with('passActionStatus', [
                'status' => 'warning',
                'message' => 'Pass rejected.',
            ]);
    }

    protected function ensureGateAssignment(int $gateId, $assignedGateIds): void
    {
        $ids = $this->parseIds($assignedGateIds);
        if (!empty($ids) && !in_array($gateId, $ids)) {
            abort(403, 'You are not assigned to this gate.');
        }
    }

    protected function parseIds($value): array
    {
        if (is_array($value)) {
            return array_map('intval', $value);
        }

        if (is_string($value)) {
            return array_map('intval', json_decode($value, true) ?? []);
        }

        return [];
    }

    protected function mapResult(string $status): string
    {
        return match ($status) {
            'warning' => 'warning',
            'error' => 'failed',
            default => 'success',
        };
    }

    protected function issueTypes(): array
    {
        return [
            'suspicious_activity',
            'unauthorized_entry',
            'equipment_issue',
            'medical_assistance',
            'other',
        ];
    }

    protected function buildStats(int $guardId): array
    {
        $totals = PassScan::selectRaw('result, COUNT(*) as total')
            ->where('guard_id', $guardId)
            ->whereDate('scanned_at', today())
            ->groupBy('result')
            ->pluck('total', 'result')
            ->toArray();

        $total = array_sum($totals);

        return [
            'total_today' => $total,
            'success' => $totals['success'] ?? 0,
            'warnings' => $totals['warning'] ?? 0,
            'failed' => ($totals['failed'] ?? 0) + ($totals['error'] ?? 0),
        ];
    }
}
