<?php

namespace App\Http\Controllers;

use App\Models\GuardIssueReport;
use App\Models\GuardShift;
use App\Models\Pass;
use App\Models\PassLog;
use App\Models\PassScan;
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

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'pendingApprovals' => $pendingApprovals,
            'recentActivity' => $recentActivity,
            'passesByDay' => $passesByDay,
            'guardAlerts' => $guardAlerts,
        ]);
    }
}
