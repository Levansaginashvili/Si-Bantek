import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

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
    return (
        <AppLayout title="Dashboard Admin">
            <Head title="Dashboard — SI BANTEK 2026" />

            <div className="space-y-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">Monitoring Kelengkapan Dokumen</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Pantauan sekolah penerima bantuan yang telah menyelesaikan seluruh verifikasi. Akses ini bersifat hanya-baca.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {[
                        { label: 'Total Kuota Sekolah', value: 173, note: 'Target SK Kemendikdasmen 2026' },
                        { label: 'Dokumen Lengkap', value: stats.sekolah_lengkap, note: 'Seluruh 12 berkas disetujui' },
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
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h3 className="text-sm font-semibold text-slate-800">
                            Sekolah dengan Dokumen Lengkap
                            <span className="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                {sekolah_lengkap_list.length}
                            </span>
                        </h3>
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
                                {sekolah_lengkap_list.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-5 py-10 text-center text-sm text-slate-400">
                                            Belum ada sekolah yang menyelesaikan seluruh verifikasi dokumen.
                                        </td>
                                    </tr>
                                ) : (
                                    sekolah_lengkap_list.map((s, idx) => (
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