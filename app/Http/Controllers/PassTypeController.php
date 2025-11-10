<?php

namespace App\Http\Controllers;

use App\Models\PassType;
use App\Models\Subdivision;
use App\Http\Requests\StorePassTypeRequest;
use App\Http\Requests\UpdatePassTypeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PassTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PassType::class);

        $query = PassType::with(['subdivision'])->latest();

        // Filter by subdivision
        if ($request->filled('subdivision_id')) {
            $query->where('subdivision_id', $request->subdivision_id);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        // Filter by requires approval
        if ($request->filled('requires_approval')) {
            $query->where('requires_approval', $request->requires_approval === 'true' || $request->requires_approval === '1');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort by sort_order, then created_at
        $query->orderBy('sort_order')->orderBy('created_at', 'desc');

        $passTypes = $query->paginate(15)->withQueryString();

        return Inertia::render('PassTypes/Index', [
            'passTypes' => $passTypes,
            'subdivisions' => Subdivision::where('status', 'active')->get(['id', 'name']),
            'filters' => $request->only(['subdivision_id', 'is_active', 'requires_approval', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', PassType::class);

        return Inertia::render('PassTypes/Create', [
            'subdivisions' => Subdivision::where('status', 'active')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePassTypeRequest $request)
    {
        $data = $request->validated();

        $passType = PassType::create($data);

        activity()
            ->performedOn($passType)
            ->causedBy($request->user())
            ->log('Pass type created');

        return redirect()->route('pass-types.index')
            ->with('success', 'Pass type created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(PassType $passType)
    {
        $this->authorize('view', $passType);

        $passType->load(['subdivision', 'passes' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('PassTypes/Show', [
            'passType' => $passType,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PassType $passType)
    {
        $this->authorize('update', $passType);

        $passType->load('subdivision');

        return Inertia::render('PassTypes/Edit', [
            'passType' => $passType,
            'subdivisions' => Subdivision::where('status', 'active')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePassTypeRequest $request, PassType $passType)
    {
        $data = $request->validated();

        $passType->update($data);

        activity()
            ->performedOn($passType)
            ->causedBy($request->user())
            ->log('Pass type updated');

        return redirect()->route('pass-types.index')
            ->with('success', 'Pass type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PassType $passType)
    {
        $this->authorize('delete', $passType);

        // Check if pass type has active passes
        $activePasses = $passType->passes()->whereIn('status', ['pending', 'approved', 'active'])->count();

        if ($activePasses > 0) {
            return back()->with('error', "Cannot delete pass type. It has {$activePasses} active passes.");
        }

        $passType->delete();

        activity()
            ->performedOn($passType)
            ->causedBy(request()->user())
            ->log('Pass type deleted');

        return redirect()->route('pass-types.index')
            ->with('success', 'Pass type deleted successfully!');
    }

    /**
     * Change the status of the pass type.
     */
    public function changeStatus(Request $request, PassType $passType)
    {
        $this->authorize('changeStatus', $passType);

        $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $passType->update([
            'is_active' => $request->is_active,
        ]);

        activity()
            ->performedOn($passType)
            ->causedBy($request->user())
            ->log("Pass type " . ($request->is_active ? 'activated' : 'deactivated'));

        return back()->with('success', 'Pass type status updated successfully!');
    }

    /**
     * Update the sort order of pass types.
     */
    public function updateOrder(Request $request)
    {
        $this->authorize('update', PassType::class);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'exists:pass_types,id'],
            'order.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->order as $item) {
            PassType::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        activity()
            ->causedBy($request->user())
            ->log('Pass types reordered');

        return back()->with('success', 'Pass types reordered successfully!');
    }
}
