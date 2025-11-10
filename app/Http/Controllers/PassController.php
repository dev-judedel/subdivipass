<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePassRequest;
use App\Http\Requests\UpdatePassRequest;
use App\Models\Pass;
use App\Models\PassType;
use App\Models\Subdivision;
use App\Services\PassService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PassController extends Controller
{
    protected $passService;

    public function __construct(PassService $passService)
    {
        $this->passService = $passService;
    }

    /**
     * Display a listing of passes.
     */
    public function index(Request $request)
    {
        $query = Pass::with(['type', 'subdivision', 'requester', 'approver'])
            ->latest();

        // Filter by subdivision if user is not super-admin
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $subdivisionIds = json_decode($user->subdivision_ids, true) ?? [];
            $query->whereIn('subdivision_id', $subdivisionIds);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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
                    ->orWhere('visitor_contact', 'like', "%{$search}%")
                    ->orWhere('pin', 'like', "%{$search}%");
            });
        }

        $passes = $query->paginate(15)->withQueryString();

        // Get subdivisions and pass types for filters
        $subdivisions = Subdivision::where('status', 'active')->get();
        $passTypes = PassType::where('is_active', true)->get();

        return Inertia::render('Passes/Index', [
            'passes' => $passes,
            'subdivisions' => $subdivisions,
            'passTypes' => $passTypes,
            'filters' => $request->only(['status', 'subdivision_id', 'pass_type_id', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new pass.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $subdivisionIds = json_decode($user->subdivision_ids, true) ?? [];

        $subdivisions = Subdivision::where('status', 'active')
            ->when(!$user->hasRole('super-admin'), function ($query) use ($subdivisionIds) {
                $query->whereIn('id', $subdivisionIds);
            })
            ->get();

        $passTypes = PassType::where('is_active', true)
            ->with('subdivision')
            ->get();

        return Inertia::render('Passes/Create', [
            'subdivisions' => $subdivisions,
            'passTypes' => $passTypes,
        ]);
    }

    /**
     * Store a newly created pass.
     */
    public function store(StorePassRequest $request)
    {
        try {
            $pass = $this->passService->createPass(
                $request->validated(),
                $request->user()
            );

            return redirect()->route('passes.show', $pass)
                ->with('success', 'Pass created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified pass.
     */
    public function show(Pass $pass)
    {
        $this->authorize('view', $pass);

        $pass->load(['type', 'subdivision', 'requester', 'approver', 'scans.gate', 'scans.guard']);

        return Inertia::render('Passes/Show', [
            'pass' => $pass,
            'qrCodeUrl' => $pass->qr_code_path ? asset('storage/' . $pass->qr_code_path) : null,
        ]);
    }

    /**
     * Show the form for editing the pass.
     */
    public function edit(Pass $pass)
    {
        $this->authorize('update', $pass);

        $subdivisions = Subdivision::where('status', 'active')->get();
        $passTypes = PassType::where('is_active', true)->get();

        return Inertia::render('Passes/Edit', [
            'pass' => $pass->load(['type', 'subdivision']),
            'subdivisions' => $subdivisions,
            'passTypes' => $passTypes,
        ]);
    }

    /**
     * Update the specified pass.
     */
    public function update(UpdatePassRequest $request, Pass $pass)
    {
        try {
            $this->passService->updatePass($pass, $request->validated());

            return redirect()->route('passes.show', $pass)
                ->with('success', 'Pass updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified pass.
     */
    public function destroy(Pass $pass)
    {
        $this->authorize('delete', $pass);

        $pass->delete();

        return redirect()->route('passes.index')
            ->with('success', 'Pass deleted successfully!');
    }

    /**
     * Approve a pending pass.
     */
    public function approve(Request $request, Pass $pass)
    {
        $this->authorize('approve', $pass);

        try {
            $this->passService->approvePass($pass, $request->user());

            return back()->with('success', 'Pass approved successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject a pending pass.
     */
    public function reject(Request $request, Pass $pass)
    {
        $this->authorize('approve', $pass);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->passService->rejectPass($pass, $request->user(), $request->reason);

            return back()->with('success', 'Pass rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Revoke an active pass.
     */
    public function revoke(Request $request, Pass $pass)
    {
        $this->authorize('revoke', $pass);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->passService->revokePass($pass, $request->reason);

            return back()->with('success', 'Pass revoked.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Download QR code.
     */
    public function downloadQR(Pass $pass)
    {
        $this->authorize('view', $pass);

        if (!$pass->qr_code_path) {
            return back()->withErrors(['error' => 'QR code not found.']);
        }

        return response()->download(
            storage_path('app/public/' . $pass->qr_code_path),
            $pass->pass_number . '.png'
        );
    }
}
