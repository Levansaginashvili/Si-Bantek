import React, { useState, useMemo } from 'react';
import { useForm, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Plus, X, Search } from 'lucide-react';

interface Props {
    users: Array<{
        id: number;
        name: string;
        username?: string;
        nip?: string;
        npsn?: string;
        email?: string;
        role: string;
        created_at: string;
        sekolah?: { id: number; nama_sekolah: string; npsn: string };
    }>;
}

const roleBadge: Record<string, string> = {
    admin: 'bg-slate-100 text-slate-700',
    verifikator: 'bg-blue-50 text-blue-700',
    sekolah: 'bg-indigo-50 text-indigo-700',
};

export default function UsersPage({ users }: Props) {
    const [modalOpen, setModalOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [filterRole, setFilterRole] = useState('semua');

    const { data, setData, post, processing, errors, reset } = useForm({
        role: 'verifikator',
        name: '',
        nip: '',
        npsn: '',
        nama_sekolah: '',
        provinsi: '',
        kabupaten: '',
        password: '',
    });

    const filteredUsers = useMemo(() => {
        return users.filter((u) => {
            const identityStr = (u.username || u.nip || u.npsn || u.email || '').toLowerCase();
            const matchesSearch =
                u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                identityStr.includes(searchQuery.toLowerCase());

            const matchesRole = filterRole === 'semua' || u.role === filterRole;

            return matchesSearch && matchesRole;
        });
    }, [users, searchQuery, filterRole]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/users', {
            onSuccess: () => { reset(); setModalOpen(false); },
        });
    };

    return (
        <AppLayout title="Kelola Akun Pengguna">
            <Head title="Kelola Akun — SI BANTEK 2026" />

            <div className="space-y-5">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-bold text-slate-800">Kelola Akun Pengguna</h2>
                        <p className="mt-1 text-sm text-slate-500">Buat akun login Verifikator (NIP) dan Sekolah (NPSN).</p>
                    </div>
                    <button
                        onClick={() => setModalOpen(true)}
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
                            <div className="relative w-full sm:w-64">
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
                                <option value="sekolah">Sekolah</option>
                            </select>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['No', 'Nama / Sekolah', 'Identitas Login (Username / NIP / NPSN)', 'Role'].map((h) => (
                                        <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white">
                                {filteredUsers.length === 0 ? (
                                    <tr>
                                        <td colSpan={4} className="px-5 py-8 text-center text-sm text-slate-400">
                                            Tidak ditemukan akun pengguna yang sesuai pencarian/filter.
                                        </td>
                                    </tr>
                                ) : (
                                    filteredUsers.map((u, idx) => (
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
                                                    {u.role}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h3 className="text-base font-semibold text-slate-800">Buat Akun Baru</h3>
                            <button onClick={() => setModalOpen(false)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="px-6 py-5 space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Pilih Role Akun</label>
                                <select
                                    value={data.role}
                                    onChange={(e) => setData('role', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                >
                                    <option value="verifikator">Verifikator Direktorat (Login via NIP)</option>
                                    <option value="sekolah">Sekolah Penerima (Login via NPSN)</option>
                                </select>
                            </div>

                            {data.role === 'verifikator' && (
                                <>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nama Verifikator & Gelar</label>
                                        <input
                                            type="text"
                                            required
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="Contoh: Drs. Budi Santoso, M.Si."
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">NIP (Nomor Induk Pegawai)</label>
                                        <input
                                            type="text"
                                            required
                                            value={data.nip}
                                            onChange={(e) => setData('nip', e.target.value)}
                                            placeholder="Contoh: 197803152003121002"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {errors.nip && <p className="mt-1 text-xs text-red-600">{errors.nip}</p>}
                                    </div>
                                </>
                            )}

                            {data.role === 'sekolah' && (
                                <>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">NPSN Sekolah</label>
                                        <input
                                            type="text"
                                            required
                                            value={data.npsn}
                                            onChange={(e) => setData('npsn', e.target.value)}
                                            placeholder="Contoh: 10110698"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {errors.npsn && <p className="mt-1 text-xs text-red-600">{errors.npsn}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nama Satuan Pendidikan</label>
                                        <input
                                            type="text"
                                            required
                                            value={data.nama_sekolah}
                                            onChange={(e) => setData('nama_sekolah', e.target.value)}
                                            placeholder="Contoh: SMP NEGERI 3 WOYLA TIMUR"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {errors.nama_sekolah && <p className="mt-1 text-xs text-red-600">{errors.nama_sekolah}</p>}
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1">Provinsi</label>
                                            <input
                                                type="text"
                                                required
                                                value={data.provinsi}
                                                onChange={(e) => setData('provinsi', e.target.value)}
                                                placeholder="Prov. Aceh"
                                                className="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1">Kabupaten / Kota</label>
                                            <input
                                                type="text"
                                                required
                                                value={data.kabupaten}
                                                onChange={(e) => setData('kabupaten', e.target.value)}
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
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Minimal 8 karakter"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
                            </div>

                            <div className="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setModalOpen(false)}
                                    className="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    {processing ? 'Menyimpan...' : 'Simpan Akun'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
