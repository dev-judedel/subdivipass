<?php

namespace App\Http\Controllers;

use App\Http\Requests\Gates\StoreGateRequest;
use App\Http\Requests\Gates\UpdateGateRequest;
use App\Models\Gate;
use App\Models\Subdivision;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GateController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'subdivision_id', 'type']);

        $gates = Gate::query()
            ->with('subdivision:id,name')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn($query, $type) => $query->where('type', $type))
            ->when($filters['subdivision_id'] ?? null, fn($query, $subdivisionId) => $query->where('subdivision_id', $subdivisionId))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Gate $gate) => [
                'id' => $gate->id,
                'name' => $gate->name,
                'code' => $gate->code,
                'status' => $gate->status,
                'type' => $gate->type,
                'location' => $gate->location,
                'subdivision' => $gate->subdivision?->only(['id', 'name']),
                'updated_at' => $gate->updated_at?->toDateTimeString(),
            ]);

        $stats = [
            'total' => Gate::count(),
            'active' => Gate::where('status', 'active')->count(),
            'maintenance' => Gate::where('status', 'maintenance')->count(),
        ];

        return Inertia::render('Gates/Index', [
            'gates' => $gates,
            'filters' => $filters,
            'statusOptions' => ['active', 'inactive', 'maintenance'],
            'typeOptions' => ['entry', 'exit', 'both'],
            'subdivisionOptions' => Subdivision::orderBy('name')->get(['id', 'name']),
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Gates/Create', [
            'statusOptions' => ['active', 'inactive', 'maintenance'],
            'typeOptions' => ['entry', 'exit', 'both'],
            'subdivisionOptions' => Subdivision::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreGateRequest $request)
    {
        $data = $request->validated();
        $coordinates = $this->normalizeCoordinates($data['coordinates'] ?? null);
        unset($data['coordinates']);

        Gate::create(array_merge($data, ['coordinates' => $coordinates]));

        return redirect()
            ->route('gates.index')
            ->with('success', 'Gate created successfully.');
    }

    public function edit(Gate $gate): Response
    {
        return Inertia::render('Gates/Edit', [
            'gate' => [
                'id' => $gate->id,
                'subdivision_id' => $gate->subdivision_id,
                'name' => $gate->name,
                'code' => $gate->code,
                'location' => $gate->location,
                'status' => $gate->status,
                'type' => $gate->type,
                'notes' => $gate->notes,
                'coordinates' => [
                    'lat' => $gate->coordinates['lat'] ?? null,
                    'lng' => $gate->coordinates['lng'] ?? null,
                ],
            ],
            'statusOptions' => ['active', 'inactive', 'maintenance'],
            'typeOptions' => ['entry', 'exit', 'both'],
            'subdivisionOptions' => Subdivision::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateGateRequest $request, Gate $gate)
    {
        $data = $request->validated();
        $coordinates = $this->normalizeCoordinates($data['coordinates'] ?? null);
        unset($data['coordinates']);

        $gate->update(array_merge($data, ['coordinates' => $coordinates]));

        return redirect()
            ->route('gates.index')
            ->with('success', 'Gate updated successfully.');
    }

    public function destroy(Gate $gate)
    {
        $gate->delete();

        return redirect()
            ->route('gates.index')
            ->with('success', 'Gate archived.');
    }

    private function normalizeCoordinates(?array $coordinates): ?array
    {
        if (!$coordinates) {
            return null;
        }

        $lat = $coordinates['lat'] ?? null;
        $lng = $coordinates['lng'] ?? null;

        $lat = $lat === '' ? null : $lat;
        $lng = $lng === '' ? null : $lng;

        if ($lat === null && $lng === null) {
            return null;
        }

        return [
            'lat' => $lat !== null ? (float)$lat : null,
            'lng' => $lng !== null ? (float)$lng : null,
        ];
    }
}
