<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Portal') - Recruitment System</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN for instant rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.4); border-radius: 4px; }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased font-sans text-slate-800 bg-slate-100 flex flex-col">

    <!-- Main Wrapper -->
    <div class="flex h-screen overflow-hidden">

        <!-- Mobile Sidebar Overlay Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar Navigation -->
        <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-200 transform -translate-x-full transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:flex lg:flex-col lg:shrink-0 shadow-2xl">
            <!-- Sidebar Header / Logo -->
            <div class="flex items-center justify-between h-20 px-6 bg-slate-950/60 border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                        <i class="fa-solid fa-briefcase text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white block leading-tight">JobPortal<span class="text-indigo-400">Hub</span></span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Admin Control</span>
                    </div>
                </a>
                <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-800 focus:outline-none">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Current Admin Mini Card -->
            <div class="px-6 py-4 mx-4 my-4 bg-slate-800/60 border border-slate-700/50 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 font-bold flex items-center justify-center border border-indigo-500/30">
                    {{ strtoupper(substr(Auth::user()->name ?? 'Admin', 0, 2)) }}
                </div>
                <div class="overflow-hidden flex-1">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs text-slate-300 font-medium capitalize">{{ Auth::user()->role_label ?? 'Admin' }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto sidebar-scroll pb-6">
                <div class="px-3 pt-2 pb-1 text-xs font-bold uppercase tracking-wider text-slate-400">Recruitment & Stats</div>

                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-base {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.jobs.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('admin.jobs.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-briefcase w-5 text-center text-base {{ request()->routeIs('admin.jobs.*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Job Openings</span>
                </a>

                @if(Auth::user() && (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()))
                    <div class="px-3 pt-6 pb-1 text-xs font-bold uppercase tracking-wider text-slate-400">Administration</div>

                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-3 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-users-gear w-5 text-center text-base {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Admin Accounts</span>
                    </a>
                @endif

                <div class="px-3 pt-6 pb-1 text-xs font-bold uppercase tracking-wider text-slate-400">Account Settings</div>

                <a href="{{ route('admin.profile') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('admin.profile') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-user-shield w-5 text-center text-base {{ request()->routeIs('admin.profile') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Profile & Security</span>
                </a>

                <div class="pt-6">
                    <form method="POST" action="{{ route('admin.logout') }}" onsubmit="return confirm('Are you sure you want to log out?');">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-3 text-sm font-semibold rounded-xl text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-colors">
                            <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center text-base"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </nav>

            <!-- Sidebar Footer Status -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/40 text-xs text-slate-400 flex items-center justify-between">
                <span>Version 1.0.0</span>
                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> System Active
                </span>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-100">

            <!-- Top Header Navbar -->
            <header class="h-20 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-sm z-30">
                <!-- Mobile Menu Button & Breadcrumb -->
                <div class="flex items-center gap-4">
                    <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-xl focus:outline-none">
                        <i class="fa-solid fa-bars-staggered text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">@yield('page-title', 'Overview')</h1>
                        <p class="text-xs text-slate-500 hidden sm:block">@yield('page-subtitle', 'Welcome to the Recruitment Portal Administration')</p>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- View Careers Site Link -->
                    <a href="{{ route('public.jobs.index') }}" target="_blank" class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors border border-indigo-200">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Public Careers</span>
                    </a>

                    <!-- Today Date Chip -->
                    <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold border border-slate-200">
                        <i class="fa-regular fa-calendar-check text-indigo-600"></i>
                        <span>{{ now()->format('l, d M Y') }}</span>
                    </div>

                    <!-- Profile Dropdown Container -->
                    <div class="relative">
                        <button type="button" onclick="toggleUserDropdown()" id="userMenuBtn" class="flex items-center gap-3 p-1.5 pr-3 rounded-2xl hover:bg-slate-100 transition-colors focus:outline-none border border-transparent hover:border-slate-200">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold flex items-center justify-center text-sm shadow">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="text-left hidden sm:block">
                                <span class="text-xs font-bold text-slate-800 block leading-tight">{{ Auth::user()->name ?? 'Admin' }}</span>
                                <span class="text-[11px] font-semibold text-slate-500 uppercase">{{ Auth::user()->role_label ?? 'Admin' }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 transform origin-top-right transition-all">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                                <p class="text-xs text-slate-500">Signed in as</p>
                                <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->email ?? '' }}</p>
                                <p class="text-[11px] font-medium text-indigo-600 mt-0.5">Role: {{ Auth::user()->role_label ?? 'Administrator' }}</p>
                            </div>

                            <a href="{{ route('admin.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 font-medium">
                                <i class="fa-regular fa-user text-slate-400 w-4"></i> Profile Settings
                            </a>
                            <a href="{{ route('admin.profile') }}#password" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 font-medium">
                                <i class="fa-solid fa-key text-slate-400 w-4"></i> Change Password
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 font-semibold">
                                    <i class="fa-solid fa-right-from-bracket w-4"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Page Body Content -->
            <main class="flex-1 overflow-y-auto px-4 sm:px-8 py-6">
                <!-- Flash Alerts -->
                @if (session('success'))
                    <div id="flashSuccess" class="mb-6 flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm transition-all animate-fadeIn">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="document.getElementById('flashSuccess').remove()" class="text-emerald-500 hover:text-emerald-800 p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div id="flashError" class="mb-6 flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="document.getElementById('flashError').remove()" class="text-rose-500 hover:text-rose-800 p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm">
                        <div class="flex items-center gap-2 mb-2 font-bold text-sm">
                            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                            <span>Please correct the errors below:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Content Slot -->
                @yield('content')
            </main>

            <!-- Responsive Footer -->
            <footer class="bg-white border-t border-slate-200/80 px-6 py-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700">Job Portal System</span>
                    <span>&copy; {{ date('Y') }} All Rights Reserved.</span>
                </div>
                <div class="flex items-center gap-4 text-slate-500">
                    <span>Recruitment & CRM Portal</span>
                    <span class="inline-block w-1 h-1 rounded-full bg-slate-300"></span>
                    <span>Server: {{ config('app.env') }}</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts for Responsiveness -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicked outside
        window.addEventListener('click', function(e) {
            const userMenuBtn = document.getElementById('userMenuBtn');
            const dropdown = document.getElementById('userDropdown');
            if (userMenuBtn && dropdown && !userMenuBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Auto dismiss flash messages after 6 seconds
        setTimeout(function() {
            const flashSuccess = document.getElementById('flashSuccess');
            if (flashSuccess) {
                flashSuccess.style.opacity = '0';
                setTimeout(() => flashSuccess.remove(), 400);
            }
        }, 6000);
    </script>
    @stack('scripts')
</body>
</html>
