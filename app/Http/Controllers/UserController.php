<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Subdivision;
use App\Models\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles'])->latest();

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subdivision (for admins/employees)
        if ($request->filled('subdivision_id')) {
            $query->whereJsonContains('subdivision_ids', (string)$request->subdivision_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        // Get filter options
        $roles = Role::all();
        $subdivisions = Subdivision::where('status', 'active')->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'subdivisions' => $subdivisions,
            'filters' => $request->only(['role', 'status', 'subdivision_id', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        $subdivisions = Subdivision::where('status', 'active')->get();
        $gates = Gate::with('subdivision')->where('status', 'active')->get();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'subdivisions' => $subdivisions,
            'gates' => $gates,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        // Hash password
        $data['password'] = Hash::make($data['password']);

        // Create user
        $user = User::create($data);

        // Assign role
        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('User created');

        return redirect()->route('users.show', $user)
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'permissions']);

        // Get subdivisions if user has any assigned
        $subdivisions = [];
        if ($user->subdivision_ids) {
            $subdivisionIds = json_decode($user->subdivision_ids, true) ?? [];
            if (!empty($subdivisionIds)) {
                $subdivisions = Subdivision::whereIn('id', $subdivisionIds)->get();
            }
        }

        // Get gates if user has any assigned (for guards)
        $gates = [];
        if ($user->gate_ids) {
            $gateIds = json_decode($user->gate_ids, true) ?? [];
            if (!empty($gateIds)) {
                $gates = Gate::with('subdivision')->whereIn('id', $gateIds)->get();
            }
        }

        // Get activity logs
        $activityLogs = activity()
            ->causedBy($user)
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Users/Show', [
            'user' => $user,
            'subdivisions' => $subdivisions,
            'gates' => $gates,
            'activityLogs' => $activityLogs,
        ]);
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $user->load('roles');

        $roles = Role::all();
        $subdivisions = Subdivision::where('status', 'active')->get();
        $gates = Gate::with('subdivision')->where('status', 'active')->get();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'subdivisions' => $subdivisions,
            'gates' => $gates,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Update user
        $user->update($data);

        // Update role if provided and user has permission
        if (isset($data['role']) && $request->user()->can('edit users')) {
            $user->syncRoles([$data['role']]);
        }

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('User updated');

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Soft delete
        $user->delete();

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log('User deleted');

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Activate or deactivate a user.
     */
    public function changeStatus(Request $request, User $user)
    {
        $this->authorize('changeStatus', $user);

        $request->validate([
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        $user->update([
            'status' => $request->status,
        ]);

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log("User status changed to {$request->status}");

        return back()->with('success', 'User status updated successfully!');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('User password reset');

        return back()->with('success', 'Password reset successfully!');
    }
}
