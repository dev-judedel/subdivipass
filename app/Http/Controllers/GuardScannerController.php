<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardIssueReportRequest;
use App\Http\Requests\GuardScanRequest;
use App\Http\Requests\GuardShiftRequest;
use App\Models\Gate;
use App\Models\GuardIssueReport;
use App\Models\GuardShift;
use App\Models\Pass;
use App\Models\PassScan;
use App\Models\WorkerPass;
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
        $scannedWorker = $result['scan_data']['payload']['scanned_worker'] ?? null;

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
                    [
                        'attempt_id' => $result['attempt_id'] ?? null,
                        'worker_id' => $scannedWorker?->id ?? null,
                        'worker_name' => $scannedWorker?->worker_name ?? null,
                    ]
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

                // If a worker QR was scanned, auto-admit that worker
                if ($scannedWorker && $scannedWorker->isActive()) {
                    $scannedWorker->admit($gate, $guard);
                }
            }
        }

        // Load workers relationship if this is a group/worker pass
        if ($pass && $pass->pass_mode === 'group') {
            $pass->load('workers');
        }

        // Customize message if worker was auto-admitted
        $message = $result['message'];
        if ($scannedWorker && $result['status'] === 'success') {
            $message = "Worker {$scannedWorker->worker_name} admitted successfully.";
        }

        return redirect()
            ->route('guard.scanner')
            ->with('scanResult', [
                'status' => $result['status'],
                'message' => $message,
                'scanned_worker_id' => $scannedWorker?->id,  // Highlight this worker in UI
                'pass' => $pass ? [
                    'id' => $pass->id,
                    'pass_number' => $pass->pass_number,
                    'visitor_name' => $pass->visitor_name,
                    'status' => $pass->status,
                    'valid_to' => $pass->valid_to,
                    'uuid' => $pass->uuid,
                    'pass_mode' => $pass->pass_mode,
                    'group_size' => $pass->group_size,
                    'workers' => $pass->pass_mode === 'group' ? $pass->workers->map(function ($worker) {
                        return [
                            'id' => $worker->id,
                            'worker_name' => $worker->worker_name,
                            'worker_contact' => $worker->worker_contact,
                            'worker_email' => $worker->worker_email,
                            'worker_position' => $worker->worker_position,
                            'worker_id_number' => $worker->worker_id_number,
                            'photo_path' => $worker->photo_path,
                            'photo_url' => $worker->photo_url,
                            'qr_code_path' => $worker->qr_code_path,
                            'qr_code_url' => $worker->qr_code_url,
                            'is_admitted' => $worker->is_admitted,
                            'last_scan_at' => $worker->last_scan_at,
                            'total_scans' => $worker->total_scans,
                            'status' => $worker->status,
                        ];
                    }) : [],
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

    public function admitWorker(Request $request, WorkerPass $worker): RedirectResponse
    {
        $guard = $request->user();

        $data = $request->validate([
            'gate_id' => ['required', 'exists:gates,id'],
            'scan_type' => ['nullable', 'in:entry,exit'],
        ]);

        $gate = Gate::where('status', 'active')->findOrFail($data['gate_id']);
        $this->ensureGateAssignment($gate->id, $guard->gate_ids);

        // Check if worker pass is active
        if (!$worker->isActive()) {
            return redirect()
                ->route('guard.scanner')
                ->with('scanResult', [
                    'status' => 'error',
                    'message' => 'Worker pass is not active or parent pass is invalid.',
                ]);
        }

        // Admit the worker
        $worker->admit($gate, $guard);

        // Create a pass scan record for this worker admission
        PassScan::create([
            'pass_id' => $worker->pass_id,
            'gate_id' => $gate->id,
            'guard_id' => $guard->id,
            'scan_type' => $data['scan_type'] ?? 'entry',
            'scan_method' => 'qr',
            'result' => 'success',
            'result_message' => "Worker {$worker->worker_name} admitted",
            'scan_data' => [
                'worker_id' => $worker->id,
                'worker_name' => $worker->worker_name,
            ],
            'device_id' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'location' => null,
            'was_offline' => false,
            'scanned_at' => now(),
        ]);

        // Load the parent pass with all workers to return updated state
        $pass = Pass::with('workers')->find($worker->pass_id);

        return redirect()
            ->route('guard.scanner')
            ->with('scanResult', [
                'status' => 'success',
                'message' => "Worker {$worker->worker_name} admitted successfully.",
                'pass' => [
                    'id' => $pass->id,
                    'pass_number' => $pass->pass_number,
                    'visitor_name' => $pass->visitor_name,
                    'status' => $pass->status,
                    'valid_to' => $pass->valid_to,
                    'uuid' => $pass->uuid,
                    'pass_mode' => $pass->pass_mode,
                    'group_size' => $pass->group_size,
                    'workers' => $pass->workers->map(function ($w) {
                        return [
                            'id' => $w->id,
                            'worker_name' => $w->worker_name,
                            'worker_contact' => $w->worker_contact,
                            'worker_email' => $w->worker_email,
                            'worker_position' => $w->worker_position,
                            'worker_id_number' => $w->worker_id_number,
                            'photo_path' => $w->photo_path,
                            'photo_url' => $w->photo_url,
                            'qr_code_path' => $w->qr_code_path,
                            'qr_code_url' => $w->qr_code_url,
                            'is_admitted' => $w->is_admitted,
                            'last_scan_at' => $w->last_scan_at,
                            'total_scans' => $w->total_scans,
                            'status' => $w->status,
                        ];
                    }),
                ],
                'gate' => [
                    'id' => $gate->id,
                    'name' => $gate->name,
                ],
            ]);
    }

    public function exitWorker(Request $request, WorkerPass $worker): RedirectResponse
    {
        $guard = $request->user();

        $data = $request->validate([
            'gate_id' => ['required', 'exists:gates,id'],
        ]);

        $gate = Gate::where('status', 'active')->findOrFail($data['gate_id']);
        $this->ensureGateAssignment($gate->id, $guard->gate_ids);

        // Mark worker as exited
        $worker->exit();

        // Create a pass scan record for this worker exit
        PassScan::create([
            'pass_id' => $worker->pass_id,
            'gate_id' => $gate->id,
            'guard_id' => $guard->id,
            'scan_type' => 'exit',
            'scan_method' => 'manual',
            'result' => 'success',
            'result_message' => "Worker {$worker->worker_name} exited",
            'scan_data' => [
                'worker_id' => $worker->id,
                'worker_name' => $worker->worker_name,
            ],
            'device_id' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'location' => null,
            'was_offline' => false,
            'scanned_at' => now(),
        ]);

        // Load the parent pass with all workers to return updated state
        $pass = Pass::with('workers')->find($worker->pass_id);

        return redirect()
            ->route('guard.scanner')
            ->with('scanResult', [
                'status' => 'success',
                'message' => "Worker {$worker->worker_name} exited successfully.",
                'pass' => [
                    'id' => $pass->id,
                    'pass_number' => $pass->pass_number,
                    'visitor_name' => $pass->visitor_name,
                    'status' => $pass->status,
                    'valid_to' => $pass->valid_to,
                    'uuid' => $pass->uuid,
                    'pass_mode' => $pass->pass_mode,
                    'group_size' => $pass->group_size,
                    'workers' => $pass->workers->map(function ($w) {
                        return [
                            'id' => $w->id,
                            'worker_name' => $w->worker_name,
                            'worker_contact' => $w->worker_contact,
                            'worker_email' => $w->worker_email,
                            'worker_position' => $w->worker_position,
                            'worker_id_number' => $w->worker_id_number,
                            'photo_path' => $w->photo_path,
                            'photo_url' => $w->photo_url,
                            'qr_code_path' => $w->qr_code_path,
                            'qr_code_url' => $w->qr_code_url,
                            'is_admitted' => $w->is_admitted,
                            'last_scan_at' => $w->last_scan_at,
                            'total_scans' => $w->total_scans,
                            'status' => $w->status,
                        ];
                    }),
                ],
                'gate' => [
                    'id' => $gate->id,
                    'name' => $gate->name,
                ],
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
