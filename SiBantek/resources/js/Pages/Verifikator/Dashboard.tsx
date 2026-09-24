import React, { useState, useMemo } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { ArrowRight, Clock, Search, CheckCircle } from 'lucide-react';

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

export default function VerifikatorDashboard({ sekolahs }: Props) {
    const [searchQuery, setSearchQuery] = useState('');
    const [filterDana, setFilterDana] = useState('semua');
    const [filterDokumen, setFilterDokumen] = useState('semua');

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
                <div>
                    <h2 className="text-xl font-bold text-slate-800">Daftar Sekolah Penerima Bantuan</h2>
                    <p className="mt-1 text-sm text-slate-500">Periksa kelengkapan berkas administrasi dan perbarui status penyaluran dana.</p>
                </div>

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

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
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
            </div>
        </AppLayout>
    );
}