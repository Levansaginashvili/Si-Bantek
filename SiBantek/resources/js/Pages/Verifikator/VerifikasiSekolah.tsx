import React, { useState } from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { ArrowLeft, FileText, X } from 'lucide-react';

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
    'Draft': 'bg-slate-100 text-slate-500',
};

const docLabels: Record<string, string> = {
    pks: 'Perjanjian Kerja Sama (PKS)',
    pakta_integritas: 'Pakta Integritas',
    sptjm: 'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM)',
    laporan_awal: 'Laporan Awal & Saldo Rekening',
    rab: 'Rencana Anggaran Biaya (RAB)',
    perbandingan_siplah: 'Perbandingan SIPLah (Min. 2 Penyedia)',
    invoice_siplah: 'Invoice / Faktur Pembelian SIPLah',
    bast: 'Berita Acara Serah Terima (BAST)',
    foto_fisik_laptop: 'Foto Fisik Perangkat (6 Sudut)',
    buku_inventaris: 'Buku Inventaris & Stiker Label',
    dokumentasi_pemanfaatan: 'Dokumentasi Pemanfaatan Pembelajaran',
    lpj: 'Laporan Akhir & Pengantar LPJ',
};

export default function VerifikasiSekolah({ sekolah }: Props) {
    const [revisiTarget, setRevisiTarget] = useState<{ type: 'dokumen' | 'rab'; id?: number } | null>(null);
    const [catatanRevisi, setCatatanRevisi] = useState('');

    const formDana = useForm({ status_dana: sekolah.status_dana });
    const formDoc = useForm({ dokumen_id: 0, status: '', catatan_revisi: '' });
    const formRab = useForm({ status: '', catatan_revisi: '' });

    const handleDana = (val: string) => {
        formDana.setData('status_dana', val);
        formDana.post(`/verifikator/sekolah/${sekolah.id}/status-dana`);
    };

    const handleDocAction = (dokumenId: number, status: 'Disetujui' | 'Revisi') => {
        if (status === 'Revisi') {
            setRevisiTarget({ type: 'dokumen', id: dokumenId });
            return;
        }
        formDoc.transform(() => ({ dokumen_id: dokumenId, status, catatan_revisi: '' }));
        formDoc.post(`/verifikator/sekolah/${sekolah.id}/dokumen`);
    };

    const handleRabAction = (status: 'Disetujui' | 'Revisi') => {
        if (status === 'Revisi') {
            setRevisiTarget({ type: 'rab' });
            return;
        }
        formRab.transform(() => ({ status, catatan_revisi: '' }));
        formRab.post(`/verifikator/sekolah/${sekolah.id}/rab`);
    };

    const submitRevisi = () => {
        if (!revisiTarget) return;
        if (revisiTarget.type === 'dokumen' && revisiTarget.id) {
            formDoc.transform(() => ({ dokumen_id: revisiTarget.id!, status: 'Revisi', catatan_revisi: catatanRevisi }));
            formDoc.post(`/verifikator/sekolah/${sekolah.id}/dokumen`, {
                onSuccess: () => { setRevisiTarget(null); setCatatanRevisi(''); },
            });
        } else {
            formRab.transform(() => ({ status: 'Revisi', catatan_revisi: catatanRevisi }));
            formRab.post(`/verifikator/sekolah/${sekolah.id}/rab`, {
                onSuccess: () => { setRevisiTarget(null); setCatatanRevisi(''); },
            });
        }
    };

    return (
        <AppLayout title={sekolah.nama_sekolah}>
            <Head title={`Verifikasi — ${sekolah.nama_sekolah}`} />

            <div className="space-y-6">
                <Link
                    href="/verifikator"
                    className="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition-colors"
                >
                    <ArrowLeft size={15} />
                    Kembali
                </Link>

                {/* School Info Card */}
                <div className="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div className="flex items-center gap-2">
                                <h2 className="text-lg font-bold text-slate-800">{sekolah.nama_sekolah}</h2>
                                <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${sekolah.status_dokumen === 'Lengkap' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                                    {sekolah.status_dokumen}
                                </span>
                            </div>
                            <p className="mt-1 text-sm text-slate-500">NPSN: {sekolah.npsn} — {sekolah.kabupaten}, {sekolah.provinsi}</p>
                            {sekolah.nama_kepsek && (
                                <p className="mt-0.5 text-sm text-slate-500">Kepala Sekolah: {sekolah.nama_kepsek}</p>
                            )}
                        </div>

                        <div className="flex-shrink-0 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm w-full sm:w-64">
                            <p className="text-xs font-medium text-slate-500 uppercase tracking-wide">Status Penyaluran Dana</p>
                            <p className="mt-1 font-semibold text-slate-800">
                                {sekolah.status_dana === 'Dana Sudah Disalurkan / Ditransfer' ? 'Sudah Disalurkan' : 'Belum Disalurkan'}
                            </p>
                            <div className="mt-3">
                                {sekolah.status_dana === 'Belum Disalurkan' ? (
                                    <button
                                        onClick={() => handleDana('Dana Sudah Disalurkan / Ditransfer')}
                                        disabled={formDana.processing}
                                        className="w-full rounded-lg bg-[#1e2d5a] px-3 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors disabled:opacity-70"
                                    >
                                        Tandai: Dana Sudah Ditransfer
                                    </button>
                                ) : (
                                    <button
                                        onClick={() => handleDana('Belum Disalurkan')}
                                        disabled={formDana.processing}
                                        className="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                    >
                                        Ubah ke Belum Disalurkan
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* RAB Section */}
                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                        <h3 className="text-sm font-semibold text-slate-800">Rencana Anggaran Biaya (RAB) Laptop</h3>
                        {sekolah.rab && (
                            <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${statusClass[sekolah.rab.status] ?? 'bg-slate-100 text-slate-500'}`}>
                                {sekolah.rab.status}
                            </span>
                        )}
                    </div>

                    <div className="p-5">
                        {sekolah.rab ? (
                            <>
                                <div className="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                                    {[
                                        { label: 'Merek / Tipe', value: sekolah.rab.merek_tipe_laptop },
                                        { label: 'Jumlah Unit', value: `${sekolah.rab.jumlah_unit} unit` },
                                        { label: 'Harga Satuan', value: `Rp ${new Intl.NumberFormat('id-ID').format(sekolah.rab.harga_satuan)}` },
                                        { label: 'Total Anggaran', value: `Rp ${new Intl.NumberFormat('id-ID').format(sekolah.rab.total_harga)}` },
                                    ].map((f) => (
                                        <div key={f.label}>
                                            <p className="text-xs text-slate-400">{f.label}</p>
                                            <p className="mt-0.5 font-medium text-slate-800">{f.value}</p>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-4 flex gap-2">
                                    <button
                                        onClick={() => handleRabAction('Disetujui')}
                                        disabled={formRab.processing}
                                        className="rounded-lg bg-[#1e2d5a] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors"
                                    >
                                        Setujui RAB
                                    </button>
                                    <button
                                        onClick={() => handleRabAction('Revisi')}
                                        disabled={formRab.processing}
                                        className="rounded-lg border border-slate-300 px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                    >
                                        Minta Revisi
                                    </button>
                                </div>
                            </>
                        ) : (
                            <p className="text-sm text-slate-400">Belum ada data RAB dari sekolah.</p>
                        )}
                    </div>
                </div>

                {/* Documents Table */}
                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h3 className="text-sm font-semibold text-slate-800">12 Berkas Administrasi & Pengadaan</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['No', 'Nama Dokumen', 'Berkas', 'Status', 'Catatan', 'Aksi'].map((h) => (
                                        <th key={h} className="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 bg-white">
                                {Object.entries(docLabels).map(([key, label], idx) => {
                                    const doc = sekolah.dokumens.find((d) => d.jenis_dokumen === key);
                                    const status = doc?.status ?? 'Belum Diunggah';

                                    return (
                                        <tr key={key} className="hover:bg-slate-50 transition-colors">
                                            <td className="px-5 py-3.5 text-slate-400">{idx + 1}</td>
                                            <td className="px-5 py-3.5 font-medium text-slate-800 max-w-xs">{label}</td>
                                            <td className="px-5 py-3.5">
                                                {doc?.file_path ? (
                                                    <a
                                                        href={`/storage/${doc.file_path}`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1 text-xs font-medium text-[#1e2d5a] underline"
                                                    >
                                                        <FileText size={13} />
                                                        Lihat
                                                    </a>
                                                ) : (
                                                    <span className="text-xs text-slate-400">—</span>
                                                )}
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium ${statusClass[status] ?? 'bg-slate-100 text-slate-500'}`}>
                                                    {status}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-xs text-red-600 max-w-xs">
                                                {doc?.catatan_revisi ?? '—'}
                                            </td>
                                            <td className="px-5 py-3.5">
                                                {doc?.file_path ? (
                                                    <div className="flex gap-1.5">
                                                        <button
                                                            onClick={() => handleDocAction(doc.id, 'Disetujui')}
                                                            className="rounded bg-[#1e2d5a] px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-[#162247] transition-colors"
                                                        >
                                                            Setujui
                                                        </button>
                                                        <button
                                                            onClick={() => handleDocAction(doc.id, 'Revisi')}
                                                            className="rounded border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                                        >
                                                            Revisi
                                                        </button>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-slate-400">—</span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Revision Modal */}
            {revisiTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h3 className="text-base font-semibold text-slate-800">Catatan Revisi</h3>
                            <button onClick={() => setRevisiTarget(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>
                        <div className="px-6 py-5">
                            <textarea
                                rows={4}
                                value={catatanRevisi}
                                onChange={(e) => setCatatanRevisi(e.target.value)}
                                placeholder="Tuliskan alasan revisi atau kekurangan dokumen..."
                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                            />
                            <div className="mt-4 flex justify-end gap-2">
                                <button
                                    onClick={() => setRevisiTarget(null)}
                                    className="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    onClick={submitRevisi}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162247] transition-colors"
                                >
                                    Kirim Catatan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
