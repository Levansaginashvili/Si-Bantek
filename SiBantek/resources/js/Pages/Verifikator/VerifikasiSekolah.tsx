import React, { useState } from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { ArrowLeft, FileText, X, CheckCircle, AlertTriangle, Clock, AlertCircle } from 'lucide-react';

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
            spesifikasi_ringkas: string;
            jumlah_unit: number;
            harga_satuan: number;
            total_harga: number;
            status: string;
            catatan_revisi: string | null;
        } | null;
    };
}

const statusStyle: Record<string, string> = {
    'Disetujui': 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold',
    'Revisi': 'bg-red-50 text-red-700 border border-red-200 font-semibold',
    'Menunggu Verifikasi': 'bg-amber-50 text-amber-700 border border-amber-200 font-semibold',
    'Belum Diunggah': 'bg-slate-100 text-slate-500 border border-slate-200',
    'Draft': 'bg-slate-100 text-slate-500 border border-slate-200',
};

const statusIcon: Record<string, React.ReactNode> = {
    'Disetujui': <CheckCircle size={13} />,
    'Menunggu Verifikasi': <Clock size={13} />,
    'Revisi': <AlertCircle size={13} />,
};

const docLabels: Record<string, string> = {
    pks: 'Perjanjian Kerja Sama (PKS)',
    pakta_integritas: 'Pakta Integritas',
    sptjm: 'Surat Pernyataan (SPTJM)',
    laporan_awal: 'Laporan Awal & Saldo',
    rab: 'Rencana Anggaran Biaya (RAB)',
    perbandingan_siplah: 'Perbandingan SIPLah',
    invoice_siplah: 'Faktur Pembelian SIPLah',
    bast: 'Berita Acara Serah Terima (BAST)',
    foto_fisik_laptop: 'Foto Perangkat Laptop',
    buku_inventaris: 'Buku Inventaris & Label',
    dokumentasi_pemanfaatan: 'Dokumentasi Pemanfaatan',
    lpj: 'Laporan LPJ',
};

const PAGU = 69364000;
const fmt = (n: number) => new Intl.NumberFormat('id-ID').format(n);

