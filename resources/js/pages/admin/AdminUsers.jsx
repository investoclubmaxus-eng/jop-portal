import React, { useState, useEffect } from 'react';
import api from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { showSuccess, showError, showConfirm } from '../../utils/alerts';

export default function AdminUsers() {
    const { user: currentUser } = useAuth();
    const [users, setUsers] = useState([]);
    const [stats, setStats] = useState({ total: 0, active: 0, inactive: 0, trashed: 0 });
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [loading, setLoading] = useState(true);
    const [statusFilter, setStatusFilter] = useState('all');
    const [search, setSearch] = useState('');
    const [roleFilter, setRoleFilter] = useState('');

    // Modal state
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingUser, setEditingUser] = useState(null);
    const [showPassword, setShowPassword] = useState(false);
    const [modalForm, setModalForm] = useState({
        name: '',
        email: '',
        password: '',
        role: 'admin',
        status: 'active',
    });
    const [modalErrors, setModalErrors] = useState({});
    const [modalSubmitting, setModalSubmitting] = useState(false);
    const [toastMessage, setToastMessage] = useState('');
    const [errorMessage, setErrorMessage] = useState('');

    const fetchUsers = async (page = 1) => {
        setLoading(true);
        try {
            const params = {
                page,
                status: statusFilter,
                role: roleFilter || undefined,
                search: search || undefined,
            };
            const res = await api.get('/admin/users', { params });
            setUsers(res.data.users);
            setStats(res.data.stats);
            setPagination(res.data.pagination);
        } catch {
            setErrorMessage('Failed to load admin accounts.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUsers(1);
    }, [statusFilter, roleFilter]);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        fetchUsers(1);
    };

    const showToast = (msg) => {
        setToastMessage(msg);
        setTimeout(() => setToastMessage(''), 4000);
    };

    const openCreateModal = () => {
        setEditingUser(null);
        setShowPassword(false);
        setModalForm({
            name: '',
            email: '',
            password: '',
            role: 'admin',
            status: 'active',
        });
        setModalErrors({});
        setIsModalOpen(true);
    };

    const openEditModal = (user) => {
        setEditingUser(user);
        setShowPassword(false);
        setModalForm({
            name: user.name,
            email: user.email,
            password: '', // leave empty unless changing
            role: user.role,
            status: user.status,
        });
        setModalErrors({});
        setIsModalOpen(true);
    };

    const handleModalSubmit = async (e) => {
        e.preventDefault();
        setModalSubmitting(true);
        setModalErrors({});

        try {
            const payload = {
                ...modalForm,
                password_confirmation: modalForm.password,
            };

            if (editingUser) {
                // Update
                if (!payload.password) {
                    delete payload.password;
                    delete payload.password_confirmation;
                }
                const res = await api.put(`/admin/users/${editingUser.id}`, payload);
                await showSuccess('Account Updated', res.data.message);
            } else {
                // Create
                const res = await api.post('/admin/users', payload);
                await showSuccess('Admin Created', res.data.message);
            }
            setIsModalOpen(false);
            fetchUsers(pagination.current_page);
        } catch (err) {
            if (err.response?.status === 422 && err.response.data?.errors) {
                setModalErrors(err.response.data.errors);
                const firstError = Object.values(err.response.data.errors)[0]?.[0];
                showError('Validation Error', firstError || 'Please correct the highlighted errors.');
            } else {
                setModalErrors({ general: [err.response?.data?.message || 'Operation failed.'] });
                showError('Error', err.response?.data?.message || 'Operation failed.');
            }
        } finally {
            setModalSubmitting(false);
        }
    };

    const handleToggleStatus = async (user) => {
        try {
            const res = await api.patch(`/admin/users/${user.id}/toggle-status`);
            await showSuccess('Status Updated', res.data.message);
            fetchUsers(pagination.current_page);
        } catch (err) {
            showError('Error', err.response?.data?.message || 'Failed to toggle status.');
        }
    };

    const handleDelete = async (user) => {
        const confirmed = await showConfirm(
            'Move to Trash?',
            `Are you sure you want to move ${user.name} to trash?`,
            'Yes, Move to Trash'
        );
        if (!confirmed) return;

        try {
            const res = await api.delete(`/admin/users/${user.id}`);
            await showSuccess('Account Trashed', res.data.message);
            fetchUsers(pagination.current_page);
        } catch (err) {
            showError('Delete Failed', err.response?.data?.message || 'Failed to delete user.');
        }
    };

    const handleRestore = async (userId) => {
        try {
            const res = await api.post(`/admin/users/${userId}/restore`);
            await showSuccess('Account Restored', res.data.message);
            fetchUsers(pagination.current_page);
        } catch (err) {
            showError('Restore Failed', err.response?.data?.message || 'Failed to restore user.');
        }
    };

    return (
        <div className="space-y-6">
            {/* Page Header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-white tracking-tight">Admin & Recruiter Accounts</h1>
                    <p className="text-sm text-slate-400 mt-1">Manage portal administrator permissions, login activity, and active statuses</p>
                </div>
                <button
                    type="button"
                    onClick={openCreateModal}
                    className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-lg shadow-indigo-600/30 transition self-start sm:self-auto"
                >
                    <i className="fa-solid fa-user-plus text-xs"></i>
                    <span>Add New Admin</span>
                </button>
            </div>

            {/* Notification Toasts */}
            {toastMessage && (
                <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center justify-between">
                    <span className="flex items-center gap-2">
                        <i className="fa-solid fa-circle-check"></i>
                        {toastMessage}
                    </span>
                    <button onClick={() => setToastMessage('')} className="text-emerald-400 hover:text-emerald-200">
                        <i className="fa-solid fa-xmark"></i>
                    </button>
                </div>
            )}
            {errorMessage && (
                <div className="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center justify-between">
                    <span className="flex items-center gap-2">
                        <i className="fa-solid fa-triangle-exclamation"></i>
                        {errorMessage}
                    </span>
                    <button onClick={() => setErrorMessage('')} className="text-rose-400 hover:text-rose-200">
                        <i className="fa-solid fa-xmark"></i>
                    </button>
                </div>
            )}

            {/* Stats Cards */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    onClick={() => setStatusFilter('all')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'all'
                            ? 'bg-indigo-950/40 border-indigo-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Users</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.total}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('active')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'active'
                            ? 'bg-emerald-950/40 border-emerald-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-emerald-400">Active Accounts</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.active}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('inactive')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'inactive'
                            ? 'bg-amber-950/40 border-amber-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-amber-400">Inactive Accounts</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.inactive}</p>
                </div>
                <div
                    onClick={() => setStatusFilter('trashed')}
                    className={`cursor-pointer p-4 rounded-2xl border transition ${
                        statusFilter === 'trashed'
                            ? 'bg-rose-950/40 border-rose-500/50 text-white'
                            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'
                    }`}
                >
                    <p className="text-xs font-semibold uppercase tracking-wider text-rose-400">Trash / Soft Deleted</p>
                    <p className="text-2xl font-bold text-white mt-1">{stats.trashed}</p>
                </div>
            </div>

            {/* Filter Bar */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row gap-4 justify-between items-center">
                <form onSubmit={handleSearchSubmit} className="w-full md:w-80 flex gap-2">
                    <div className="relative flex-1">
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i className="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by name or email..."
                            className="w-full pl-9 pr-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                    <button
                        type="submit"
                        className="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 rounded-xl transition"
                    >
                        Search
                    </button>
                </form>

                <div className="w-full md:w-auto flex items-center gap-3">
                    <select
                        value={roleFilter}
                        onChange={(e) => setRoleFilter(e.target.value)}
                        className="px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Roles</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="recruiter">Recruiter</option>
                        <option value="hr">HR</option>
                    </select>

                    <button
                        type="button"
                        onClick={() => {
                            setSearch('');
                            setRoleFilter('');
                            setStatusFilter('all');
                            fetchUsers(1);
                        }}
                        className="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 text-xs"
                        title="Reset Filters"
                    >
                        <i className="fa-solid fa-arrow-rotate-left"></i>
                    </button>
                </div>
            </div>

            {/* Table */}
            <div className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-slate-800/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <tr>
                                <th className="px-6 py-3.5">Administrator</th>
                                <th className="px-6 py-3.5">Role</th>
                                <th className="px-6 py-3.5">Status</th>
                                <th className="px-6 py-3.5">Last Login</th>
                                <th className="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                            {loading ? (
                                <tr>
                                    <td colSpan="5" className="px-6 py-12 text-center text-slate-400">
                                        <div className="w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                                        <span>Loading admin team accounts...</span>
                                    </td>
                                </tr>
                            ) : users.length === 0 ? (
                                <tr>
                                    <td colSpan="5" className="px-6 py-12 text-center text-slate-400">
                                        <i className="fa-solid fa-users text-2xl text-slate-400 mb-2 block"></i>
                                        <span>No administrators found matching current filters.</span>
                                    </td>
                                </tr>
                            ) : (
                                users.map((u) => (
                                    <tr key={u.id} className="hover:bg-slate-800/40 transition">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-white text-xs">
                                                    {u.name.charAt(0)}
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-semibold text-slate-100">{u.name}</span>
                                                        {u.is_current_user && (
                                                            <span className="px-1.5 py-0.5 rounded text-[10px] bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                                                                You
                                                            </span>
                                                        )}
                                                    </div>
                                                    <span className="text-slate-400 block">{u.email}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                                {u.role_label || u.role}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            {u.is_trashed ? (
                                                <span className="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                    Trashed
                                                </span>
                                            ) : u.status === 'active' ? (
                                                <button
                                                    type="button"
                                                    disabled={u.is_current_user}
                                                    onClick={() => handleToggleStatus(u)}
                                                    className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition disabled:opacity-50"
                                                    title={u.is_current_user ? "Cannot toggle own status" : "Click to toggle Active/Inactive"}
                                                >
                                                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                    <span>Active</span>
                                                </button>
                                            ) : (
                                                <button
                                                    type="button"
                                                    disabled={u.is_current_user}
                                                    onClick={() => handleToggleStatus(u)}
                                                    className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition disabled:opacity-50"
                                                    title={u.is_current_user ? "Cannot toggle own status" : "Click to toggle Active/Inactive"}
                                                >
                                                    <span className="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                                    <span>Inactive</span>
                                                </button>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-slate-400">
                                            {u.last_login_at}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                {u.is_trashed ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRestore(u.id)}
                                                        className="px-2.5 py-1 rounded-lg bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600/30 text-xs font-medium transition"
                                                    >
                                                        <i className="fa-solid fa-rotate-left mr-1"></i>
                                                        Restore
                                                    </button>
                                                ) : (
                                                    <>
                                                        <button
                                                            type="button"
                                                            onClick={() => openEditModal(u)}
                                                            className="p-1.5 rounded-lg text-slate-400 hover:text-indigo-400 hover:bg-slate-800 transition"
                                                            title="Edit Account"
                                                        >
                                                            <i className="fa-solid fa-pen-to-square"></i>
                                                        </button>
                                                        {!u.is_current_user && (
                                                            <button
                                                                type="button"
                                                                onClick={() => handleDelete(u)}
                                                                className="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition"
                                                                title="Soft Delete"
                                                            >
                                                                <i className="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        )}
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {pagination.last_page > 1 && (
                    <div className="px-6 py-3.5 bg-slate-800/40 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <span>Showing page {pagination.current_page} of {pagination.last_page} ({pagination.total} total)</span>
                        <div className="flex gap-1">
                            <button
                                disabled={pagination.current_page <= 1}
                                onClick={() => fetchUsers(pagination.current_page - 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 transition"
                            >
                                Prev
                            </button>
                            <button
                                disabled={pagination.current_page >= pagination.last_page}
                                onClick={() => fetchUsers(pagination.current_page + 1)}
                                className="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 transition"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Create/Edit Admin Modal */}
            {isModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
                        <div className="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                            <h3 className="text-base font-bold text-white">
                                {editingUser ? `Edit Administrator: ${editingUser.name}` : 'Create Administrator Account'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsModalOpen(false)}
                                className="text-slate-400 hover:text-white"
                            >
                                <i className="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        {modalErrors.general && (
                            <div className="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
                                {modalErrors.general[0]}
                            </div>
                        )}

                        <form onSubmit={handleModalSubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                    Full Name
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={modalForm.name}
                                    onChange={(e) => setModalForm({ ...modalForm, name: e.target.value })}
                                    className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                                {modalErrors.name && <p className="mt-1 text-xs text-rose-400">{modalErrors.name[0]}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                    Email Address
                                </label>
                                <input
                                    type="email"
                                    required
                                    value={modalForm.email}
                                    onChange={(e) => setModalForm({ ...modalForm, email: e.target.value })}
                                    className="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                                {modalErrors.email && <p className="mt-1 text-xs text-rose-400">{modalErrors.email[0]}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                    {editingUser ? 'Password (Leave blank to keep unchanged)' : 'Initial Password'}
                                </label>
                                <div className="relative">
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        required={!editingUser}
                                        value={modalForm.password}
                                        onChange={(e) => setModalForm({ ...modalForm, password: e.target.value })}
                                        placeholder={editingUser ? '••••••••' : 'Min 8 characters'}
                                        className="w-full px-3.5 py-2.5 pr-10 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white p-1 transition cursor-pointer"
                                        tabIndex={-1}
                                        title={showPassword ? 'Hide password' : 'Show password'}
                                    >
                                        <i className={`fa-solid ${showPassword ? 'fa-eye-slash' : 'fa-eye'} text-xs`}></i>
                                    </button>
                                </div>
                                {modalErrors.password && <p className="mt-1 text-xs text-rose-400">{modalErrors.password[0]}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                        Role
                                    </label>
                                    <select
                                        value={modalForm.role}
                                        onChange={(e) => setModalForm({ ...modalForm, role: e.target.value })}
                                        className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="super_admin">Super Admin</option>
                                        <option value="admin">Admin</option>
                                        <option value="recruiter">Recruiter</option>
                                        <option value="hr">HR</option>
                                    </select>
                                    {modalErrors.role && <p className="mt-1 text-xs text-rose-400">{modalErrors.role[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 mb-1 uppercase tracking-wider">
                                        Status
                                    </label>
                                    <select
                                        value={modalForm.status}
                                        onChange={(e) => setModalForm({ ...modalForm, status: e.target.value })}
                                        className="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    {modalErrors.status && <p className="mt-1 text-xs text-rose-400">{modalErrors.status[0]}</p>}
                                </div>
                            </div>

                            <div className="pt-3 flex justify-end gap-2 border-t border-slate-800 mt-4">
                                <button
                                    type="button"
                                    onClick={() => setIsModalOpen(false)}
                                    className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={modalSubmitting}
                                    className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white transition disabled:opacity-50"
                                >
                                    {modalSubmitting ? 'Saving...' : editingUser ? 'Update Admin' : 'Create Admin'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
