<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display listing of admin accounts.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $role = $request->query('role');
        $search = $request->query('search');

        $query = User::query();

        if ($status === 'trashed') {
            $query->onlyTrashed();
        } else {
            if ($status === 'active') {
                $query->where('status', User::STATUS_ACTIVE);
            } elseif ($status === 'inactive') {
                $query->where('status', User::STATUS_INACTIVE);
            }
        }

        if ($role && in_array($role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_RECRUITER, User::ROLE_HR], true)) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest('id')->paginate(10)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', User::STATUS_ACTIVE)->count(),
            'inactive' => User::where('status', User::STATUS_INACTIVE)->count(),
            'trashed' => User::onlyTrashed()->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'status', 'role', 'search'));
    }

    /**
     * Store a newly created admin account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_RECRUITER, User::ROLE_HR])],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Admin user created successfully.');
    }

    /**
     * Update the specified admin account.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_RECRUITER, User::ROLE_HR])],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (Auth::id() === $user->id && $validated['status'] === User::STATUS_INACTIVE) {
            return back()->with('error', 'You cannot deactivate your own logged-in account.');
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Admin '{$user->name}' updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot change the status of your own account.');
        }

        $newStatus = $user->status === User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;
        $user->update(['status' => $newStatus]);

        return back()->with('success', "Status of '{$user->name}' changed to " . ucfirst($newStatus) . '.');
    }

    /**
     * Soft delete an admin account.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own logged-in account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Admin user '{$user->name}' was moved to trash (soft deleted).");
    }

    /**
     * Restore a soft deleted admin account.
     */
    public function restore(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index', ['status' => 'trashed'])
            ->with('success', "Admin user '{$user->name}' was restored successfully.");
    }
}
