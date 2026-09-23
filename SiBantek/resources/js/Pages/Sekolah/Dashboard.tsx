import React, { useState, useMemo } from 'react';
import { useForm, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Download, Upload, Printer, X, CheckCircle, Clock, AlertCircle, Search } from 'lucide-react';

interface Props {
    sekolah: {
        id: number;
        npsn: string;
        nama_sekolah: string;
        provinsi: string;
        kabupaten: string;
        alamat: string;
        nama_kepsek: string;
        nip_kepsek: string;
        status_dana: string;
        status_dokumen: string;
        dokumens: Array<{
            id: number;
            jenis_dokumen: string;
            file_path: string | null;
            status: string;
            catatan_revisi: string | null;
        }>;
        rab: {
            id: number;
            merek_tipe_laptop: string;
            jumlah_unit: number;
            harga_satuan: number;
            total_harga: number;
            status: string;
            catatan_revisi: string | null;
        } | null;
    };
}

const statusClass: Record<string, string> = {
    'Disetujui': 'bg-emerald-50 text-emerald-700',
    'Revisi': 'bg-red-50 text-red-700',
    'Menunggu Verifikasi': 'bg-amber-50 text-amber-700',
    'Belum Diunggah': 'bg-slate-100 text-slate-500',
};

const statusIcon: Record<string, React.ReactNode> = {
    'Disetujui': <CheckCircle size={12} />,
    'Menunggu Verifikasi': <Clock size={12} />,
    'Revisi': <AlertCircle size={12} />,
};

const docItems = [
    { key: 'pks', label: 'Perjanjian Kerja Sama (PKS)', pdfType: 'pks' },
    { key: 'pakta_integritas', label: 'Pakta Integritas', pdfType: 'pakta_integritas' },
    { key: 'sptjm', label: 'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM)', pdfType: 'sptjm' },
    { key: 'laporan_awal', label: 'Laporan Awal & Saldo Rekening', pdfType: null },
    { key: 'rab', label: 'Rencana Anggaran Biaya (RAB)', pdfType: 'rab' },
    { key: 'perbandingan_siplah', label: 'Perbandingan SIPLah (Min. 2 Penyedia)', pdfType: null },
    { key: 'invoice_siplah', label: 'Invoice / Faktur Pembelian SIPLah', pdfType: null },
    { key: 'bast', label: 'Berita Acara Serah Terima (BAST)', pdfType: 'bast' },
    { key: 'foto_fisik_laptop', label: 'Foto Fisik Perangkat (6 Sudut)', pdfType: null },
    { key: 'buku_inventaris', label: 'Buku Inventaris & Stiker Label', pdfType: null },
    { key: 'dokumentasi_pemanfaatan', label: 'Dokumentasi Pemanfaatan Pembelajaran', pdfType: null },
    { key: 'lpj', label: 'Laporan Akhir & Pengantar LPJ', pdfType: null },
];

type TabKey = 'dokumen' | 'rab' | 'profil';

