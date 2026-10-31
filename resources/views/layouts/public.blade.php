<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Careers Portal') - Explore Opportunities</title>

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
<body class="h-full antialiased font-sans bg-slate-50 text-slate-800 flex flex-col selection:bg-indigo-600 selection:text-white">

    <!-- Header Navigation -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('public.jobs.index') }}" class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25">
                    <i class="fa-solid fa-briefcase text-xl"></i>
                </div>
                <div>
                    <span class="text-xl font-black tracking-tight text-slate-900 block leading-tight">JobPortal<span class="text-indigo-600">Hub</span></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Careers & Opportunities</span>
                </div>
            </a>

            <!-- Nav Links & Admin Portal Entry -->
            <div class="flex items-center gap-3 sm:gap-6">
                <a href="{{ route('public.jobs.index') }}" class="text-sm font-bold {{ request()->routeIs('public.jobs.*') ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' }} transition-colors">
                    Browse Vacancies
                </a>

                <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

                <a href="{{ route('admin.login') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-lock text-slate-400"></i>
                    <span>Admin Portal</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 pb-8 border-b border-slate-800">
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                            <i class="fa-solid fa-briefcase text-sm"></i>
                        </div>
                        <span class="text-lg font-black text-white">JobPortal<span class="text-indigo-400">Hub</span></span>
                    </div>
                    <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                        Discover your next career leap. Explore open vacancies, check eligibility criteria, and submit your application directly to our hiring team.
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white mb-3">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('public.jobs.index') }}" class="hover:text-indigo-400 transition-colors">All Vacancies</a></li>
                        <li><a href="{{ route('admin.login') }}" class="hover:text-indigo-400 transition-colors">Administrator Login</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white mb-3">Support & Legal</h4>
                    <p class="text-xs text-slate-500 mb-2">Equal Opportunity Employer. Candidate submissions are reviewed in compliance with data privacy policies.</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} JobPortal System. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <span>Privacy Policy</span>
                    <span>&bull;</span>
                    <span>Terms of Service</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
