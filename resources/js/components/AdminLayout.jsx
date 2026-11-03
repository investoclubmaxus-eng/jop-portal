import React, { useState } from 'react';
import { NavLink, Link, Outlet, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function AdminLayout() {
    const { user, logout } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [userDropdownOpen, setUserDropdownOpen] = useState(false);

    const handleLogout = async () => {
        await logout();
        navigate('/admin/login');
    };

    const navItems = [
        { name: 'Dashboard', path: '/admin/dashboard', icon: 'fa-gauge-high' },
        { name: 'Job Openings', path: '/admin/jobs', icon: 'fa-briefcase' },
        { name: 'Post Vacancy', path: '/admin/jobs/create', icon: 'fa-plus-circle' },
        { name: 'Applications', path: '/admin/applications', icon: 'fa-file-invoice' },
        { name: 'Admin Team', path: '/admin/users', icon: 'fa-users-gear' },
        { name: 'My Profile', path: '/admin/profile', icon: 'fa-id-badge' },
    ];

    const getPageTitle = () => {
        const p = location.pathname;
        if (p === '/admin/dashboard') return 'Recruitment Dashboard';
        if (p === '/admin/jobs') return 'Job Openings Management';
        if (p === '/admin/jobs/create') return 'Create New Vacancy';
        if (p === '/admin/applications') return 'Applications & Candidate Screening';
        if (p.includes('/admin/jobs/') && p.endsWith('/edit')) return 'Edit Job Opening';
        if (p.startsWith('/admin/jobs/')) return 'Job Opening Details & Campaign Links';
        if (p === '/admin/users') return 'Admin User Directory';
        if (p === '/admin/profile') return 'My Profile & Security';
        return 'Admin Portal';
    };

    return (
        <div className="min-h-screen bg-slate-900 text-slate-100 flex flex-col font-sans">
            {/* Top Navigation Bar */}
            <header className="sticky top-0 z-40 bg-slate-900/95 backdrop-blur border-b border-slate-800 px-4 sm:px-6 lg:px-8 py-3">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none"
                            aria-label="Toggle navigation"
                        >
                            <i className="fa-solid fa-bars text-lg"></i>
                        </button>

                        <Link to="/admin/dashboard" className="flex items-center gap-2.5">
                            <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                                <i className="fa-solid fa-layer-group text-sm"></i>
                            </div>
                            <div className="hidden sm:block">
                                <span className="font-bold text-base tracking-tight text-white">JobPortal</span>
                                <span className="ml-1.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">Admin</span>
                            </div>
                        </Link>

                        <div className="hidden md:flex items-center pl-4 ml-4 border-l border-slate-800">
                            <span className="text-sm font-medium text-slate-400">{getPageTitle()}</span>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            to="/jobs"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-indigo-300 hover:text-indigo-200 bg-indigo-950/60 hover:bg-indigo-900/80 border border-indigo-800/60 rounded-lg transition"
                        >
                            <i className="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            <span>View Public Careers</span>
                        </Link>

                        {/* User Profile Dropdown */}
                        <div className="relative">
                            <button
                                type="button"
                                onClick={() => setUserDropdownOpen(!userDropdownOpen)}
                                className="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-800 transition focus:outline-none"
                            >
                                <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs uppercase shadow-sm">
                                    {user?.name?.charAt(0) || 'A'}
                                </div>
                                <div className="hidden lg:block text-left">
                                    <p className="text-xs font-semibold text-slate-200 leading-tight">{user?.name || 'Administrator'}</p>
                                    <p className="text-[10px] text-slate-400 capitalize">{user?.role?.replace('_', ' ') || 'Admin'}</p>
                                </div>
                                <i className="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>

                            {userDropdownOpen && (
                                <div
                                    className="absolute right-0 mt-2 w-56 rounded-xl bg-slate-800 border border-slate-700 shadow-xl py-1.5 z-50 text-xs"
                                    onMouseLeave={() => setUserDropdownOpen(false)}
                                >
                                    <div className="px-3.5 py-2 border-b border-slate-700/80">
                                        <p className="font-semibold text-slate-200">{user?.name}</p>
                                        <p className="text-slate-400 truncate">{user?.email}</p>
                                        <div className="mt-1 flex items-center gap-1.5">
                                            <span className="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span className="text-[10px] uppercase font-bold text-emerald-400">{user?.role}</span>
                                        </div>
                                    </div>

                                    <Link
                                        to="/admin/profile"
                                        onClick={() => setUserDropdownOpen(false)}
                                        className="flex items-center gap-2.5 px-3.5 py-2 text-slate-300 hover:text-white hover:bg-slate-700/60 transition"
                                    >
                                        <i className="fa-solid fa-user text-slate-400"></i>
                                        <span>My Profile</span>
                                    </Link>

                                    <Link
                                        to="/admin/profile"
                                        onClick={() => setUserDropdownOpen(false)}
                                        className="flex items-center gap-2.5 px-3.5 py-2 text-slate-300 hover:text-white hover:bg-slate-700/60 transition"
                                    >
                                        <i className="fa-solid fa-key text-slate-400"></i>
                                        <span>Change Password</span>
                                    </Link>

                                    <div className="border-t border-slate-700/80 my-1"></div>

                                    <button
                                        type="button"
                                        onClick={handleLogout}
                                        className="w-full flex items-center gap-2.5 px-3.5 py-2 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition text-left"
                                    >
                                        <i className="fa-solid fa-arrow-right-from-bracket"></i>
                                        <span>Sign Out</span>
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </header>

            <div className="flex-1 flex">
                {/* Mobile Backdrop */}
                {sidebarOpen && (
                    <div
                        className="fixed inset-0 bg-black/60 z-30 lg:hidden backdrop-blur-sm"
                        onClick={() => setSidebarOpen(false)}
                    ></div>
                )}

                {/* Sidebar Navigation */}
                <aside
                    className={`fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <div className="p-4 flex-1 flex flex-col justify-between overflow-y-auto">
                        <nav className="space-y-1">
                            <p className="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Recruitment Core</p>
                            {navItems.map((item) => (
                                <NavLink
                                    key={item.path}
                                    to={item.path}
                                    onClick={() => setSidebarOpen(false)}
                                    end={item.path === '/admin/dashboard'}
                                    className={({ isActive }) =>
                                        `flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition ${
                                            isActive
                                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                                                : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800'
                                        }`
                                    }
                                >
                                    <i className={`fa-solid ${item.icon} text-base w-5 text-center`}></i>
                                    <span>{item.name}</span>
                                </NavLink>
                            ))}

                            <div className="pt-4 mt-4 border-t border-slate-800">
                                <p className="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Candidate Facing</p>
                                <Link
                                    to="/jobs"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-indigo-300 hover:bg-slate-800 transition"
                                >
                                    <div className="flex items-center gap-3">
                                        <i className="fa-solid fa-globe text-base w-5 text-center text-indigo-400"></i>
                                        <span>Candidate Job Board</span>
                                    </div>
                                    <i className="fa-solid fa-arrow-up-right-from-square text-xs text-slate-400"></i>
                                </Link>
                            </div>
                        </nav>

                        {/* Sidebar Footer Info */}
                        <div className="pt-4 border-t border-slate-800/80">
                            <div className="bg-slate-800/60 rounded-xl p-3 border border-slate-700/50">
                                <div className="flex items-center gap-2 mb-1.5">
                                    <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span className="text-[11px] font-semibold text-slate-300">Recruitment Portal Live</span>
                                </div>
                                <p className="text-[10px] text-slate-400">
                                    Logged in: <span className="text-slate-300 font-medium">{user?.email}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>

                {/* Main Content Area */}
                <main className="flex-1 min-w-0 bg-slate-950 p-4 sm:p-6 lg:p-8">
                    <Outlet />
                </main>
            </div>

            {/* Admin Footer */}
            <footer className="bg-slate-900 border-t border-slate-800 py-3.5 px-4 sm:px-6 text-center text-xs text-slate-400">
                <div className="flex flex-col sm:flex-row items-center justify-between gap-2 max-w-7xl mx-auto">
                    <p>&copy; {new Date().getFullYear()} JobPortal Enterprise Recruitment Platform. All rights reserved.</p>
                    <p className="flex items-center gap-2">
                        <span className="inline-block w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                        <span>API Powered &bull; React v19 SPA</span>
                    </p>
                </div>
            </footer>
        </div>
    );
}
