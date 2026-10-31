@extends('layouts.admin')

@section('title', 'Admin Profile & Security')
@section('page-title', 'Profile & Account Security')
@section('page-subtitle', 'Manage your personal administrator details and update login credentials')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Admin Account Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $user->role_label }}
                        </span>
                        @if($user->isActive())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                Inactive
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $user->email }}</p>
                </div>
            </div>

            <!-- Last Login Badge -->
            <div class="bg-slate-50 border border-slate-200/70 rounded-2xl p-4 text-xs space-y-1">
                <div class="flex items-center gap-2 text-slate-500">
                    <i class="fa-regular fa-clock text-indigo-600"></i>
                    <span class="font-medium">Last Login Tracked:</span>
                </div>
                <div class="text-slate-800 font-bold text-sm">
                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y - h:i A') : 'First session active' }}
                </div>
                @if($user->last_login_at)
                    <div class="text-[11px] text-slate-400">({{ $user->last_login_at->diffForHumans() }})</div>
                @endif
            </div>
        </div>

        <!-- Two Column Forms -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-8">

            <!-- Profile Info Form -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-regular fa-address-card"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Personal Information</h3>
                </div>
                <p class="text-xs text-slate-500 mb-5">Update your display name and email address registered on the portal.</p>

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Full Name
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                            Assigned Role
                        </label>
                        <input type="text" value="{{ $user->role_label }} (Assigned by Administrator)" disabled
                               class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Form -->
            <div id="password" class="lg:border-l lg:border-slate-100 lg:pl-8">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Change Password</h3>
                </div>
                <p class="text-xs text-slate-500 mb-5">Ensure your administrator account uses a strong, unique password.</p>

                <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                               placeholder="Enter your current password">
                    </div>

                    <div>
                        <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            New Password
                        </label>
                        <input type="password" id="new_password" name="password" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                               placeholder="Minimum 8 characters">
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Confirm New Password
                        </label>
                        <input type="password" id="new_password_confirmation" name="password_confirmation" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                               placeholder="Re-type new password">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all flex items-center gap-2">
                            <i class="fa-solid fa-key"></i>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
