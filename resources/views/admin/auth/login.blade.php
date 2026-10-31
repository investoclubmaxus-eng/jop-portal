@extends('layouts.auth')

@section('title', 'Admin Sign In')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white tracking-tight">Admin Sign In</h2>
        <p class="text-xs text-slate-400 mt-1">Enter your authorized administrative credentials to continue.</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-xs space-y-1">
            @foreach ($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Admin Email Address
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-regular fa-envelope text-sm"></i>
                </div>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                       placeholder="admin@jobportal.com">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Password
                </label>
                <a href="{{ route('admin.password.request') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors font-medium">
                    Forgot Password?
                </a>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-lock text-sm"></i>
                </div>
                <input type="password" id="password" name="password" required
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                       placeholder="••••••••">
            </div>
        </div>

        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-800">
                <span class="text-xs font-medium text-slate-400">Remember this device</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-200 flex items-center justify-center gap-2">
                <span>Sign In to Portal</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </div>
    </form>
@endsection