export default function SekolahDashboard({ sekolah }: Props) {
    const [tab, setTab] = useState<TabKey>('dokumen');
    const [uploadDocKey, setUploadDocKey] = useState<string | null>(null);
    const [searchDocQuery, setSearchDocQuery] = useState('');
    const [filterDocStatus, setFilterDocStatus] = useState('semua');

    const formProfil = useForm({
        alamat: sekolah.alamat ?? '',
        nama_kepsek: sekolah.nama_kepsek ?? '',
        nip_kepsek: sekolah.nip_kepsek ?? '',
    });

    const formRab = useForm({
        merek_tipe_laptop: sekolah.rab?.merek_tipe_laptop ?? '',
        jumlah_unit: sekolah.rab?.jumlah_unit ?? 8,
        harga_satuan: sekolah.rab?.harga_satuan ?? 8625000,
    });

    const formUpload = useForm({ jenis_dokumen: '', file: null as File | null });

    const filteredDocs = useMemo(() => {
        return docItems.filter((item) => {
            const doc = sekolah.dokumens.find((d) => d.jenis_dokumen === item.key);
            const status = doc?.status ?? 'Belum Diunggah';

            const matchesSearch = item.label.toLowerCase().includes(searchDocQuery.toLowerCase());
            const matchesStatus = filterDocStatus === 'semua' || status === filterDocStatus;

            return matchesSearch && matchesStatus;
        });
    }, [sekolah.dokumens, searchDocQuery, filterDocStatus]);

    const handleProfil = (e: React.FormEvent) => {
        e.preventDefault();
        formProfil.post('/sekolah/profil');
    };

    const handleRab = (e: React.FormEvent) => {
        e.preventDefault();
        formRab.post('/sekolah/rab');
    };

    const handleUpload = (e: React.FormEvent) => {
        e.preventDefault();
        if (!uploadDocKey || !formUpload.data.file) return;
        formUpload.setData('jenis_dokumen', uploadDocKey);
        formUpload.post('/sekolah/dokumen/upload', {
            onSuccess: () => { setUploadDocKey(null); formUpload.reset(); },
        });
    };

    const docApproved = sekolah.dokumens.filter((d) => d.status === 'Disetujui').length;
    const totalBiaya = formRab.data.jumlah_unit * formRab.data.harga_satuan;

    const tabs: { key: TabKey; label: string }[] = [
        { key: 'dokumen', label: 'Dokumen & Berkas' },
        { key: 'rab', label: 'RAB Laptop' },
        { key: 'profil', label: 'Profil Sekolah' },
    ];

    return (
        <AppLayout title="Dashboard Sekolah">
            <Head title={`Sekolah — ${sekolah.nama_sekolah}`} />

            <div className="space-y-5">
                {/* School Overview */}
                <div className="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="min-w-0">
                            <h2 className="text-lg font-bold text-slate-800">{sekolah.nama_sekolah}</h2>
                            <p className="mt-1 text-sm text-slate-500">
                                NPSN: {sekolah.npsn} — {sekolah.kabupaten}, {sekolah.provinsi}
                            </p>
                            {sekolah.nama_kepsek && (
                                <p className="mt-0.5 text-sm text-slate-500">Kepala Sekolah: {sekolah.nama_kepsek}</p>
                            )}
                        </div>
                        <div className="flex-shrink-0 space-y-2 text-sm sm:text-right">
                            <div>
                                <p className="text-xs text-slate-400 uppercase tracking-wide">Status Dana</p>
                                <span className={`inline-block mt-0.5 rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                    sekolah.status_dana === 'Dana Sudah Disalurkan / Ditransfer'
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-amber-50 text-amber-700'
                                }`}>
                                    {sekolah.status_dana === 'Dana Sudah Disalurkan / Ditransfer' ? 'Sudah Disalurkan' : 'Belum Disalurkan'}
                                </span>
                            </div>
                            <div>
                                <p className="text-xs text-slate-400 uppercase tracking-wide">Kelengkapan Berkas</p>
                                <p className="mt-0.5 font-semibold text-slate-700">{docApproved} / 12 dokumen disetujui</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Print Labels */}
                <div className="flex items-center justify-between rounded-xl border border-slate-200 bg-white shadow-sm px-5 py-4">
                    <div>
                        <p className="text-sm font-semibold text-slate-800">Stiker Label Aset Laptop (Lampiran IX)</p>
                        <p className="mt-0.5 text-xs text-slate-500">Cetak label ber-QR Code untuk setiap unit laptop bantuan.</p>
                    </div>
                    <a
                        href="/sekolah/inventaris/label-pdf"
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center gap-2 rounded-lg bg-[#1e2d5a] px-4 py-2.5 text-xs font-semibold text-white hover:bg-[#162247] transition-colors flex-shrink-0 ml-4"
                    >
                        <Printer size={15} />
                        Cetak Label
                    </a>
                </div>

                {/* Tabs */}
                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="flex border-b border-slate-100">
                        {tabs.map((t) => (
                            <button
                                key={t.key}
                                onClick={() => setTab(t.key)}
                                className={`flex-1 py-3.5 text-sm font-medium transition-colors sm:flex-none sm:px-6 ${
                                    tab === t.key
                                        ? 'border-b-2 border-[#1e2d5a] text-[#1e2d5a]'
                                        : 'text-slate-500 hover:text-slate-700'
                                }`}
                            >
                                {t.label}
                            </button>
                        ))}
                    </div>

                    {/* Tab: Dokumen */}
                    {tab === 'dokumen' && (
                        <div>
                            {/* Search & Filter bar */}
                            <div className="border-b border-slate-100 px-5 py-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50">
                                <span className="text-xs font-semibold text-slate-600">
                                    Daftar 12 Berkas ({filteredDocs.length} ditampilkan)
                                </span>

                                <div className="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                                    <div className="relative w-full sm:w-56">
                                        <Search size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                        <input
                                            type="text"
                                            value={searchDocQuery}
                                            onChange={(e) => setSearchDocQuery(e.target.value)}
                                            placeholder="Cari nama dokumen..."
                                            className="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>

                                    <select
                                        value={filterDocStatus}
                                        onChange={(e) => setFilterDocStatus(e.target.value)}
                                        className="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    >
                                        <option value="semua">Semua Status</option>
                                        <option value="Disetujui">Disetujui</option>
                                        <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                                        <option value="Revisi">Revisi</option>
                                        <option value="Belum Diunggah">Belum Diunggah</option>
                                    </select>
                                </div>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            {['No', 'Nama Dokumen', 'Status', 'Catatan Revisi', 'Unduh Draft', 'Unggah'].map((h) => (
                                                <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                                    {h}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {filteredDocs.length === 0 ? (
                                            <tr>
                                                <td colSpan={6} className="px-5 py-8 text-center text-sm text-slate-400">
                                                    Tidak ada dokumen yang sesuai pencarian/filter.
                                                </td>
                                            </tr>
                                        ) : (
                                            filteredDocs.map((item, idx) => {
                                                const doc = sekolah.dokumens.find((d) => d.jenis_dokumen === item.key);
                                                const status = doc?.status ?? 'Belum Diunggah';
                                                return (
                                                    <tr key={item.key} className="hover:bg-slate-50 transition-colors">
                                                        <td className="px-5 py-3.5 text-slate-400">{idx + 1}</td>
                                                        <td className="px-5 py-3.5 font-medium text-slate-800 max-w-xs">{item.label}</td>
                                                        <td className="px-5 py-3.5">
                                                            <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ${statusClass[status] ?? 'bg-slate-100 text-slate-500'}`}>
                                                                {statusIcon[status]}
                                                                {status}
                                                            </span>
                                                        </td>
                                                        <td className="px-5 py-3.5 text-xs text-red-600 max-w-xs">
                                                            {doc?.catatan_revisi ?? '—'}
                                                        </td>
                                                        <td className="px-5 py-3.5">
                                                            {item.pdfType ? (
                                                                <a
                                                                    href={`/sekolah/dokumen/download/${item.pdfType}`}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                    className="inline-flex items-center gap-1 text-xs font-medium text-[#1e2d5a] underline"
                                                                >
                                                                    <Download size={13} />
                                                                    Unduh Draft
                                                                </a>
                                                            ) : (
                                                                <span className="text-xs text-slate-400">—</span>
                                                            )}
                                                        </td>
                                                        <td className="px-5 py-3.5">
                                                            <button
                                                                onClick={() => setUploadDocKey(item.key)}
                                                                className="inline-flex items-center gap-1 rounded bg-[#1e2d5a] px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-[#162247] transition-colors"
                                                            >
                                                                <Upload size={12} />
                                                                Unggah
                                                            </button>
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    {/* Tab: RAB */}
                    {tab === 'rab' && (
                        <div className="p-6">
                            {sekolah.rab && (
                                <div className="mb-5 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
                                    <p className="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Status RAB Saat Ini</p>
                                    <div className="flex flex-wrap gap-6">
                                        <div>
                                            <p className="text-xs text-slate-400">Merek / Tipe</p>
                                            <p className="font-medium text-slate-800">{sekolah.rab.merek_tipe_laptop}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-slate-400">Total Anggaran</p>
                                            <p className="font-medium text-slate-800">Rp {new Intl.NumberFormat('id-ID').format(sekolah.rab.total_harga)}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-slate-400">Status</p>
                                            <span className={`inline-block mt-0.5 rounded-full px-2.5 py-0.5 text-xs font-medium ${statusClass[sekolah.rab.status] ?? 'bg-slate-100 text-slate-500'}`}>
                                                {sekolah.rab.status}
                                            </span>
                                        </div>
                                    </div>
                                    {sekolah.rab.catatan_revisi && (
                                        <p className="mt-3 text-xs text-red-600">Catatan: {sekolah.rab.catatan_revisi}</p>
                                    )}
                                </div>
                            )}

                            <form onSubmit={handleRab} className="space-y-4 max-w-lg">
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Merek & Tipe Laptop</label>
                                    <input
                                        type="text"
                                        required
                                        value={formRab.data.merek_tipe_laptop}
                                        onChange={(e) => formRab.setData('merek_tipe_laptop', e.target.value)}
                                        placeholder="Contoh: ASUS Chromebook C423"
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formRab.errors.merek_tipe_laptop && <p className="mt-1 text-xs text-red-600">{formRab.errors.merek_tipe_laptop}</p>}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Jumlah Unit (Min. 8)</label>
                                        <input
                                            type="number"
                                            min={8}
                                            required
                                            value={formRab.data.jumlah_unit}
                                            onChange={(e) => formRab.setData('jumlah_unit', parseInt(e.target.value) || 8)}
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-slate-600 mb-1">Harga Satuan (Maks. 8.625.000)</label>
                                        <input
                                            type="number"
                                            max={8625000}
                                            required
                                            value={formRab.data.harga_satuan}
                                            onChange={(e) => formRab.setData('harga_satuan', parseFloat(e.target.value) || 0)}
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                </div>

                                <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <p className="text-xs text-slate-500">Total Biaya RAB</p>
                                    <p className={`mt-1 text-2xl font-bold ${totalBiaya > 69364000 ? 'text-red-600' : 'text-[#1e2d5a]'}`}>
                                        Rp {new Intl.NumberFormat('id-ID').format(totalBiaya)}
                                    </p>
                                    {totalBiaya > 69364000 && (
                                        <p className="mt-1 text-xs text-red-600">Melebihi nilai bantuan Rp 69.364.000</p>
                                    )}
                                </div>

                                <button
                                    type="submit"
                                    disabled={formRab.processing || totalBiaya > 69364000}
                                    className="rounded-lg bg-[#1e2d5a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    {formRab.processing ? 'Menyimpan...' : 'Simpan & Ajukan RAB'}
                                </button>
                            </form>
                        </div>
                    )}

                    {/* Tab: Profil */}
                    {tab === 'profil' && (
                        <div className="p-6">
                            <form onSubmit={handleProfil} className="space-y-4 max-w-lg">
                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Alamat Lengkap Sekolah</label>
                                    <textarea
                                        rows={3}
                                        required
                                        value={formProfil.data.alamat}
                                        onChange={(e) => formProfil.setData('alamat', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formProfil.errors.alamat && <p className="mt-1 text-xs text-red-600">{formProfil.errors.alamat}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">Nama Kepala Sekolah</label>
                                    <input
                                        type="text"
                                        required
                                        value={formProfil.data.nama_kepsek}
                                        onChange={(e) => formProfil.setData('nama_kepsek', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formProfil.errors.nama_kepsek && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nama_kepsek}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-slate-600 mb-1">NIP Kepala Sekolah</label>
                                    <input
                                        type="text"
                                        required
                                        value={formProfil.data.nip_kepsek}
                                        onChange={(e) => formProfil.setData('nip_kepsek', e.target.value)}
                                        className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    />
                                    {formProfil.errors.nip_kepsek && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nip_kepsek}</p>}
                                </div>

                                <button
                                    type="submit"
                                    disabled={formProfil.processing}
                                    className="rounded-lg bg-[#1e2d5a] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    {formProfil.processing ? 'Menyimpan...' : 'Simpan Profil'}
                                </button>
                            </form>
                        </div>
                    )}
                </div>
            </div>

            {/* Upload Modal */}
            {uploadDocKey && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h3 className="text-base font-semibold text-slate-800">Unggah Berkas</h3>
                            <button onClick={() => setUploadDocKey(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>
                        <form onSubmit={handleUpload} className="px-6 py-5 space-y-4">
                            <div>
                                <p className="text-xs text-slate-500 mb-3">Format yang diizinkan: PDF, JPG, PNG (Maks. 10 MB)</p>
                                <input
                                    type="file"
                                    required
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    onChange={(e) => formUpload.setData('file', e.target.files?.[0] ?? null)}
                                    className="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                                />
                            </div>
                            <div className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setUploadDocKey(null)}
                                    className="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={formUpload.processing}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    {formUpload.processing ? 'Mengunggah...' : 'Unggah'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}