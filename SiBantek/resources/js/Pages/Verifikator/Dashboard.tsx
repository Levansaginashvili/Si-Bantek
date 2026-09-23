import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { ArrowRight, Clock } from 'lucide-react';

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
    const totalLengkap = sekolahs.filter((s) => s.status_dokumen === 'Lengkap').length;
    const totalMenunggu = sekolahs.reduce((sum, s) => sum + s.dokumen_menunggu, 0);

    return (
        <AppLayout title="Dashboard Verifikator">
            <Head title="Verifikasi Sekolah — SI BANTEK 2026" />

            <div className="space-y-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">Daftar Sekolah Penerima Bantuan</h2>
                    <p className="mt-1 text-sm text-slate-500">Periksa kelengkapan berkas administrasi dan perbarui status penyaluran dana.</p>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {[
                        { label: 'Total Sekolah', value: sekolahs.length },
                        { label: 'Dokumen Lengkap', value: totalLengkap },
                        { label: 'Dokumen Menunggu Periksa', value: totalMenunggu },
                    ].map((c) => (
                        <div key={c.label} className="rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                            <p className="text-xs font-medium uppercase tracking-wide text-slate-500">{c.label}</p>
                            <p className="mt-2 text-3xl font-bold text-[#1e2d5a]">{c.value}</p>
                        </div>
                    ))}
                </div>

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h3 className="text-sm font-semibold text-slate-800">Semua Satuan Pendidikan</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['No', 'NPSN & Nama Sekolah', 'Kabupaten / Provinsi', 'Status Dana', 'Progres Berkas (12)', 'Aksi'].map((h) => (
                                        <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white">
                                {sekolahs.map((s, idx) => (
                                    <tr key={s.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="px-5 py-3.5 text-slate-500">{idx + 1}</td>
                                        <td className="px-5 py-3.5">
                                            <p className="font-medium text-slate-800">{s.nama_sekolah}</p>
                                            <p className="text-xs font-mono text-slate-400">{s.npsn}</p>
                                        </td>
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
                                        <td className="px-5 py-3.5">
                                            <div className="flex items-center gap-2">
                                                <div className="h-1.5 w-24 overflow-hidden rounded-full bg-slate-200">
                                                    <div
                                                        className="h-full rounded-full bg-[#1e2d5a]"
                                                        style={{ width: `${(s.dokumen_disetujui / 12) * 100}%` }}
                                                    />
                                                </div>
                                                <span className="text-xs font-medium text-slate-600">{s.dokumen_disetujui}/12</span>
                                            </div>
                                            {s.dokumen_menunggu > 0 && (
                                                <div className="mt-0.5 flex items-center gap-1 text-[11px] text-amber-600">
                                                    <Clock size={11} />
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
                                                <ArrowRight size={13} />
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}