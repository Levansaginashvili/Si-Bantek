import React, { useState } from 'react';
import { useForm, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Plus, X } from 'lucide-react';

interface Props {
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        created_at: string;
        sekolah?: { id: number; nama_sekolah: string; npsn: string };
    }>;
    sekolahs: Array<{ id: number; nama_sekolah: string; npsn: string }>;
}

const roleBadge: Record<string, string> = {
    admin: 'bg-slate-100 text-slate-700',
    verifikator: 'bg-blue-50 text-blue-700',
    sekolah: 'bg-indigo-50 text-indigo-700',
};

export default function UsersPage({ users, sekolahs }: Props) {
    const [modalOpen, setModalOpen] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        role: 'verifikator',
        sekolah_id: '',
    });

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
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-bold text-slate-800">Kelola Akun Pengguna</h2>
                        <p className="mt-1 text-sm text-slate-500">Buat akun login untuk Verifikator Direktorat dan Operator Sekolah.</p>
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
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['No', 'Nama', 'Email', 'Peran', 'Satuan Pendidikan'].map((h) => (
                                        <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white">
                                {users.map((u, idx) => (
                                    <tr key={u.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="px-5 py-3.5 text-slate-500">{idx + 1}</td>
                                        <td className="px-5 py-3.5 font-medium text-slate-800">{u.name}</td>
                                        <td className="px-5 py-3.5 font-mono text-slate-600">{u.email}</td>
                                        <td className="px-5 py-3.5">
                                            <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${roleBadge[u.role] ?? 'bg-slate-100 text-slate-700'}`}>
                                                {u.role}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3.5 text-slate-500">
                                            {u.sekolah ? `${u.sekolah.nama_sekolah} (${u.sekolah.npsn})` : '—'}
                                        </td>
                                    </tr>
                                ))}
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
                            {[
                                { key: 'name', label: 'Nama Pengguna', type: 'text', placeholder: 'Contoh: Operator SMPN 1' },
                                { key: 'email', label: 'Email Login', type: 'email', placeholder: 'email@kemendikdasmen.go.id' },
                                { key: 'password', label: 'Kata Sandi (Min. 8 karakter)', type: 'password', placeholder: '' },
                            ].map(({ key, label, type, placeholder }) => (
                                <div key={key}>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">{label}</label>
                                    <input
                                        type={type}
                                        required
                                        value={(data as any)[key]}
                                        onChange={(e) => setData(key as any, e.target.value)}
                                        placeholder={placeholder}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {(errors as any)[key] && <p className="mt-1 text-xs text-red-600">{(errors as any)[key]}</p>}
                                </div>
                            ))}

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Peran</label>
                                <select
                                    value={data.role}
                                    onChange={(e) => setData('role', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                >
                                    <option value="verifikator">Verifikator Direktorat</option>
                                    <option value="sekolah">Operator Sekolah</option>
                                </select>
                            </div>

                            {data.role === 'sekolah' && (
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Satuan Pendidikan</label>
                                    <select
                                        value={data.sekolah_id}
                                        onChange={(e) => setData('sekolah_id', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    >
                                        <option value="">— Pilih Sekolah —</option>
                                        {sekolahs.map((s) => (
                                            <option key={s.id} value={s.id}>{s.nama_sekolah} ({s.npsn})</option>
                                        ))}
                                    </select>
                                    {errors.sekolah_id && <p className="mt-1 text-xs text-red-600">{errors.sekolah_id}</p>}
                                </div>
                            )}

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
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
