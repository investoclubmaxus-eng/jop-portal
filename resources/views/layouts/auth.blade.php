<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Portal') - Authentication</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="h-full antialiased font-sans bg-slate-900 text-slate-100 flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white">

    <div class="w-full max-w-md my-auto">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-xl shadow-indigo-500/25 mb-4">
                <i class="fa-solid fa-briefcase text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">JobPortal<span class="text-indigo-400">Hub</span></h1>
            <p class="text-sm font-medium text-slate-400 mt-1">Recruitment Administration Portal</p>
        </div>

        <!-- Flash messages -->
        @if (session('success'))
            <div class="mb-5 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-2xl text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-5 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-2xl text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base shrink-0"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-2xl text-sm flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-base shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            @yield('content')
        </div>

        <!-- Footer Notice -->
        <div class="text-center mt-8 text-xs text-slate-500">
            &copy; {{ date('Y') }} JobPortal Recruitment System. All rights reserved.
        </div>
    </div>

</body>
</html>
