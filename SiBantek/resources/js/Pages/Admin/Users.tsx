import React, { useState, useMemo } from 'react';
import { useForm, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Plus, X, Search, Edit2, ShieldAlert, ShieldCheck } from 'lucide-react';

interface UserItem {
    id: number;
    name: string;
    username?: string;
    nip?: string;
    npsn?: string;
    email?: string;
    role: string;
    status?: string;
    catatan_nonaktif?: string;
    created_at: string;
    sekolah?: { id: number; nama_sekolah: string; npsn: string };
}

interface Props {
    users: UserItem[];
}

const roleBadge: Record<string, string> = {
    admin: 'bg-slate-100 text-slate-700',
    verifikator: 'bg-blue-50 text-blue-700',
    sekolah: 'bg-indigo-50 text-indigo-700',
};

export default function UsersPage({ users }: Props) {
    const [createModalOpen, setCreateModalOpen] = useState(false);
    const [editModalUser, setEditModalUser] = useState<UserItem | null>(null);
    const [statusModalUser, setStatusModalUser] = useState<UserItem | null>(null);

    const [searchQuery, setSearchQuery] = useState('');
    const [filterRole, setFilterRole] = useState('semua');
    const [filterStatus, setFilterStatus] = useState('semua');

    // Create Form
    const createForm = useForm({
        role: 'verifikator',
        name: '',
        nip: '',
        npsn: '',
        nama_sekolah: '',
        provinsi: '',
        kabupaten: '',
        password: '',
    });

    // Edit Form
    const editForm = useForm({
        name: '',
        nip: '',
        npsn: '',
        password: '',
    });

    // Status Form (Toggle Aktif / Nonaktif)
    const statusForm = useForm({
        status: 'nonaktif',
        catatan_nonaktif: '',
    });

    const filteredUsers = useMemo(() => {
        return users.filter((u) => {
            const identityStr = (u.username || u.nip || u.npsn || u.email || '').toLowerCase();
            const matchesSearch =
                u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                identityStr.includes(searchQuery.toLowerCase());

            const matchesRole = filterRole === 'semua' || u.role === filterRole;
            const userStatus = u.status ?? 'aktif';
            const matchesStatus = filterStatus === 'semua' || userStatus === filterStatus;

            return matchesSearch && matchesRole && matchesStatus;
        });
    }, [users, searchQuery, filterRole, filterStatus]);

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post('/admin/users', {
            onSuccess: () => { createForm.reset(); setCreateModalOpen(false); },
        });
    };

    const openEditModal = (u: UserItem) => {
        setEditModalUser(u);
        editForm.setData({
            name: u.name || '',
            nip: u.nip || '',
            npsn: u.npsn || '',
            password: '',
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!editModalUser) return;
        editForm.post(`/admin/users/${editModalUser.id}`, {
            onSuccess: () => { setEditModalUser(null); editForm.reset(); },
        });
    };

    const openStatusModal = (u: UserItem) => {
        setStatusModalUser(u);
        const nextStatus = (u.status ?? 'aktif') === 'aktif' ? 'nonaktif' : 'aktif';
        statusForm.setData({
            status: nextStatus,
            catatan_nonaktif: u.catatan_nonaktif || '',
        });
    };

    const handleStatusSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!statusModalUser) return;
        statusForm.post(`/admin/users/${statusModalUser.id}/status`, {
            onSuccess: () => { setStatusModalUser(null); statusForm.reset(); },
        });
    };

    return (
        <AppLayout title="Kelola Akun Pengguna">
            <Head title="Kelola Akun — Si Bantek" />

            <div className="space-y-5">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-bold text-slate-800">Kelola Akun Pengguna</h2>
                        <p className="mt-1 text-sm text-slate-500">Buat, perbarui profil, atau nonaktifkan akun Verifikator dan Operator Sekolah.</p>
                    </div>
                    <button
                        onClick={() => setCreateModalOpen(true)}
                        className="inline-flex items-center gap-2 rounded-lg bg-[#1e2d5a] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] transition-colors"
                    >
                        <Plus size={16} />
                        Tambah Akun
                    </button>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    {/* Search & Filter Bar */}
                    <div className="border-b border-slate-100 px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50">
                        <span className="text-xs font-semibold text-slate-600">
                            Terdaftar: {filteredUsers.length} dari {users.length} Akun
                        </span>

                        <div className="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                            <div className="relative w-full sm:w-56">
                                <Search size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input
                                    type="text"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    placeholder="Cari nama, NIP, NPSN..."
                                    className="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                            </div>

                            <select
                                value={filterRole}
                                onChange={(e) => setFilterRole(e.target.value)}
                                className="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                            >
                                <option value="semua">Semua Role</option>
                                <option value="admin">Admin</option>
                                <option value="verifikator">Verifikator</option>
                                <option value="sekolah">Operator Sekolah</option>
                            </select>

                            <select
                                value={filterStatus}
                                onChange={(e) => setFilterStatus(e.target.value)}
                                className="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                            >
                                <option value="semua">Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['No', 'Nama / Sekolah', 'Identitas Login', 'Role', 'Status', 'Aksi'].map((h) => (
                                        <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white">
                                {filteredUsers.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="px-5 py-8 text-center text-sm text-slate-400">
                                            Tidak ditemukan akun pengguna yang sesuai pencarian/filter.
                                        </td>
                                    </tr>
                                ) : (
                                    filteredUsers.map((u, idx) => {
                                        const isAktif = (u.status ?? 'aktif') === 'aktif';
                                        return (
                                            <tr key={u.id} className="hover:bg-slate-50 transition-colors">
                                                <td className="px-5 py-3.5 text-slate-500">{idx + 1}</td>
                                                <td className="px-5 py-3.5 font-medium text-slate-800">{u.name}</td>
                                                <td className="px-5 py-3.5 font-mono text-slate-700">
                                                    {u.username ? `Username: ${u.username}` :
                                                     u.nip ? `NIP: ${u.nip}` :
                                                     u.npsn ? `NPSN: ${u.npsn}` : u.email}
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${roleBadge[u.role] ?? 'bg-slate-100 text-slate-700'}`}>
                                                        {u.role === 'sekolah' ? 'Operator Sekolah' : u.role === 'verifikator' ? 'Verifikator' : 'Admin'}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    {isAktif ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                                            Aktif
                                                        </span>
                                                    ) : (
                                                        <div>
                                                            <span className="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                                                Nonaktif
                                                            </span>
                                                            {u.catatan_nonaktif && (
                                                                <p className="mt-0.5 text-[11px] text-red-500 max-w-xs truncate" title={u.catatan_nonaktif}>
                                                                    Ket: {u.catatan_nonaktif}
                                                                </p>
                                                            )}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    {u.role !== 'admin' && (
                                                        <div className="flex items-center gap-2">
                                                            <button
                                                                onClick={() => openEditModal(u)}
                                                                className="inline-flex items-center gap-1 rounded border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                                                            >
                                                                <Edit2 size={12} />
                                                                Edit
                                                            </button>

                                                            <button
                                                                onClick={() => openStatusModal(u)}
                                                                className={`inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium transition-colors ${
                                                                    isAktif
                                                                        ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                                                        : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                                                }`}
                                                            >
                                                                {isAktif ? <ShieldAlert size={12} /> : <ShieldCheck size={12} />}
                                                                {isAktif ? 'Nonaktifkan' : 'Aktifkan'}
                                                            </button>
                                                        </div>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Create Modal */}
            {createModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h3 className="text-base font-semibold text-slate-800">Buat Akun Baru</h3>
                            <button onClick={() => setCreateModalOpen(false)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>

                        <form onSubmit={handleCreateSubmit} className="px-6 py-5 space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Pilih Role Akun</label>
                                <select
                                    value={createForm.data.role}
                                    onChange={(e) => createForm.setData('role', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                >
                                    <option value="verifikator">Verifikator (Login via NIP)</option>
                                    <option value="sekolah">Operator Sekolah (Login via NPSN)</option>
                                </select>
                            </div>

                            {createForm.data.role === 'verifikator' && (
                                <>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nama Verifikator</label>
                                        <input
                                            type="text"
                                            required
                                            value={createForm.data.name}
                                            onChange={(e) => createForm.setData('name', e.target.value)}
                                            placeholder="Contoh: Drs. Budi Santoso, M.Si."
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {createForm.errors.name && <p className="mt-1 text-xs text-red-600">{createForm.errors.name}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">NIP (Nomor Induk Pegawai)</label>
                                        <input
                                            type="text"
                                            required
                                            value={createForm.data.nip}
                                            onChange={(e) => createForm.setData('nip', e.target.value)}
                                            placeholder="Contoh: 197803152003121002"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {createForm.errors.nip && <p className="mt-1 text-xs text-red-600">{createForm.errors.nip}</p>}
                                    </div>
                                </>
                            )}

                            {createForm.data.role === 'sekolah' && (
                                <>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">NPSN Sekolah</label>
                                        <input
                                            type="text"
                                            required
                                            value={createForm.data.npsn}
                                            onChange={(e) => createForm.setData('npsn', e.target.value)}
                                            placeholder="Contoh: 10110698"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {createForm.errors.npsn && <p className="mt-1 text-xs text-red-600">{createForm.errors.npsn}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nama Satuan Pendidikan</label>
                                        <input
                                            type="text"
                                            required
                                            value={createForm.data.nama_sekolah}
                                            onChange={(e) => createForm.setData('nama_sekolah', e.target.value)}
                                            placeholder="Contoh: SMP NEGERI 3 WOYLA TIMUR"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {createForm.errors.nama_sekolah && <p className="mt-1 text-xs text-red-600">{createForm.errors.nama_sekolah}</p>}
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1">Provinsi</label>
                                            <input
                                                type="text"
                                                required
                                                value={createForm.data.provinsi}
                                                onChange={(e) => createForm.setData('provinsi', e.target.value)}
                                                placeholder="Prov. Aceh"
                                                className="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1">Kabupaten / Kota</label>
                                            <input
                                                type="text"
                                                required
                                                value={createForm.data.kabupaten}
                                                onChange={(e) => createForm.setData('kabupaten', e.target.value)}
                                                placeholder="Kab. Aceh Barat"
                                                className="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                            />
                                        </div>
                                    </div>
                                </>
                            )}

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Kata Sandi Initial</label>
                                <input
                                    type="password"
                                    required
                                    value={createForm.data.password}
                                    onChange={(e) => createForm.setData('password', e.target.value)}
                                    placeholder="Minimal 8 karakter"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {createForm.errors.password && <p className="mt-1 text-xs text-red-600">{createForm.errors.password}</p>}
                            </div>

                            <div className="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setCreateModalOpen(false)}
                                    className="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={createForm.processing}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    {createForm.processing ? 'Menyimpan...' : 'Simpan Akun'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Edit Modal */}
            {editModalUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h3 className="text-base font-semibold text-slate-800">Edit Akun Pengguna</h3>
                            <button onClick={() => setEditModalUser(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>

                        <form onSubmit={handleEditSubmit} className="px-6 py-5 space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">
                                    {editModalUser.role === 'sekolah' ? 'Nama Satuan Pendidikan' : 'Nama Lengkap'}
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={editForm.data.name}
                                    onChange={(e) => editForm.setData('name', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {editForm.errors.name && <p className="mt-1 text-xs text-red-600">{editForm.errors.name}</p>}
                            </div>

                            {editModalUser.role === 'verifikator' && (
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">NIP (Nomor Induk Pegawai)</label>
                                    <input
                                        type="text"
                                        required
                                        value={editForm.data.nip}
                                        onChange={(e) => editForm.setData('nip', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {editForm.errors.nip && <p className="mt-1 text-xs text-red-600">{editForm.errors.nip}</p>}
                                </div>
                            )}

                            {editModalUser.role === 'sekolah' && (
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">NPSN Sekolah</label>
                                    <input
                                        type="text"
                                        required
                                        value={editForm.data.npsn}
                                        onChange={(e) => editForm.setData('npsn', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {editForm.errors.npsn && <p className="mt-1 text-xs text-red-600">{editForm.errors.npsn}</p>}
                                </div>
                            )}

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Kata Sandi Baru (kosongkan jika tidak diubah)</label>
                                <input
                                    type="password"
                                    value={editForm.data.password}
                                    onChange={(e) => editForm.setData('password', e.target.value)}
                                    placeholder="Minimal 8 karakter"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {editForm.errors.password && <p className="mt-1 text-xs text-red-600">{editForm.errors.password}</p>}
                            </div>

                            <div className="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setEditModalUser(null)}
                                    className="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    {editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Status (Nonaktifkan / Aktifkan) Modal */}
            {statusModalUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h3 className="text-base font-semibold text-slate-800">
                                {statusForm.data.status === 'nonaktif' ? 'Nonaktifkan Akun' : 'Aktifkan Kembali Akun'}
                            </h3>
                            <button onClick={() => setStatusModalUser(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>

                        <form onSubmit={handleStatusSubmit} className="px-6 py-5 space-y-4">
                            <p className="text-xs text-slate-600">
                                Akun: <span className="font-semibold text-slate-800">{statusModalUser.name}</span>
                            </p>

                            {statusForm.data.status === 'nonaktif' ? (
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">
                                        Catatan / Alasan Penonaktifan
                                    </label>
                                    <textarea
                                        rows={3}
                                        required
                                        value={statusForm.data.catatan_nonaktif}
                                        onChange={(e) => statusForm.setData('catatan_nonaktif', e.target.value)}
                                        placeholder="Contoh: Perubahan operator sekolah, akun duplikat, atau permintaan resmi..."
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a] resize-none"
                                    />
                                    {statusForm.errors.catatan_nonaktif && <p className="mt-1 text-xs text-red-600">{statusForm.errors.catatan_nonaktif}</p>}
                                </div>
                            ) : (
                                <p className="text-xs text-slate-500">
                                    Apakah Anda yakin ingin mengaktifkan kembali akun ini agar pengguna dapat kembali melakukan login?
                                </p>
                            )}

                            <div className="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setStatusModalUser(null)}
                                    className="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={statusForm.processing}
                                    className={`rounded-lg px-4 py-2 text-sm font-semibold text-white transition-colors ${
                                        statusForm.data.status === 'nonaktif'
                                            ? 'bg-red-600 hover:bg-red-700'
                                            : 'bg-emerald-600 hover:bg-emerald-700'
                                    }`}
                                >
                                    {statusForm.processing
                                        ? 'Memproses...'
                                        : statusForm.data.status === 'nonaktif'
                                        ? 'Nonaktifkan Akun'
                                        : 'Aktifkan Akun'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
