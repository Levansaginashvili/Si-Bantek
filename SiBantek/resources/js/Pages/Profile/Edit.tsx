import React from 'react';
import { useForm, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { DAFTAR_PROVINSI, getKabupatenByProvinsi } from '../../data/wilayahIndonesia';

interface Props {
    user: {
        id: number;
        name: string;
        username?: string;
        nip?: string;
        jabatan?: string;
        alamat?: string;
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
        rt?: string;
        rw?: string;
        nomor_bangunan?: string;
        desa_kelurahan?: string;
        kecamatan?: string;
        kode_pos?: string;
        nama_kepsek?: string;
        nip_kepsek?: string;
        nama_bendahara?: string;
        nip_bendahara?: string;
        no_telepon?: string;
        email_sekolah?: string;
        nama_ketua_komite?: string;
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
        jabatan: user.jabatan || '',
        alamat: user.alamat || '',
        password: '',
    });

    const formSekolah = useForm({
        nama_sekolah: sekolah?.nama_sekolah || user.name || '',
        npsn: sekolah?.npsn || user.npsn || '',
        provinsi: sekolah?.provinsi || '',
        kabupaten: sekolah?.kabupaten || '',
        alamat: sekolah?.alamat || '',
        rt: sekolah?.rt || '',
        rw: sekolah?.rw || '',
        nomor_bangunan: sekolah?.nomor_bangunan || '',
        desa_kelurahan: sekolah?.desa_kelurahan || '',
        kecamatan: sekolah?.kecamatan || '',
        kode_pos: sekolah?.kode_pos || '',
        nama_kepsek: sekolah?.nama_kepsek || '',
        nip_kepsek: sekolah?.nip_kepsek || '',
        nama_bendahara: sekolah?.nama_bendahara || '',
        nip_bendahara: sekolah?.nip_bendahara || '',
        no_telepon: sekolah?.no_telepon || '',
        email_sekolah: sekolah?.email_sekolah || '',
        nama_ketua_komite: sekolah?.nama_ketua_komite || '',
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
        <AppLayout title="Profile">
            <Head title="Profile — Si Bantek" />

            <div className="space-y-6 max-w-2xl">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">Pengaturan Profile & Keamanan</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Perbarui informasi profile akun dan kata sandi login Anda.
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
                                {formAdmin.processing ? 'Menyimpan...' : 'Simpan Profile'}
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
                                <label className="block text-xs font-medium text-slate-600 mb-1">Jabatan / Posisi Instansi</label>
                                <input
                                    type="text"
                                    value={formVerifikator.data.jabatan}
                                    onChange={(e) => formVerifikator.setData('jabatan', e.target.value)}
                                    placeholder="Contoh: Pejabat Pembuat Komitmen (PPK) Direktorat SMP"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formVerifikator.errors.jabatan && <p className="mt-1 text-xs text-red-600">{formVerifikator.errors.jabatan}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Alamat Kantor / Instansi Verifikator (PPK)</label>
                                <textarea
                                    rows={2}
                                    value={formVerifikator.data.alamat}
                                    onChange={(e) => formVerifikator.setData('alamat', e.target.value)}
                                    placeholder="Contoh: Kompleks Kemendikbudristek Gedung E Lt. 17, Jl. Jenderal Sudirman, Senayan, Jakarta Pusat"
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                <p className="mt-1 text-[11px] text-slate-400">
                                    Alamat ini otomatis tercantum sebagai alamat PIHAK KEDUA pada Surat Perjanjian Kerjasama (PKS) dan dokumen resmi lainnya.
                                </p>
                                {formVerifikator.errors.alamat && <p className="mt-1 text-xs text-red-600">{formVerifikator.errors.alamat}</p>}
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
                                {formVerifikator.processing ? 'Menyimpan...' : 'Simpan Profile'}
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
                                    <select
                                        required
                                        value={formSekolah.data.provinsi}
                                        onChange={(e) => {
                                            const newProv = e.target.value;
                                            formSekolah.setData((prev) => ({
                                                ...prev,
                                                provinsi: newProv,
                                                kabupaten: '',
                                            }));
                                        }}
                                        className="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    >
                                        <option value="">-- Pilih Provinsi --</option>
                                        {DAFTAR_PROVINSI.map((prov) => (
                                            <option key={prov} value={prov}>
                                                {prov}
                                            </option>
                                        ))}
                                    </select>
                                    {formSekolah.errors.provinsi && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.provinsi}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Kabupaten / Kota</label>
                                    <select
                                        required
                                        disabled={!formSekolah.data.provinsi}
                                        value={formSekolah.data.kabupaten}
                                        onChange={(e) => formSekolah.setData('kabupaten', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a] disabled:bg-slate-100 disabled:text-slate-400"
                                    >
                                        <option value="">
                                            {formSekolah.data.provinsi ? '-- Pilih Kabupaten / Kota --' : '-- Pilih Provinsi Dahulu --'}
                                        </option>
                                        {getKabupatenByProvinsi(formSekolah.data.provinsi).map((kab) => (
                                            <option key={kab} value={kab}>
                                                {kab}
                                            </option>
                                        ))}
                                    </select>
                                    {formSekolah.errors.kabupaten && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.kabupaten}</p>}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-1">Alamat Jalan / Dusun</label>
                                <textarea
                                    rows={2}
                                    required
                                    placeholder="Contoh: Jl. Meulaboh - Tutut Km. 35"
                                    value={formSekolah.data.alamat}
                                    onChange={(e) => formSekolah.setData('alamat', e.target.value)}
                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                                {formSekolah.errors.alamat && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.alamat}</p>}
                            </div>

                            {/* Detail RT / RW / No Bangunan */}
                            <div className="grid grid-cols-3 gap-3">
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">RT (Rukun Tetangga)</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: 002"
                                        value={formSekolah.data.rt}
                                        onChange={(e) => formSekolah.setData('rt', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.rt && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.rt}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">RW (Rukun Warga)</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: 005"
                                        value={formSekolah.data.rw}
                                        onChange={(e) => formSekolah.setData('rw', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.rw && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.rw}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">No. Bangunan</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: 45 / 12A"
                                        value={formSekolah.data.nomor_bangunan}
                                        onChange={(e) => formSekolah.setData('nomor_bangunan', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.nomor_bangunan && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.nomor_bangunan}</p>}
                                </div>
                            </div>

                            {/* Detail Desa / Kecamatan / Kode Pos */}
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Desa / Kelurahan</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: Pasir Putih"
                                        value={formSekolah.data.desa_kelurahan}
                                        onChange={(e) => formSekolah.setData('desa_kelurahan', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.desa_kelurahan && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.desa_kelurahan}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Kecamatan</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: Woyla Timur"
                                        value={formSekolah.data.kecamatan}
                                        onChange={(e) => formSekolah.setData('kecamatan', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.kecamatan && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.kecamatan}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Kode Pos</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: 23685"
                                        value={formSekolah.data.kode_pos}
                                        onChange={(e) => formSekolah.setData('kode_pos', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formSekolah.errors.kode_pos && <p className="mt-1 text-xs text-red-600">{formSekolah.errors.kode_pos}</p>}
                                </div>
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

                            {/* Bendahara Section */}
                            <div className="border-t border-slate-100 pt-4 mt-2">
                                <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Informasi Bendahara</p>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nama Bendahara</label>
                                        <input
                                            type="text"
                                            value={formSekolah.data.nama_bendahara}
                                            onChange={(e) => formSekolah.setData('nama_bendahara', e.target.value)}
                                            placeholder="Contoh: Siti Rahmah, S.Pd."
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">NIP Bendahara</label>
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

                            <div className="border-t border-slate-200 pt-4">
                                <h4 className="text-xs font-bold uppercase tracking-wide text-slate-500 mb-3">Kontak & Komite Sekolah</h4>
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nomor Telepon Sekolah</label>
                                        <input
                                            type="text"
                                            value={formSekolah.data.no_telepon}
                                            onChange={(e) => formSekolah.setData('no_telepon', e.target.value)}
                                            placeholder="Contoh: 0651-12345"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Email Sekolah</label>
                                        <input
                                            type="email"
                                            value={formSekolah.data.email_sekolah}
                                            onChange={(e) => formSekolah.setData('email_sekolah', e.target.value)}
                                            placeholder="Contoh: smpn3@sch.id"
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Nama Ketua Komite Sekolah</label>
                                        <input
                                            type="text"
                                            value={formSekolah.data.nama_ketua_komite}
                                            onChange={(e) => formSekolah.setData('nama_ketua_komite', e.target.value)}
                                            placeholder="Contoh: H. Ahmad Sulaiman, S.Pd."
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
                                {formSekolah.processing ? 'Menyimpan...' : 'Simpan Profile'}
                            </button>
                        </form>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
