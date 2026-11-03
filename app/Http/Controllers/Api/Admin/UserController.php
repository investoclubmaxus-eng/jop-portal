<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List administrators with filtering & statistics.
     */
    public function index(Request $request): JsonResponse
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

        $users = $query->latest('id')->paginate(10);

        // Format items
        $formattedUsers = collect($users->items())->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'role_label' => $u->role_label,
                'status' => $u->status,
                'is_trashed' => $u->trashed(),
                'last_login_at' => $u->last_login_at ? $u->last_login_at->format('M d, Y - h:i A') : 'Never',
                'created_at' => $u->created_at->format('M d, Y'),
                'is_current_user' => $u->id === Auth::id(),
            ];
        });

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', User::STATUS_ACTIVE)->count(),
            'inactive' => User::where('status', User::STATUS_INACTIVE)->count(),
            'trashed' => User::onlyTrashed()->count(),
        ];

        return response()->json([
            'users' => $formattedUsers,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created administrator account.
     */
    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Administrator account '{$user->name}' created successfully.",
            'user' => $user,
        ], 201);
    }

    /**
     * Update an administrator account.
     */
    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (Auth::id() === $user->id && $validated['status'] === User::STATUS_INACTIVE) {
            return response()->json([
                'message' => 'You cannot deactivate your own logged-in account.',
                'errors' => ['status' => ['You cannot deactivate your own logged-in account.']],
            ], 422);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Admin '{$user->name}' updated successfully.",
            'user' => $user,
        ]);
    }

    /**
     * Toggle active/inactive account status.
     */
    public function toggleStatus(User $user): JsonResponse
    {
        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot change the status of your own account.',
            ], 422);
        }

        $newStatus = $user->status === User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;
        $user->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Status of '{$user->name}' changed to " . ucfirst($newStatus) . '.',
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Soft delete an administrator account.
     */
    public function destroy(User $user): JsonResponse
    {
        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own logged-in account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "Admin '{$user->name}' moved to trash (soft deleted).",
        ]);
    }

    /**
     * Restore a soft-deleted administrator account.
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'success' => true,
            'message' => "Admin '{$user->name}' restored successfully.",
        ]);
    }
}
