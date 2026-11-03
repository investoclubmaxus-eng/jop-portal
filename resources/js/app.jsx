import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import AdminLayout from './components/AdminLayout';
import PublicLayout from './components/PublicLayout';

// Admin Pages
import Login from './pages/admin/Login';
import ForgotPassword from './pages/admin/ForgotPassword';
import ResetPassword from './pages/admin/ResetPassword';
import Dashboard from './pages/admin/Dashboard';
import JobsList from './pages/admin/JobsList';
import JobCreate from './pages/admin/JobCreate';
import JobEdit from './pages/admin/JobEdit';
import JobDetails from './pages/admin/JobDetails';
import ApplicationsList from './pages/admin/ApplicationsList';
import AdminUsers from './pages/admin/AdminUsers';
import Profile from './pages/admin/Profile';

// Public Pages
import JobList from './pages/public/JobList';
import JobDetail from './pages/public/JobDetail';
import DirectApply from './pages/public/DirectApply';

function NotFound() {
    return (
        <div className="min-h-screen bg-slate-950 flex flex-col items-center justify-center text-center p-6">
            <h1 className="text-6xl font-extrabold text-indigo-500 mb-2">404</h1>
            <p className="text-lg font-bold text-white mb-1">Page Not Found</p>
            <p className="text-xs text-slate-400 max-w-sm mb-6">The requested URL does not exist or has been moved.</p>
            <a
                href="/jobs"
                className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 transition"
            >
                Return to Careers Portal
            </a>
        </div>
    );
}

export default function App() {
    return (
        <AuthProvider>
            <BrowserRouter>
                <Routes>
                    {/* Public Candidate Portal */}
                    <Route element={<PublicLayout />}>
                        <Route path="/" element={<Navigate to="/jobs" replace />} />
                        <Route path="/jobs" element={<JobList />} />
                        <Route path="/jobs/:jobCode" element={<JobDetail />} />
                        <Route path="/jobs/:jobCode/apply" element={<DirectApply />} />
                    </Route>

                    {/* Admin Guest Authentication */}
                    <Route path="/admin/login" element={<Login />} />
                    <Route path="/admin/forgot-password" element={<ForgotPassword />} />
                    <Route path="/admin/reset-password" element={<ResetPassword />} />

                    {/* Admin Protected Area */}
                    <Route element={<ProtectedRoute />}>
                        <Route element={<AdminLayout />}>
                            <Route path="/admin" element={<Navigate to="/admin/dashboard" replace />} />
                            <Route path="/admin/dashboard" element={<Dashboard />} />
                            <Route path="/admin/jobs" element={<JobsList />} />
                            <Route path="/admin/jobs/create" element={<JobCreate />} />
                            <Route path="/admin/jobs/:id" element={<JobDetails />} />
                            <Route path="/admin/jobs/:id/edit" element={<JobEdit />} />
                            <Route path="/admin/applications" element={<ApplicationsList />} />
                            <Route path="/admin/users" element={<AdminUsers />} />
                            <Route path="/admin/profile" element={<Profile />} />
                        </Route>
                    </Route>

                    {/* 404 Fallback */}
                    <Route path="*" element={<NotFound />} />
                </Routes>
            </BrowserRouter>
        </AuthProvider>
    );
}

const container = document.getElementById('app');

if (container) {
    // Check if root already exists to prevent duplicate createRoot calls
    if (!container._reactRoot) {
        container._reactRoot = ReactDOM.createRoot(container);
    }

    container._reactRoot.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}