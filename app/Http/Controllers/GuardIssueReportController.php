<?php

namespace App\Http\Controllers;

use App\Models\GuardIssueReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuardIssueReportController extends Controller
{
    public function index(Request $request): Response
    {
        $query = GuardIssueReport::with(['guardUser:id,name', 'gate:id,name', 'pass:id,pass_number,visitor_name'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('guardUser', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('pass', fn ($sub) => $sub->where('pass_number', 'like', "%{$search}%"));
            });
        }

        $issues = $query->paginate(15)->withQueryString();

        $stats = [
            'open' => GuardIssueReport::where('status', 'open')->count(),
            'in_progress' => GuardIssueReport::where('status', 'in_progress')->count(),
            'resolved' => GuardIssueReport::where('status', 'resolved')->count(),
        ];

        return Inertia::render('GuardIssues/Index', [
            'issues' => $issues,
            'filters' => $request->only(['status', 'severity', 'search']),
            'stats' => $stats,
        ]);
    }

    public function show(GuardIssueReport $guardIssue): Response
    {
        $issue = $guardIssue->load(['guardUser:id,name,email', 'gate:id,name', 'pass:id,pass_number,visitor_name,status']);

        return Inertia::render('GuardIssues/Show', [
            'issue' => $issue,
            'statuses' => ['open', 'in_progress', 'resolved'],
            'severities' => ['low', 'medium', 'high'],
        ]);
    }

    public function update(Request $request, GuardIssueReport $guardIssue): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $guardIssue->status = $data['status'];
        $guardIssue->resolution_notes = $data['resolution_notes'] ?? null;
        $guardIssue->resolved_at = $data['status'] === 'resolved' ? now() : null;
        $guardIssue->save();

        return redirect()
            ->route('guard-issues.show', $guardIssue)
            ->with('success', 'Issue updated successfully.');
    }
}
