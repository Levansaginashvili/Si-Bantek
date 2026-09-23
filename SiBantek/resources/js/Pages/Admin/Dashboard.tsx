import React, { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Search } from 'lucide-react';

interface Props {
    stats: {
        total_sekolah: number;
        sekolah_lengkap: number;
        dana_disalurkan: number;
    };
    sekolah_lengkap_list: Array<{
        id: number;
        npsn: string;
        nama_sekolah: string;
        provinsi: string;
        kabupaten: string;
        status_dana: string;
        updated_at: string;
    }>;
}

export default function Dashboard({ stats, sekolah_lengkap_list }: Props) {
    const [searchQuery, setSearchQuery] = useState('');
    const [filterDana, setFilterDana] = useState('semua');

    const filteredList = useMemo(() => {
        return sekolah_lengkap_list.filter((s) => {
            const matchesSearch =
                s.nama_sekolah.toLowerCase().includes(searchQuery.toLowerCase()) ||
                s.npsn.includes(searchQuery) ||
                s.kabupaten.toLowerCase().includes(searchQuery.toLowerCase()) ||
                s.provinsi.toLowerCase().includes(searchQuery.toLowerCase());

            const matchesDana =
                filterDana === 'semua' ||
                (filterDana === 'disalurkan' && s.status_dana === 'Dana Sudah Disalurkan / Ditransfer') ||
                (filterDana === 'belum' && s.status_dana === 'Belum Disalurkan');

            return matchesSearch && matchesDana;
        });
    }, [sekolah_lengkap_list, searchQuery, filterDana]);

    return (
        <AppLayout title="Dashboard Admin">
            <Head title="Dashboard — SI BANTEK 2026" />

            <div className="space-y-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">Sekolah dengan Dokumen Lengkap</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Pantauan sekolah penerima bantuan yang telah menyelesaikan seluruh verifikasi dokumen. Akses ini bersifat hanya-baca (read-only).
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {[
                        { label: 'Total Kuota Sekolah', value: 173, note: 'Target SK Kemendikdasmen 2026' },
                        { label: 'Sekolah dengan Dokumen Lengkap', value: stats.sekolah_lengkap, note: 'Seluruh 12 berkas disetujui' },
                        { label: 'Dana Disalurkan', value: stats.dana_disalurkan, note: 'Status diperbarui verifikator' },
                    ].map((card) => (
                        <div
                            key={card.label}
                            className="rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm"
                        >
                            <p className="text-xs font-medium uppercase tracking-wide text-slate-500">{card.label}</p>
                            <p className="mt-2 text-3xl font-bold text-[#1e2d5a]">{card.value}</p>
                            <p className="mt-1 text-xs text-slate-400">{card.note}</p>
                        </div>
                    ))}
                </div>

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-100 px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <h3 className="text-sm font-semibold text-slate-800">
                            Sekolah dengan Dokumen Lengkap
                            <span className="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                {filteredList.length} dari {sekolah_lengkap_list.length}
                            </span>
                        </h3>

                        {/* Search & Filter Bar */}
                        <div className="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                            <div className="relative w-full sm:w-64">
                                <Search size={15} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input
                                    type="text"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    placeholder="Cari NPSN, nama, daerah..."
                                    className="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                />
                            </div>

                            <select
                                value={filterDana}
                                onChange={(e) => setFilterDana(e.target.value)}
                                className="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                            >
                                <option value="semua">Semua Status Dana</option>
                                <option value="disalurkan">Sudah Disalurkan</option>
                                <option value="belum">Belum Disalurkan</option>
                            </select>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['No', 'NPSN', 'Nama Sekolah', 'Kabupaten / Provinsi', 'Status Dana'].map((h) => (
                                        <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white">
                                {filteredList.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-5 py-10 text-center text-sm text-slate-400">
                                            {searchQuery || filterDana !== 'semua'
                                                ? 'Tidak ditemukan sekolah yang sesuai pencarian/filter.'
                                                : 'Belum ada sekolah yang menyelesaikan seluruh verifikasi dokumen.'}
                                        </td>
                                    </tr>
                                ) : (
                                    filteredList.map((s, idx) => (
                                        <tr key={s.id} className="hover:bg-slate-50 transition-colors">
                                            <td className="px-5 py-3.5 text-slate-500">{idx + 1}</td>
                                            <td className="px-5 py-3.5 font-mono text-slate-700">{s.npsn}</td>
                                            <td className="px-5 py-3.5 font-medium text-slate-800">{s.nama_sekolah}</td>
                                            <td className="px-5 py-3.5 text-slate-500">{s.kabupaten}, {s.provinsi}</td>
                                            <td className="px-5 py-3.5">
                                                <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                    s.status_dana === 'Dana Sudah Disalurkan / Ditransfer'
                                                        ? 'bg-emerald-50 text-emerald-700'
                                                        : 'bg-amber-50 text-amber-700'
                                                }`}>
                                                    {s.status_dana === 'Dana Sudah Disalurkan / Ditransfer' ? 'Sudah Disalurkan' : 'Belum Disalurkan'}
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
        </AppLayout>
    );
}