@extends('layouts.admin')

@section('title', 'Admin Accounts Management')
@section('page-title', 'Admin Accounts & Roles')
@section('page-subtitle', 'Manage administrator credentials, role permissions, active status, and soft deletions')

@section('content')
<div class="space-y-6">

    <!-- Top Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.users.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-indigo-500 transition-colors {{ $status === 'all' ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Admins</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['total'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">All registered administrators</p>
        </a>

        <a href="{{ route('admin.users.index', ['status' => 'active']) }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-emerald-500 transition-colors {{ $status === 'active' ? 'ring-2 ring-emerald-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-user-check"></i></span>
            </div>
            <div class="text-2xl font-black text-emerald-600 mt-2">{{ $stats['active'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Can access portal</p>
        </a>

        <a href="{{ route('admin.users.index', ['status' => 'inactive']) }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-amber-500 transition-colors {{ $status === 'inactive' ? 'ring-2 ring-amber-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Inactive</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-user-xmark"></i></span>
            </div>
            <div class="text-2xl font-black text-amber-600 mt-2">{{ $stats['inactive'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Deactivated logins</p>
        </a>

        <a href="{{ route('admin.users.index', ['status' => 'trashed']) }}" class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:border-rose-500 transition-colors {{ $status === 'trashed' ? 'ring-2 ring-rose-500' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Soft Deleted</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold"><i class="fa-solid fa-trash-can"></i></span>
            </div>
            <div class="text-2xl font-black text-rose-600 mt-2">{{ $stats['trashed'] }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Archived (can restore)</p>
        </a>
    </div>

    <!-- Action Toolbar & Filters -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900">
                    {{ $status === 'trashed' ? 'Trashed Admin Accounts' : 'Administrator Directory' }}
                </h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                    {{ $users->total() }} accounts
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($status === 'trashed')
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-2 transition-colors">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Back to Active List</span>
                    </a>
                @else
                    <button type="button" onclick="openCreateModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Add New Admin</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by admin name or email..."
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
            </div>

            <div class="sm:col-span-4">
                <select name="role" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    <option value="">All Roles</option>
                    <option value="super_admin" {{ $role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="recruiter" {{ $role === 'recruiter' ? 'selected' : '' }}>Recruiter</option>
                    <option value="hr_manager" {{ $role === 'hr_manager' ? 'selected' : '' }}>HR Manager</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors">
                    Filter
                </button>
                @if($search || $role)
                    <a href="{{ route('admin.users.index', ['status' => $status]) }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Responsive Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Administrator</th>
                        <th class="py-3.5 px-4">Role & Access</th>
                        <th class="py-3.5 px-4">Account Status</th>
                        <th class="py-3.5 px-4">Last Login Tracked</th>
                        <th class="py-3.5 px-4">Created Date</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($users as $userItem)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Admin Details -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-500 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-sm">
                                        {{ strtoupper(substr($userItem->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                            <span>{{ $userItem->name }}</span>
                                            @if($userItem->id === Auth::id())
                                                <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 text-[10px] font-bold">You</span>
                                            @endif
                                        </div>
                                        <span class="text-slate-400 text-xs">{{ $userItem->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                                    @if($userItem->role === 'super_admin') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($userItem->role === 'admin') bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @elseif($userItem->role === 'recruiter') bg-blue-50 text-blue-700 border border-blue-200
                                    @else bg-slate-100 text-slate-700 border border-slate-200
                                    @endif">
                                    <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                    <span>{{ $userItem->role_label }}</span>
                                </span>
                            </td>

                            <!-- Status & Quick Toggle -->
                            <td class="py-4 px-4">
                                @if($userItem->trashed())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Deleted
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $userItem) }}" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        @if($userItem->id === Auth::id())
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-not-allowed" title="Your own account">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                            </span>
                                        @else
                                            <button type="submit" onclick="return confirm('Toggle status for {{ $userItem->name }}?')" class="group inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold transition-all {{ $userItem->isActive() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200' }}" title="Click to toggle status">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $userItem->isActive() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                                <span>{{ ucfirst($userItem->status) }}</span>
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </td>

                            <!-- Last Login Tracking -->
                            <td class="py-4 px-4 text-slate-600">
                                @if($userItem->last_login_at)
                                    <div class="font-bold text-slate-800">{{ $userItem->last_login_at->format('M d, Y') }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $userItem->last_login_at->format('h:i A') }} ({{ $userItem->last_login_at->diffForHumans() }})</div>
                                @else
                                    <span class="text-slate-400 italic">Never logged in</span>
                                @endif
                            </td>

                            <!-- Created Date -->
                            <td class="py-4 px-4 text-slate-500">
                                {{ $userItem->created_at->format('M d, Y') }}
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if($userItem->trashed())
                                        <form method="POST" action="{{ route('admin.users.restore', $userItem->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-bold text-xs flex items-center gap-1.5 transition-colors" title="Restore User">
                                                <i class="fa-solid fa-rotate-left"></i>
                                                <span>Restore</span>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Edit Button -->
                                        <button type="button" onclick='openEditModal(@json($userItem))' class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Admin">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        @if($userItem->id !== Auth::id())
                                            <!-- Soft Delete -->
                                            <form method="POST" action="{{ route('admin.users.destroy', $userItem) }}" onsubmit="return confirm('Soft delete admin account {{ $userItem->name }}? The user will be moved to trash.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Move to Trash (Soft Delete)">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-users-slash"></i>
                                </div>
                                <p class="font-bold text-slate-700">No admin accounts found</p>
                                <p class="text-xs text-slate-400 mt-1">Try adjusting your search or filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Create Admin Modal -->
<div id="createAdminModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 w-full max-w-lg overflow-hidden shadow-2xl animate-scaleIn">
        <div class="px-6 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Create New Administrator</h3>
            </div>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name</label>
                <input type="text" name="name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="John Doe">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="john@jobportal.com">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Role</label>
                    <select name="role" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="recruiter">Recruiter</option>
                        <option value="hr_manager">HR Manager</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status</label>
                    <select name="status" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="Min. 8 chars">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="Repeat password">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/20 transition-all">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Admin Modal -->
<div id="editAdminModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="px-6 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Edit Administrator</h3>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editAdminForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Role</label>
                    <select id="edit_role" name="role" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="recruiter">Recruiter</option>
                        <option value="hr_manager">HR Manager</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status</label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Change Password (leave blank to keep current)</span>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <input type="password" name="password" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="New password">
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" placeholder="Confirm password">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/20 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createAdminModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createAdminModal').classList.add('hidden');
    }

    function openEditModal(user) {
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_status').value = user.status;
        document.getElementById('editAdminForm').action = "/admin/users/" + user.id;
        document.getElementById('editAdminModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editAdminModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
