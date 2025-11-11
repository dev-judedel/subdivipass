<?php

namespace App\Http\Controllers;

use App\Models\Gate;
use App\Models\GuardIssueReport;
use App\Models\GuardShift;
use App\Models\Pass;
use App\Models\PassLog;
use App\Models\PassScan;
use App\Models\Subdivision;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [
            'total_passes' => Pass::count(),
            'active_passes' => Pass::where('status', 'active')->count(),
            'pending_approvals' => Pass::where('status', 'pending')->count(),
            'expiring_today' => Pass::whereIn('status', ['approved', 'active'])
                ->whereBetween('valid_to', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
            'open_guard_issues' => GuardIssueReport::where('status', 'open')->count(),
            'active_guard_shifts' => GuardShift::active()->count(),
            'today_scans' => PassScan::whereDate('scanned_at', today())->count(),
            'today_successful_scans' => PassScan::whereDate('scanned_at', today())
                ->where('result', 'success')
                ->count(),
            'today_failed_scans' => PassScan::whereDate('scanned_at', today())
                ->where('result', 'error')
                ->count(),
        ];

        $pendingApprovals = Pass::with(['requester:id,name', 'type:id,name', 'subdivision:id,name'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($pass) => [
                'id' => $pass->id,
                'pass_number' => $pass->pass_number,
                'visitor_name' => $pass->visitor_name,
                'type' => $pass->type?->name,
                'subdivision' => $pass->subdivision?->name,
                'requested_by' => $pass->requester?->name,
                'created_at' => $pass->created_at?->toDateTimeString(),
            ]);

        $recentActivity = PassLog::with('pass:id,pass_number')
            ->latest('logged_at')
            ->limit(6)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'pass_number' => $log->pass?->pass_number,
                'action' => $log->action,
                'description' => $log->description,
                'logged_at' => $log->logged_at?->diffForHumans(),
            ]);

        $passesByDay = Pass::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                return [
                    'day' => Carbon::parse($row->day)->format('M d'),
                    'total' => (int) $row->total,
                ];
            });

        $guardAlerts = GuardIssueReport::latest()
            ->limit(5)
            ->get(['id', 'issue_type', 'severity', 'status', 'created_at'])
            ->map(fn ($issue) => [
                'id' => $issue->id,
                'type' => $issue->issue_type,
                'severity' => $issue->severity,
                'status' => $issue->status,
                'created_at' => $issue->created_at?->diffForHumans(),
            ]);

        // Subdivision breakdown
        $subdivisionStats = Subdivision::withCount([
            'passes as total_passes',
            'passes as active_passes' => fn ($q) => $q->where('status', 'active'),
        ])->limit(5)->get()->map(fn ($subdivision) => [
            'name' => $subdivision->name,
            'total_passes' => $subdivision->total_passes,
            'active_passes' => $subdivision->active_passes,
        ]);

        // Today's scan activity by hour
        $todayScans = PassScan::whereDate('scanned_at', today())
            ->selectRaw('HOUR(scanned_at) as hour, COUNT(*) as total, result')
            ->groupBy('hour', 'result')
            ->orderBy('hour')
            ->get()
            ->groupBy('hour')
            ->map(function ($hourScans, $hour) {
                return [
                    'hour' => Carbon::createFromTime($hour)->format('ha'),
                    'successful' => $hourScans->where('result', 'success')->sum('total'),
                    'failed' => $hourScans->where('result', 'error')->sum('total'),
                    'warning' => $hourScans->where('result', 'warning')->sum('total'),
                    'total' => $hourScans->sum('total'),
                ];
            })->values();

        // Active guards on duty
        $activeGuards = GuardShift::with(['guardUser:id,name', 'gate:id,name'])
            ->active()
            ->get()
            ->map(fn ($shift) => [
                'guard_name' => $shift->guardUser?->name,
                'gate_name' => $shift->gate?->name,
                'started_at' => $shift->started_at?->diffForHumans(),
                'duration' => $shift->started_at?->diffInHours(now()) . ' hours',
            ]);

        // Pass type distribution
        $passByType = Pass::selectRaw('pass_types.name as type_name, COUNT(*) as count')
            ->join('pass_types', 'passes.pass_type_id', '=', 'pass_types.id')
            ->where('passes.status', 'active')
            ->groupBy('pass_types.name')
            ->get()
            ->map(fn ($item) => [
                'type' => $item->type_name,
                'count' => $item->count,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'pendingApprovals' => $pendingApprovals,
            'recentActivity' => $recentActivity,
            'passesByDay' => $passesByDay,
            'guardAlerts' => $guardAlerts,
            'subdivisionStats' => $subdivisionStats,
            'todayScans' => $todayScans,
            'activeGuards' => $activeGuards,
            'passByType' => $passByType,
        ]);
    }
}
