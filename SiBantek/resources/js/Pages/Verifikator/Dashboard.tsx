import React, { useState, useMemo } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { ArrowRight, Clock, Search, CheckCircle, Upload, FileText, Download, X } from 'lucide-react';

interface Props {
    sekolahs: Array<{
        id: number;
        npsn: string;
        nama_sekolah: string;
        provinsi: string;
        kabupaten: string;
        status_dana: string;
        status_dokumen: string;
        total_dokumen: number;
        dokumen_disetujui: number;
        dokumen_menunggu: number;
    }>;
}

interface DocDefinition {
    key: string;
    label: string;
    stageName: string;
}

const docDefinitions: DocDefinition[] = [
    { key: 'pks', label: 'Perjanjian Kerja Sama (PKS)', stageName: '1. Persiapan & RAB' },
    { key: 'pakta_integritas', label: 'Pakta Integritas', stageName: '1. Persiapan & RAB' },
    { key: 'sptjm', label: 'Surat Pernyataan (SPTJM)', stageName: '1. Persiapan & RAB' },
    { key: 'rab', label: 'Rencana Anggaran Biaya (RAB)', stageName: '1. Persiapan & RAB' },
    { key: 'laporan_awal', label: 'Laporan Awal & Saldo Bank', stageName: '2. Pencairan Dana' },
    { key: 'perbandingan_siplah', label: 'Perbandingan Produk SIPLah', stageName: '3. Pengadaan SIPLah' },
    { key: 'invoice_siplah', label: 'Faktur Pembelian SIPLah', stageName: '3. Pengadaan SIPLah' },
    { key: 'bast', label: 'Berita Acara Serah Terima (BAST)', stageName: '4. Penerimaan & Pelabelan' },
    { key: 'foto_fisik_laptop', label: 'Foto Perangkat Laptop (6 Sudut)', stageName: '4. Penerimaan & Pelabelan' },
    { key: 'buku_inventaris', label: 'Buku Inventaris & Label Aset', stageName: '4. Penerimaan & Pelabelan' },
    { key: 'dokumentasi_pemanfaatan', label: 'Foto Pemanfaatan Pembelajaran', stageName: '5. LPJ & Pemanfaatan' },
    { key: 'lpj', label: 'Laporan Akhir LPJ', stageName: '5. LPJ & Pemanfaatan' },
];

type MainTab = 'sekolah' | 'template';

