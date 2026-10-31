@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors mb-3">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Back to Login</span>
        </a>
        <h2 class="text-xl font-bold text-white tracking-tight">Forgot Password</h2>
        <p class="text-xs text-slate-400 mt-1">Provide your registered email address and we will generate a secure reset link.</p>
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

    <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-4">
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

        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-200 flex items-center justify-center gap-2">
                <span>Send Reset Instructions</span>
                <i class="fa-regular fa-paper-plane text-xs"></i>
            </button>
        </div>
    </form>
@endsection
