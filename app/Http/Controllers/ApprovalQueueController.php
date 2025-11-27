<?php

namespace App\Http\Controllers;

use App\Models\Pass;
use App\Services\PassService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovalQueueController extends Controller
{
    protected PassService $passService;

    public function __construct(PassService $passService)
    {
        $this->middleware(['auth', 'role:admin|super-admin']);
        $this->passService = $passService;
    }

    /**
     * Display the approval queue dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Build query for pending passes
        $query = Pass::with(['subdivision', 'type', 'requester'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc');

        // Filter by subdivision for non-super-admins
        if (!$user->hasRole('super-admin')) {
            $subdivisionIds = json_decode($user->subdivision_ids ?? '[]', true);
            $query->whereIn('subdivision_id', $subdivisionIds);
        }

        // Optional filters
        if ($request->filled('subdivision_id')) {
            $query->where('subdivision_id', $request->subdivision_id);
        }

        if ($request->filled('pass_type_id')) {
            $query->where('pass_type_id', $request->pass_type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pass_number', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('visitor_contact', 'like', "%{$search}%");
            });
        }

        $pendingPasses = $query->paginate(20)->withQueryString();

        // Get counts for stats
        $stats = [
            'total_pending' => Pass::where('status', 'pending')->count(),
            'urgent' => Pass::where('status', 'pending')
                ->whereDate('valid_from', '<=', now()->addDay())
                ->count(),
            'today' => Pass::where('status', 'pending')
                ->whereDate('created_at', today())
                ->count(),
        ];

        return Inertia::render('ApprovalQueue/Index', [
            'passes' => $pendingPasses,
            'stats' => $stats,
            'filters' => $request->only(['subdivision_id', 'pass_type_id', 'search']),
        ]);
    }

    /**
     * Approve multiple passes at once.
     */
    public function batchApprove(Request $request)
    {
        $request->validate([
            'pass_ids' => ['required', 'array'],
            'pass_ids.*' => ['exists:passes,id'],
        ]);

        $approved = 0;
        $failed = [];

        foreach ($request->pass_ids as $passId) {
            try {
                $pass = Pass::findOrFail($passId);
                $this->passService->approve($pass, $request->user());
                $approved++;
            } catch (\Exception $e) {
                $failed[] = $passId;
            }
        }

        $message = $approved > 0
            ? "Successfully approved {$approved} pass(es)."
            : "No passes were approved.";

        if (!empty($failed)) {
            $message .= " Failed to approve " . count($failed) . " pass(es).";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Reject multiple passes at once.
     */
    public function batchReject(Request $request)
    {
        $request->validate([
            'pass_ids' => ['required', 'array'],
            'pass_ids.*' => ['exists:passes,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $rejected = 0;
        $failed = [];

        foreach ($request->pass_ids as $passId) {
            try {
                $pass = Pass::findOrFail($passId);
                $this->passService->reject($pass, $request->user(), $request->reason);
                $rejected++;
            } catch (\Exception $e) {
                $failed[] = $passId;
            }
        }

        $message = $rejected > 0
            ? "Successfully rejected {$rejected} pass(es)."
            : "No passes were rejected.";

        if (!empty($failed)) {
            $message .= " Failed to reject " . count($failed) . " pass(es).";
        }

        return redirect()->back()->with('success', $message);
    }
}