export default function VerifikatorDashboard({ sekolahs }: Props) {
    const [mainTab, setMainTab] = useState<MainTab>('sekolah');
    const [searchQuery, setSearchQuery] = useState('');
    const [filterDana, setFilterDana] = useState('semua');
    const [filterDokumen, setFilterDokumen] = useState('semua');
    const [uploadDocKey, setUploadDocKey] = useState<string | null>(null);

    const formTemplate = useForm({
        jenis_dokumen: '',
        file: null as File | null,
    });

    const openUploadModal = (docKey: string) => {
        setUploadDocKey(docKey);
        formTemplate.setData({ jenis_dokumen: docKey, file: null });
    };

    const handleUploadTemplate = (e: React.FormEvent) => {
        e.preventDefault();
        if (!uploadDocKey || !formTemplate.data.file) return;
        formTemplate.post('/verifikator/template/upload', {
            onSuccess: () => {
                setUploadDocKey(null);
                formTemplate.reset();
            },
        });
    };

    const totalMenunggu = sekolahs.reduce((sum, s) => sum + s.dokumen_menunggu, 0);

    const filteredSekolahs = useMemo(() => {
        return sekolahs.filter((s) => {
            const matchesSearch =
                s.nama_sekolah.toLowerCase().includes(searchQuery.toLowerCase()) ||
                s.npsn.includes(searchQuery) ||
                s.kabupaten.toLowerCase().includes(searchQuery.toLowerCase()) ||
                s.provinsi.toLowerCase().includes(searchQuery.toLowerCase());

            const matchesDana =
                filterDana === 'semua' ||
                (filterDana === 'disalurkan' && s.status_dana === 'Dana Sudah Disalurkan / Ditransfer') ||
                (filterDana === 'belum' && s.status_dana === 'Belum Disalurkan');

            const matchesDokumen =
                filterDokumen === 'semua' ||
                (filterDokumen === 'lengkap' && s.status_dokumen === 'Lengkap') ||
                (filterDokumen === 'belum' && s.status_dokumen === 'Belum Lengkap');

            return matchesSearch && matchesDana && matchesDokumen;
        });
    }, [sekolahs, searchQuery, filterDana, filterDokumen]);

    return (
        <AppLayout title="Dashboard Verifikator">
            <Head title="Verifikasi Sekolah — Si Bantek" />

            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-bold text-slate-800">Dashboard Verifikator</h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Kelola verifikasi sekolah penerima bantuan dan pembaruan berkas dokumen.
                        </p>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div className="rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">Total Sekolah</p>
                        <p className="mt-2 text-3xl font-bold text-[#1e2d5a]">{sekolahs.length}</p>
                    </div>
                    <div className="rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">Nilai Bantuan Per Sekolah</p>
                        <p className="mt-2 text-2xl font-bold text-[#1e2d5a]">Rp 69.364.000,00</p>
                    </div>
                    <div className="rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">Menunggu Verifikasi</p>
                        <p className={`mt-2 text-3xl font-bold ${totalMenunggu > 0 ? 'text-amber-600' : 'text-slate-700'}`}>{totalMenunggu}</p>
                    </div>
                </div>

                {/* Main Tabs Container */}
                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="flex border-b border-slate-100">
                        <button
                            onClick={() => setMainTab('sekolah')}
                            className={`flex-1 py-3.5 text-sm font-medium transition-colors sm:flex-none sm:px-6 ${
                                mainTab === 'sekolah'
                                    ? 'border-b-2 border-[#1e2d5a] text-[#1e2d5a]'
                                    : 'text-slate-500 hover:text-slate-700'
                            }`}
                        >
                            Daftar Sekolah Penerima Bantuan
                        </button>
                        <button
                            onClick={() => setMainTab('template')}
                            className={`flex-1 py-3.5 text-sm font-medium transition-colors sm:flex-none sm:px-6 ${
                                mainTab === 'template'
                                    ? 'border-b-2 border-[#1e2d5a] text-[#1e2d5a]'
                                    : 'text-slate-500 hover:text-slate-700'
                            }`}
                        >
                            Dokumen
                        </button>
                    </div>

                    {/* Tab 1: Daftar Sekolah Table */}
                    {mainTab === 'sekolah' && (
                        <div>
                            {/* Search & Filter Bar */}
                            <div className="border-b border-slate-100 px-5 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50">
                                <h3 className="text-sm font-semibold text-slate-700">
                                    Daftar Sekolah ({filteredSekolahs.length} dari {sekolahs.length})
                                </h3>
                                <div className="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                                    <div className="relative w-full sm:w-56">
                                        <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                        <input
                                            type="text"
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                            placeholder="Cari sekolah, NPSN, daerah..."
                                            className="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                    </div>
                                    <select
                                        value={filterDana}
                                        onChange={(e) => setFilterDana(e.target.value)}
                                        className="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none"
                                    >
                                        <option value="semua">Semua Status Dana</option>
                                        <option value="disalurkan">Sudah Disalurkan</option>
                                        <option value="belum">Belum Disalurkan</option>
                                    </select>
                                    <select
                                        value={filterDokumen}
                                        onChange={(e) => setFilterDokumen(e.target.value)}
                                        className="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none"
                                    >
                                        <option value="semua">Semua Status Dokumen</option>
                                        <option value="lengkap">Lengkap (12/12)</option>
                                        <option value="belum">Belum Lengkap</option>
                                    </select>
                                </div>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            {['No', 'Nama Sekolah & NPSN', 'Kabupaten / Provinsi', 'Status Dana', 'Status Berkas', 'Aksi'].map((h) => (
                                                <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                                    {h}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {filteredSekolahs.length === 0 ? (
                                            <tr>
                                                <td colSpan={6} className="px-5 py-8 text-center text-sm text-slate-400">
                                                    Tidak ditemukan sekolah yang sesuai pencarian/filter.
                                                </td>
                                            </tr>
                                        ) : (
                                            filteredSekolahs.map((s, idx) => (
                                                <tr key={s.id} className="hover:bg-slate-50 transition-colors">
                                                    <td className="px-5 py-3.5 text-xs text-slate-400">{idx + 1}</td>
                                                    <td className="px-5 py-3.5">
                                                        <p className="font-semibold text-slate-800">{s.nama_sekolah}</p>
                                                        <p className="text-xs font-mono text-slate-400 mt-0.5">{s.npsn}</p>
                                                    </td>
                                                    <td className="px-5 py-3.5 text-xs text-slate-500">{s.kabupaten}, {s.provinsi}</td>
                                                    <td className="px-5 py-3.5">
                                                        <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ${
                                                            s.status_dana === 'Dana Sudah Disalurkan / Ditransfer'
                                                                ? 'bg-emerald-50 text-emerald-700'
                                                                : 'bg-amber-50 text-amber-700'
                                                        }`}>
                                                            {s.status_dana === 'Dana Sudah Disalurkan / Ditransfer'
                                                                ? <><CheckCircle size={11} /> Disalurkan</>
                                                                : <><Clock size={11} /> Belum</>
                                                            }
                                                        </span>
                                                    </td>
                                                    <td className="px-5 py-3.5">
                                                        <div className="flex items-center gap-2">
                                                            <div className="h-1.5 w-24 overflow-hidden rounded-full bg-slate-200">
                                                                <div
                                                                    className="h-full rounded-full bg-[#1e2d5a]"
                                                                    style={{ width: `${(s.dokumen_disetujui / 12) * 100}%` }}
                                                                />
                                                            </div>
                                                            <span className="text-xs font-semibold text-slate-600">{s.dokumen_disetujui}/12</span>
                                                        </div>
                                                        {s.dokumen_menunggu > 0 && (
                                                            <div className="mt-0.5 flex items-center gap-1 text-[11px] text-amber-600">
                                                                <Clock size={10} />
                                                                {s.dokumen_menunggu} menunggu periksa
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="px-5 py-3.5">
                                                        <Link
                                                            href={`/verifikator/sekolah/${s.id}`}
                                                            className="inline-flex items-center gap-1.5 rounded-lg bg-[#1e2d5a] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#162247] transition-colors"
                                                        >
                                                            Periksa
                                                            <ArrowRight size={12} />
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    {/* Tab 2: Dokumen Table (Global for All Schools) */}
                    {mainTab === 'template' && (
                        <div>
                            <div className="border-b border-slate-100 px-5 py-3 flex items-center justify-between bg-slate-50">
                                <div>
                                    <h3 className="text-sm font-semibold text-slate-800">
                                        Daftar 12 Berkas Dokumen
                                    </h3>
                                    <p className="text-xs text-slate-500 mt-0.5">
                                        Jika Verifikator mengunggah berkas PDF baru di sini, seluruh akun sekolah akan otomatis mengunduh berkas terbaru ini.
                                    </p>
                                </div>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-100 text-sm">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            {['No', 'Tahap Program', 'Nama Dokumen', 'Pratinjau Dokumen Saat Ini', 'Unggah / Perbarui Dokumen'].map((h) => (
                                                <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                                    {h}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {docDefinitions.map((item, idx) => (
                                            <tr key={item.key} className="hover:bg-slate-50 transition-colors">
                                                <td className="px-5 py-3.5 text-xs text-slate-400">{idx + 1}</td>
                                                <td className="px-5 py-3.5 text-xs">
                                                    <span className="inline-block rounded bg-slate-100 px-2 py-0.5 font-medium text-slate-600">
                                                        {item.stageName}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-3.5 font-semibold text-slate-800">{item.label}</td>
                                                <td className="px-5 py-3.5">
                                                    <a
                                                        href={`/dokumen/download/${item.key}`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-[#1e2d5a] hover:underline"
                                                    >
                                                        <FileText size={13} />
                                                        Lihat File PDF Saat Ini
                                                    </a>
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    <button
                                                        onClick={() => openUploadModal(item.key)}
                                                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors"
                                                    >
                                                        <Upload size={13} />
                                                        Unggah PDF Baru
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Template Upload Modal */}
            {uploadDocKey && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <div>
                                <h3 className="text-sm font-semibold text-slate-800">Unggah Dokumen Baru</h3>
                                <p className="text-xs text-slate-400 mt-0.5">
                                    {docDefinitions.find(d => d.key === uploadDocKey)?.label}
                                </p>
                            </div>
                            <button onClick={() => setUploadDocKey(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>
                        <form onSubmit={handleUploadTemplate} className="px-6 py-5 space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-600 mb-2">Pilih File PDF Baru</label>
                                <input
                                    type="file"
                                    required
                                    accept=".pdf"
                                    onChange={(e) => formTemplate.setData('file', e.target.files?.[0] ?? null)}
                                    className="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                                />
                                <p className="mt-2 text-[11px] text-slate-500 leading-relaxed bg-slate-50 p-2.5 rounded border border-slate-200">
                                    File PDF yang diunggah di sini secara otomatis menggantikan dokumen ini untuk <strong>seluruh akun sekolah</strong>.
                                </p>
                            </div>

                            <div className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setUploadDocKey(null)}
                                    className="rounded-lg px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={formTemplate.processing || !formTemplate.data.file}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-xs font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    {formTemplate.processing ? 'Menyimpan...' : 'Simpan & Perbarui Dokumen'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}