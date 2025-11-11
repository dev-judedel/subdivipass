<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subdivisions\StoreSubdivisionRequest;
use App\Http\Requests\Subdivisions\UpdateSubdivisionRequest;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SubdivisionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);

        $subdivisions = Subdivision::query()
            ->withCount([
                'gates',
                'passTypes',
                'passes',
                'users as guard_count' => function ($query) {
                    $query->whereHas('roles', fn($roleQuery) => $roleQuery->where('name', 'guard'));
                },
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Subdivision $subdivision) => [
                'id' => $subdivision->id,
                'name' => $subdivision->name,
                'code' => $subdivision->code,
                'status' => $subdivision->status,
                'address' => $subdivision->address,
                'contact_person' => $subdivision->contact_person,
                'contact_email' => $subdivision->contact_email,
                'gates_count' => $subdivision->gates_count,
                'pass_types_count' => $subdivision->pass_types_count,
                'passes_count' => $subdivision->passes_count,
                'guard_count' => $subdivision->guard_count,
                'logo_url' => $subdivision->logo_path ? Storage::url($subdivision->logo_path) : null,
                'updated_at' => $subdivision->updated_at?->toDateTimeString(),
            ]);

        $stats = [
            'total' => Subdivision::count(),
            'active' => Subdivision::where('status', 'active')->count(),
            'inactive' => Subdivision::where('status', 'inactive')->count(),
            'suspended' => Subdivision::where('status', 'suspended')->count(),
        ];

        return Inertia::render('Subdivisions/Index', [
            'subdivisions' => $subdivisions,
            'filters' => $filters,
            'statusOptions' => ['active', 'inactive', 'suspended'],
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Subdivisions/Create', [
            'statusOptions' => ['active', 'inactive', 'suspended'],
            'defaultSettings' => $this->defaultSettings(),
        ]);
    }

    public function store(StoreSubdivisionRequest $request)
    {
        $data = $request->validated();
        $settings = $this->normalizeSettings($data['settings'] ?? []);
        unset($data['settings']);
        unset($data['remove_logo']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('subdivisions/logos', 'public');
        }

        $subdivision = new Subdivision($data);
        $subdivision->settings = $settings;
        $subdivision->save();

        return redirect()
            ->route('subdivisions.index')
            ->with('success', 'Subdivision created successfully.');
    }

    public function edit(Subdivision $subdivision): Response
    {
        $subdivision->load(['users.roles', 'gates']);

        $assignedUsers = $subdivision->users
            ->sortBy('name')
            ->map(fn(User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->first(),
                'status' => $user->status,
            ])
            ->values();

        $availableUsers = User::role(['admin', 'employee', 'guard'])
            ->where('status', 'active')
            ->whereNotIn('id', $assignedUsers->pluck('id'))
            ->orderBy('name')
            ->get()
            ->map(fn(User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->first(),
            ]);

        $metrics = [
            'gates' => $subdivision->gates->count(),
            'assigned_users' => $assignedUsers->count(),
            'guards' => $assignedUsers->where('role', 'guard')->count(),
            'active_passes' => $subdivision->passes()->where('status', 'active')->count(),
        ];

        return Inertia::render('Subdivisions/Edit', [
            'subdivision' => [
                'id' => $subdivision->id,
                'name' => $subdivision->name,
                'code' => $subdivision->code,
                'address' => $subdivision->address,
                'contact_person' => $subdivision->contact_person,
                'contact_email' => $subdivision->contact_email,
                'contact_phone' => $subdivision->contact_phone,
                'status' => $subdivision->status,
                'notes' => $subdivision->notes,
                'settings' => array_merge($this->defaultSettings(), $subdivision->settings ?? []),
                'logo_url' => $subdivision->logo_path ? Storage::url($subdivision->logo_path) : null,
            ],
            'statusOptions' => ['active', 'inactive', 'suspended'],
            'assignedUsers' => $assignedUsers,
            'availableUsers' => $availableUsers,
            'metrics' => $metrics,
        ]);
    }

    public function update(UpdateSubdivisionRequest $request, Subdivision $subdivision)
    {
        $data = $request->validated();
        $settings = $this->normalizeSettings($data['settings'] ?? []);
        unset($data['settings']);
        $removeLogo = (bool)($data['remove_logo'] ?? false);
        unset($data['remove_logo']);

        if ($removeLogo && $subdivision->logo_path) {
            Storage::disk('public')->delete($subdivision->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($subdivision->logo_path) {
                Storage::disk('public')->delete($subdivision->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('subdivisions/logos', 'public');
        }

        $subdivision->fill($data);
        $subdivision->settings = $settings;
        $subdivision->save();

        return redirect()
            ->route('subdivisions.index')
            ->with('success', 'Subdivision updated successfully.');
    }

    public function destroy(Subdivision $subdivision)
    {
        if ($subdivision->logo_path) {
            Storage::disk('public')->delete($subdivision->logo_path);
        }

        $subdivision->delete();

        return redirect()
            ->route('subdivisions.index')
            ->with('success', 'Subdivision archived.');
    }

    private function normalizeSettings(array $settings): array
    {
        $defaults = $this->defaultSettings();

        return [
            'requires_approval' => (bool)($settings['requires_approval'] ?? $defaults['requires_approval']),
            'default_pass_validity_hours' => (int)($settings['default_pass_validity_hours'] ?? $defaults['default_pass_validity_hours']),
            'allow_manual_entry' => (bool)($settings['allow_manual_entry'] ?? $defaults['allow_manual_entry']),
            'send_guard_alerts' => (bool)($settings['send_guard_alerts'] ?? $defaults['send_guard_alerts']),
        ];
    }

    private function defaultSettings(): array
    {
        return [
            'requires_approval' => true,
            'default_pass_validity_hours' => 24,
            'allow_manual_entry' => true,
            'send_guard_alerts' => true,
        ];
    }
}
