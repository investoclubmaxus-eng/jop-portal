import React from 'react';
import { Link, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function PublicLayout() {
    const { user } = useAuth();

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-indigo-500 selection:text-white">
            {/* Top Navigation */}
            <header className="sticky top-0 z-30 bg-slate-900/95 backdrop-blur-md border-b border-slate-800">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <Link to="/jobs" className="flex items-center gap-3 group">
                        <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                            <i className="fa-solid fa-briefcase text-base"></i>
                        </div>
                        <div>
                            <span className="font-extrabold text-lg tracking-tight text-white group-hover:text-indigo-400 transition-colors">JobPortal</span>
                            <span className="text-xs font-semibold text-indigo-400 block -mt-1 tracking-wide">CAREERS</span>
                        </div>
                    </Link>

                    <div className="flex items-center gap-4">
                        <Link
                            to="/jobs"
                            className="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-300 hover:text-white px-3 py-1.5 rounded-lg hover:bg-slate-800 transition"
                        >
                            <i className="fa-solid fa-magnifying-glass text-xs text-slate-400"></i>
                            <span>All Openings</span>
                        </Link>

                        {user ? (
                            <Link
                                to="/admin/dashboard"
                                className="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30 transition"
                            >
                                <i className="fa-solid fa-gauge-high"></i>
                                <span>Admin Dashboard ({user.name})</span>
                            </Link>
                        ) : (
                            <Link
                                to="/admin/login"
                                className="inline-flex items-center gap-2 text-xs font-semibold px-3.5 py-2 rounded-xl border border-slate-700 hover:border-slate-600 bg-slate-800/80 hover:bg-slate-800 text-slate-300 hover:text-white transition"
                            >
                                <i className="fa-solid fa-lock text-slate-400"></i>
                                <span>Recruiter Login</span>
                            </Link>
                        )}
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="flex-1 bg-slate-950">
                <Outlet />
            </main>

            {/* Footer */}
            <footer className="bg-slate-900 border-t border-slate-800 py-10 px-4 sm:px-6 lg:px-8 mt-auto">
                <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-indigo-600/30">
                            JP
                        </div>
                        <div>
                            <p className="font-bold text-sm text-white">JobPortal Careers</p>
                            <p className="text-xs text-slate-400">Building top talent teams worldwide</p>
                        </div>
                    </div>

                    <div className="flex items-center gap-6 text-xs text-slate-400">
                        <Link to="/jobs" className="hover:text-indigo-400 transition">Browse Vacancies</Link>
                        <span className="text-slate-700">&bull;</span>
                        <Link to="/admin/login" className="hover:text-indigo-400 transition">Hiring Team Portal</Link>
                        <span className="text-slate-700">&bull;</span>
                        <span className="text-slate-500">&copy; {new Date().getFullYear()} JobPortal Recruitment Inc.</span>
                    </div>
                </div>
            </footer>
        </div>
    );
}
