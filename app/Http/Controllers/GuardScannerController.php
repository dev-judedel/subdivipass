<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardScanRequest;
use App\Models\Gate;
use App\Models\Pass;
use App\Models\PassScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuardScannerController extends Controller
{
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
            ])
            ->where('guard_id', $guard->id)
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        return Inertia::render('Guard/Scanner', [
            'gates' => $gates,
            'defaultGateId' => $gates->first()->id ?? null,
            'recentScans' => $recentScans,
        ]);
    }

    public function store(GuardScanRequest $request): RedirectResponse
    {
        $guard = $request->user();
        $data = $request->validated();

        $gate = Gate::where('status', 'active')->findOrFail($data['gate_id']);
        $this->ensureGateAssignment($gate->id, $guard->gate_ids);

        $pass = $this->findPass($data['method'], $data['code']);
        if (!$pass) {
            return redirect()
                ->route('guard.scanner')
                ->with('scanResult', [
                    'status' => 'error',
                    'message' => 'Pass not found. Please verify the code or PIN.',
                ]);
        }

        $evaluation = $this->evaluatePass($pass, $gate);

        PassScan::create([
            'pass_id' => $pass->id,
            'gate_id' => $gate->id,
            'guard_id' => $guard->id,
            'scan_type' => $data['scan_type'] ?? 'entry',
            'scan_method' => $data['method'],
            'result' => $this->mapResult($evaluation['status']),
            'result_message' => $evaluation['message'],
            'scan_data' => [
                'input' => $data['code'],
            ],
            'device_id' => $data['device_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'location' => null,
            'was_offline' => false,
            'scanned_at' => now(),
        ]);

        if ($evaluation['status'] !== 'error') {
            $pass->recordScan($gate, $guard);
        }

        return redirect()
            ->route('guard.scanner')
            ->with('scanResult', array_merge($evaluation, [
                'pass' => [
                    'id' => $pass->id,
                    'pass_number' => $pass->pass_number,
                    'visitor_name' => $pass->visitor_name,
                    'status' => $pass->status,
                    'valid_to' => $pass->valid_to,
                ],
                'gate' => [
                    'id' => $gate->id,
                    'name' => $gate->name,
                ],
            ]));
    }

    protected function findPass(string $method, string $code): ?Pass
    {
        return match ($method) {
            'pin' => Pass::where('pin', $code)->first(),
            'pass_number' => Pass::where('pass_number', $code)->first(),
            default => Pass::where('uuid', $code)
                ->orWhere('pass_number', $code)
                ->first(),
        };
    }

    protected function evaluatePass(Pass $pass, Gate $gate): array
    {
        if ($pass->status === 'revoked') {
            return [
                'status' => 'error',
                'message' => 'This pass has been revoked.',
            ];
        }

        if ($pass->isExpired()) {
            return [
                'status' => 'error',
                'message' => 'Pass is expired.',
            ];
        }

        if (!$pass->isApproved()) {
            return [
                'status' => 'error',
                'message' => 'Pass is not approved or active yet.',
            ];
        }

        if ($pass->subdivision_id !== $gate->subdivision_id) {
            return [
                'status' => 'warning',
                'message' => 'Pass belongs to a different subdivision.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Pass validated successfully.',
        ];
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
}