export default function VerifikasiSekolah({ sekolah }: Props) {
    const [revisiTarget, setRevisiTarget] = useState<{ type: 'dokumen' | 'rab'; id?: number; name?: string } | null>(null);
    const [catatanRevisi, setCatatanRevisi] = useState('');

    // Konfirmasi persetujuan dokumen/RAB
    const [confirmApproveTarget, setConfirmApproveTarget] = useState<{ type: 'dokumen' | 'rab'; id?: number; title: string } | null>(null);

    // Konfirmasi perubahan status dana
    const [confirmDana, setConfirmDana] = useState<string | null>(null);

    const formDana = useForm({ status_dana: sekolah.status_dana });
    const formDoc = useForm({ dokumen_id: 0, status: '', catatan_revisi: '' });
    const formRab = useForm({ status: '', catatan_revisi: '' });

    // Dana: tampilkan modal konfirmasi dulu
    const handleDanaClick = (val: string) => {
        setConfirmDana(val);
    };

    // Dana: eksekusi setelah konfirmasi
    const processDana = () => {
        if (!confirmDana) return;
        formDana.setData('status_dana', confirmDana);
        formDana.post(`/verifikator/sekolah/${sekolah.id}/status-dana`, {
            onSuccess: () => setConfirmDana(null),
        });
    };

    const processApproval = () => {
        if (!confirmApproveTarget) return;
        if (confirmApproveTarget.type === 'dokumen' && confirmApproveTarget.id) {
            formDoc.transform(() => ({ dokumen_id: confirmApproveTarget.id!, status: 'Disetujui', catatan_revisi: '' }));
            formDoc.post(`/verifikator/sekolah/${sekolah.id}/dokumen`, {
                onSuccess: () => setConfirmApproveTarget(null),
            });
        } else if (confirmApproveTarget.type === 'rab') {
            formRab.transform(() => ({ status: 'Disetujui', catatan_revisi: '' }));
            formRab.post(`/verifikator/sekolah/${sekolah.id}/rab`, {
                onSuccess: () => setConfirmApproveTarget(null),
            });
        }
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

    const totalRab = sekolah.rab?.total_harga ?? 0;
    const sisaDana = PAGU - totalRab;

    const sudahDisalurkan = sekolah.status_dana === 'Dana Sudah Disalurkan / Ditransfer';

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
                            <div className="flex items-center gap-3 flex-wrap">
                                <h2 className="text-xl font-bold text-slate-800">{sekolah.nama_sekolah}</h2>
                                <span className={`px-2.5 py-1 text-xs rounded-md ${sekolah.status_dokumen === 'Lengkap' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-600 border border-slate-200 font-medium'}`}>
                                    {sekolah.status_dokumen === 'Lengkap' ? '✓ Berkas Lengkap (12/12)' : 'Berkas Belum Lengkap'}
                                </span>
                            </div>
                            <p className="mt-1 text-sm text-slate-500">NPSN: {sekolah.npsn} — {sekolah.kabupaten}, {sekolah.provinsi}</p>
                            {sekolah.nama_kepsek && (
                                <p className="mt-0.5 text-sm text-slate-500">Kepala Sekolah: {sekolah.nama_kepsek}</p>
                            )}
                        </div>

                        {/* Dana Status Box */}
                        <div className="flex-shrink-0 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm w-full sm:w-64">
                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Status Penyaluran Dana</p>
                            <span className={`inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md ${
                                sudahDisalurkan
                                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                    : 'bg-amber-50 text-amber-700 border border-amber-200'
                            }`}>
                                {sudahDisalurkan ? <CheckCircle size={13} /> : <Clock size={13} />}
                                {sudahDisalurkan ? 'Dana Sudah Disalurkan' : 'Belum Disalurkan'}
                            </span>
                            <div className="mt-3">
                                {!sudahDisalurkan ? (
                                    <button
                                        onClick={() => handleDanaClick('Dana Sudah Disalurkan / Ditransfer')}
                                        disabled={formDana.processing}
                                        className="w-full rounded-lg bg-[#1e2d5a] px-3 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors disabled:opacity-70"
                                    >
                                        Tandai: Dana Sudah Ditransfer
                                    </button>
                                ) : (
                                    <button
                                        onClick={() => handleDanaClick('Belum Disalurkan')}
                                        disabled={formDana.processing}
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
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
                    <div className="border-b border-slate-100 bg-slate-50 px-5 py-3.5 flex items-center justify-between">
                        <h3 className="text-sm font-bold text-slate-800">Rencana Anggaran Biaya (RAB) Laptop</h3>
                        {sekolah.rab && (
                            <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-md ${statusStyle[sekolah.rab.status] ?? 'bg-slate-100 text-slate-600'}`}>
                                {statusIcon[sekolah.rab.status]}
                                {sekolah.rab.status}
                            </span>
                        )}
                    </div>
                    <div className="p-5">
                        {sekolah.rab ? (
                            <>
                                <div className="grid grid-cols-2 gap-4 text-sm sm:grid-cols-5">
                                    <div>
                                        <p className="text-xs text-slate-400 uppercase font-medium">Merek & Tipe</p>
                                        <p className="mt-0.5 font-semibold text-slate-800">{sekolah.rab.merek_tipe_laptop}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-slate-400 uppercase font-medium">Jumlah Unit</p>
                                        <p className="mt-0.5 font-semibold text-slate-800">{sekolah.rab.jumlah_unit} unit</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-slate-400 uppercase font-medium">Harga Satuan</p>
                                        <p className="mt-0.5 font-semibold text-slate-800">Rp {fmt(sekolah.rab.harga_satuan)}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-slate-400 uppercase font-medium">Total Anggaran</p>
                                        <p className="mt-0.5 font-bold text-[#1e2d5a]">Rp {fmt(sekolah.rab.total_harga)}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-slate-400 uppercase font-medium">Sisa Dana</p>
                                        <p className={`mt-0.5 font-bold ${sisaDana >= 0 ? 'text-emerald-700' : 'text-red-600'}`}>
                                            Rp {fmt(sisaDana)}
                                        </p>
                                    </div>
                                </div>

                                <div className="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                                    <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Spesifikasi Laptop</p>
                                    <p className="mt-1 text-sm text-slate-700">{sekolah.rab.spesifikasi_ringkas}</p>
                                </div>

                                {sekolah.rab.catatan_revisi && (
                                    <div className="mt-3 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-xs text-red-700">
                                        <span className="font-bold">Catatan Revisi: </span>{sekolah.rab.catatan_revisi}
                                    </div>
                                )}

                                <div className="mt-4 pt-1">
                                    {sekolah.rab.status === 'Disetujui' ? (
                                        <span className="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 text-xs font-semibold">
                                            <CheckCircle size={14} />
                                            RAB Telah Disetujui
                                        </span>
                                    ) : (
                                        <div className="flex gap-2">
                                            <button
                                                onClick={() => setConfirmApproveTarget({ type: 'rab', title: 'Rencana Anggaran Biaya (RAB)' })}
                                                disabled={formRab.processing}
                                                className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors"
                                            >
                                                Setujui RAB
                                            </button>
                                            <button
                                                onClick={() => { setRevisiTarget({ type: 'rab', name: 'RAB Laptop' }); setCatatanRevisi(''); }}
                                                disabled={formRab.processing}
                                                className="rounded-lg border border-red-200 bg-white px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-50 transition-colors"
                                            >
                                                Minta Revisi
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </>
                        ) : (
                            <p className="text-sm text-slate-400 py-2">Belum ada data RAB dari sekolah.</p>
                        )}
                    </div>
                </div>

                {/* Documents Table */}
                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-100 bg-slate-50 px-5 py-3.5">
                        <h3 className="text-sm font-bold text-slate-800">Daftar Berkas Dokumen (12)</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-100 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['No', 'Nama Dokumen', 'Berkas Upload', 'Status', 'Catatan Revisi', 'Aksi'].map((h) => (
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
                                    const isDisetujui = status === 'Disetujui';

                                    return (
                                        <tr key={key} className="hover:bg-slate-50 transition-colors">
                                            <td className="px-5 py-3.5 text-xs text-slate-400">{idx + 1}</td>
                                            <td className="px-5 py-3.5 font-medium text-slate-800 max-w-xs">{label}</td>
                                            <td className="px-5 py-3.5">
                                                {doc?.id && doc.file_path ? (
                                                    <a
                                                        href={`/dokumen/file/${doc.id}`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                                                    >
                                                        <FileText size={13} />
                                                        Lihat Berkas
                                                    </a>
                                                ) : (
                                                    <span className="text-xs text-slate-300 italic">Belum diunggah</span>
                                                )}
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <span className={`inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs ${statusStyle[status] ?? 'bg-slate-100 text-slate-500'}`}>
                                                    {statusIcon[status]}
                                                    {status}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-xs text-red-600 max-w-xs font-medium">
                                                {doc?.catatan_revisi ?? '—'}
                                            </td>
                                            <td className="px-5 py-3.5">
                                                {doc?.file_path ? (
                                                    isDisetujui ? (
                                                        <span className="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1.5 rounded-md">
                                                            <CheckCircle size={13} />
                                                            Disetujui
                                                        </span>
                                                    ) : (
                                                        <div className="flex gap-1.5">
                                                            <button
                                                                onClick={() => setConfirmApproveTarget({ type: 'dokumen', id: doc.id, title: label })}
                                                                className="rounded-md bg-[#1e2d5a] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#162247] transition-colors"
                                                            >
                                                                Setujui
                                                            </button>
                                                            <button
                                                                onClick={() => { setRevisiTarget({ type: 'dokumen', id: doc.id, name: label }); setCatatanRevisi(''); }}
                                                                className="rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 transition-colors"
                                                            >
                                                                Revisi
                                                            </button>
                                                        </div>
                                                    )
                                                ) : (
                                                    <span className="text-slate-300 text-xs">—</span>
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

            {/* Modal: Konfirmasi Status Dana */}
            {confirmDana !== null && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-sm rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className={`flex items-center justify-between border-b px-6 py-4 ${confirmDana === 'Dana Sudah Disalurkan / Ditransfer' ? 'bg-emerald-50 border-emerald-100' : 'bg-amber-50 border-amber-100'}`}>
                            <div className={`flex items-center gap-2 ${confirmDana === 'Dana Sudah Disalurkan / Ditransfer' ? 'text-emerald-800' : 'text-amber-800'}`}>
                                {confirmDana === 'Dana Sudah Disalurkan / Ditransfer' ? <CheckCircle size={18} /> : <AlertTriangle size={18} />}
                                <h3 className="text-sm font-bold">Konfirmasi Status Dana</h3>
                            </div>
                            <button onClick={() => setConfirmDana(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>
                        <div className="px-6 py-5 space-y-4">
                            <p className="text-sm text-slate-700">
                                {confirmDana === 'Dana Sudah Disalurkan / Ditransfer'
                                    ? <>Tandai bahwa dana bantuan untuk <span className="font-semibold">{sekolah.nama_sekolah}</span> sudah ditransfer / disalurkan?</>
                                    : <>Ubah status menjadi <span className="font-semibold">Belum Disalurkan</span> untuk sekolah ini?</>
                                }
                            </p>
                            <p className="text-xs text-slate-400 bg-slate-50 rounded-lg p-3 border border-slate-200">
                                Pastikan transfer sudah dikonfirmasi sebelum mengubah status ini.
                            </p>
                            <div className="flex justify-end gap-2 pt-1">
                                <button
                                    onClick={() => setConfirmDana(null)}
                                    className="rounded-lg px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    onClick={processDana}
                                    disabled={formDana.processing}
                                    className={`rounded-lg px-4 py-2 text-xs font-semibold text-white transition-colors disabled:opacity-70 ${
                                        confirmDana === 'Dana Sudah Disalurkan / Ditransfer'
                                            ? 'bg-[#1e2d5a] hover:bg-[#162247]'
                                            : 'bg-amber-600 hover:bg-amber-700'
                                    }`}
                                >
                                    {formDana.processing ? 'Memproses...' : 'Ya, Konfirmasi'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal: Konfirmasi Persetujuan Dokumen / RAB */}
            {confirmApproveTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-sm rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-emerald-100 bg-emerald-50 px-6 py-4">
                            <div className="flex items-center gap-2 text-emerald-800">
                                <CheckCircle size={18} />
                                <h3 className="text-sm font-bold">Konfirmasi Persetujuan</h3>
                            </div>
                            <button onClick={() => setConfirmApproveTarget(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>
                        <div className="px-6 py-5 space-y-4">
                            <p className="text-sm text-slate-700">
                                Setujui <span className="font-semibold text-slate-900">{confirmApproveTarget.title}</span>?
                            </p>
                            <p className="text-xs text-slate-400 bg-slate-50 rounded-lg p-3 border border-slate-200">
                                Setelah disetujui, status berubah menjadi <span className="font-semibold text-emerald-700">Disetujui</span>. Jika sekolah mengunggah ulang, status akan kembali ke Menunggu Verifikasi.
                            </p>
                            <div className="flex justify-end gap-2 pt-1">
                                <button
                                    onClick={() => setConfirmApproveTarget(null)}
                                    className="rounded-lg px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    onClick={processApproval}
                                    disabled={formDoc.processing || formRab.processing}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-xs font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors"
                                >
                                    Ya, Setujui
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal: Revisi */}
            {revisiTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white shadow-xl overflow-hidden">
                        <div className="flex items-center justify-between border-b border-red-100 bg-red-50 px-6 py-4">
                            <div className="flex items-center gap-2 text-red-800">
                                <AlertTriangle size={18} />
                                <h3 className="text-sm font-bold">Minta Revisi: {revisiTarget.name}</h3>
                            </div>
                            <button onClick={() => setRevisiTarget(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>
                        <div className="px-6 py-5">
                            <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                                Catatan / Alasan Revisi <span className="text-red-600">*</span>
                            </label>
                            <textarea
                                rows={4}
                                required
                                value={catatanRevisi}
                                onChange={(e) => setCatatanRevisi(e.target.value)}
                                placeholder="Tuliskan catatan revisi yang jelas agar dapat diperbaiki oleh pihak sekolah..."
                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400 resize-none"
                            />
                            <div className="mt-4 flex justify-end gap-2">
                                <button
                                    onClick={() => setRevisiTarget(null)}
                                    className="rounded-lg px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    onClick={submitRevisi}
                                    disabled={!catatanRevisi.trim() || formDoc.processing || formRab.processing}
                                    className="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700 disabled:opacity-50 transition-colors"
                                >
                                    Kirim Catatan Revisi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
