import React from 'react';
import { useForm, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

interface Props {
    user: {
        id: number;
        name: string;
        username?: string;
        nip?: string;
        npsn?: string;
        email?: string;
        role: string;
    };
    sekolah?: {
        id: number;
        npsn: string;
        nama_sekolah: string;
        provinsi: string;
        kabupaten: string;
        alamat?: string;
        nama_kepsek?: string;
        nip_kepsek?: string;
        nama_bendahara?: string;
        nip_bendahara?: string;
    } | null;
}

export default function ProfileEdit({ user, sekolah }: Props) {
    const formAdmin = useForm({
        name: user.name || '',
        username: user.username || '',
        password: '',
    });

    const formVerifikator = useForm({
        name: user.name || '',
        nip: user.nip || '',
        password: '',
    });

    const formSekolah = useForm({
        nama_sekolah: sekolah?.nama_sekolah || user.name || '',
        npsn: sekolah?.npsn || user.npsn || '',
        provinsi: sekolah?.provinsi || '',
        kabupaten: sekolah?.kabupaten || '',
        alamat: sekolah?.alamat || '',
        nama_kepsek: sekolah?.nama_kepsek || '',
        nip_kepsek: sekolah?.nip_kepsek || '',
        nama_bendahara: sekolah?.nama_bendahara || '',
        nip_bendahara: sekolah?.nip_bendahara || '',
        password: '',
    });

    const handleSubmitAdmin = (e: React.FormEvent) => {
        e.preventDefault();
        formAdmin.post('/profile');
    };

    const handleSubmitVerifikator = (e: React.FormEvent) => {
        e.preventDefault();
        formVerifikator.post('/profile');
    };

    const handleSubmitSekolah = (e: React.FormEvent) => {
        e.preventDefault();
        formSekolah.post('/profile');
    };

    return (
        <AppLayout title="Profil Saya">
            <Head title="Profil — SI BANTEK 2026" />

            <div className="space-y-6 max-w-2xl">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">Pengaturan Profil & Keamanan</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Perbarui informasi profil akun dan kata sandi login Anda.
                    </p>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    {/* Admin Profile Form */}
                    {user.role === 'admin' && (
                        <form onSubmit={handleSubmitAdmin} className="space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Nama Administrator</label>
                                <input
                                    type="text"
                                    required
                                    value={formAdmin.data.name}
                                    onChange={(e) => formAdmin.setData('name', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formAdmin.errors.name && <p className="mt-1 text-xs text-red-600">{formAdmin.errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Username Login</label>
                                <input
                                    type="text"
                                    required
                                    value={formAdmin.data.username}
                                    onChange={(e) => formAdmin.setData('username', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formAdmin.errors.username && <p className="mt-1 text-xs text-red-600">{formAdmin.errors.username}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                                <input
                                    type="password"
                                    value={formAdmin.data.password}
                                    onChange={(e) => formAdmin.setData('password', e.target.value)}
                                    placeholder="Minimal 8 karakter"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formAdmin.errors.password && <p className="mt-1 text-xs text-red-600">{formAdmin.errors.password}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={formAdmin.processing}
                                className="rounded-lg bg-[#1e2d5a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                            >
                                {formAdmin.processing ? 'Menyimpan...' : 'Simpan Profil'}
                            </button>
                        </form>
                    )}

                    {/* Verifikator Profile Form */}
                    {user.role === 'verifikator' && (
                        <form onSubmit={handleSubmitVerifikator} className="space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap & Gelar</label>
                                <input
                                    type="text"
                                    required
                                    value={formVerifikator.data.name}
                                    onChange={(e) => formVerifikator.setData('name', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formVerifikator.errors.name && <p className="mt-1 text-xs text-red-600">{formVerifikator.errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">NIP (Nomor Induk Pegawai) untuk Login</label>
                                <input
                                    type="text"
                                    required
                                    value={formVerifikator.data.nip}
                                    onChange={(e) => formVerifikator.setData('nip', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formVerifikator.errors.nip && <p className="mt-1 text-xs text-red-600">{formVerifikator.errors.nip}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                                <input
                                    type="password"
                                    value={formVerifikator.data.password}
                                    onChange={(e) => formVerifikator.setData('password', e.target.value)}
                                    placeholder="Minimal 8 karakter"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formVerifikator.errors.password && <p className="mt-1 text-xs text-red-600">{formVerifikator.errors.password}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={formVerifikator.processing}
                                className="rounded-lg bg-[#1e2d5a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                            >
                                {formVerifikator.processing ? 'Menyimpan...' : 'Simpan Profil'}
                            </button>
                        </form>
                    )}

                    {/* Sekolah Profile Form */}
                    {user.role === 'sekolah' && (
                        <form onSubmit={handleSubmitSekolah} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Nama Satuan Pendidikan</label>
                                    <input
                                        type="text"
                                        required
                                        value={formSekolah.data.nama_sekolah}
                                        onChange={(e) => formSekolah.setData('nama_sekolah', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.nama_sekolah && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.nama_sekolah}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">NPSN (untuk Login)</label>
                                    <input
                                        type="text"
                                        required
                                        value={formSekolah.data.npsn}
                                        onChange={(e) => formSekolah.setData('npsn', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.npsn && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.npsn}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Provinsi</label>
                                    <input
                                        type="text"
                                        required
                                        value={formSekolah.data.provinsi}
                                        onChange={(e) => formSekolah.setData('provinsi', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.provinsi && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.provinsi}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Kabupaten / Kota</label>
                                    <input
                                        type="text"
                                        required
                                        value={formSekolah.data.kabupaten}
                                        onChange={(e) => formSekolah.setData('kabupaten', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.kabupaten && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.kabupaten}</p>}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Alamat Lengkap</label>
                                <textarea
                                    rows={2}
                                    required
                                    value={formSekolah.data.alamat}
                                    onChange={(e) => formSekolah.setData('alamat', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formSekolah.errors.alamat && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.alamat}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Nama Kepala Sekolah</label>
                                    <input
                                        type="text"
                                        required
                                        value={formSekolah.data.nama_kepsek}
                                        onChange={(e) => formSekolah.setData('nama_kepsek', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.nama_kepsek && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.nama_kepsek}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">NIP Kepala Sekolah</label>
                                    <input
                                        type="text"
                                        required
                                        value={formSekolah.data.nip_kepsek}
                                        onChange={(e) => formSekolah.setData('nip_kepsek', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.nip_kepsek && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.nip_kepsek}</p>}
                                </div>
                            </div>

                            {/* Optional Bendahara Section */}
                            <div className="border-t border-slate-100 pt-4 mt-2">
                                <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Informasi Bendahara (Opsional — untuk cetak LPJ)</p>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nama Bendahara (Opsional)</label>
                                        <input
                                            type="text"
                                            value={formSekolah.data.nama_bendahara}
                                            onChange={(e) => formSekolah.setData('nama_bendahara', e.target.value)}
                                            placeholder="Contoh: Siti Rahmah, S.Pd."
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">NIP Bendahara (Opsional)</label>
                                        <input
                                            type="text"
                                            value={formSekolah.data.nip_bendahara}
                                            onChange={(e) => formSekolah.setData('nip_bendahara', e.target.value)}
                                            placeholder="Contoh: 198204152009032005"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                                <input
                                    type="password"
                                    value={formSekolah.data.password}
                                    onChange={(e) => formSekolah.setData('password', e.target.value)}
                                    placeholder="Minimal 8 karakter"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formSekolah.errors.password && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.password}</p>}
                            </div>

                            <button
                                type="submit"
                                disabled={formSekolah.processing}
                                className="rounded-lg bg-[#1e2d5a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                            >
                                {formSekolah.processing ? 'Menyimpan...' : 'Simpan Profil'}
                            </button>
                        </form>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
