import React, { useState, useMemo } from 'react';
import { useForm, router, Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { Download, Upload, Printer, X, CheckCircle, Clock, AlertCircle, Search, FileText, Plus, Trash2, Camera, Image, Eye, AlertTriangle } from 'lucide-react';
import DocxViewer from '../../Components/DocxViewer';

interface Props {
    sekolah: {
        id: number;
        npsn: string;
        nama_sekolah: string;
        provinsi: string;
        kabupaten: string;
        alamat: string;
        rt?: string | null;
        rw?: string | null;
        nomor_bangunan?: string | null;
        desa_kelurahan?: string | null;
        kecamatan?: string | null;
        kode_pos?: string | null;
        nama_kepsek: string;
        nip_kepsek: string;
        nama_bendahara: string;
        nip_bendahara: string;
        nama_bank?: string | null;
        nomor_rekening?: string | null;
        atas_nama_rekening?: string | null;
        no_telepon?: string | null;
        email_sekolah?: string | null;
        nama_ketua_komite?: string | null;
        status_dana: string;
        status_dokumen: string;
        dokumens: Array<{
            id: number;
            jenis_dokumen: string;
            file_path: string | null;
            meta?: {
                slots?: Record<string, string>;
            } | null;
            status: string;
            catatan_revisi: string | null;
        }>;
        rab: {
            id: number;
            merek_tipe_laptop: string;
            spesifikasi_ringkas: string;
            items?: Array<{
                merek_tipe_laptop: string;
                spesifikasi_ringkas: string;
                jumlah_unit: number;
                harga_satuan: number;
                total_harga?: number;
            }> | null;
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
    'Belum Diunggah': 'bg-slate-100 text-slate-500 border border-slate-200 font-medium',
};

const statusIcon: Record<string, React.ReactNode> = {
    'Disetujui': <CheckCircle size={13} />,
    'Menunggu Verifikasi': <Clock size={13} />,
    'Revisi': <AlertCircle size={13} />,
};

const docItems = [
    // Tahap 1 — Persiapan & RAB (Lampiran III, V, VI, IV)
    { key: 'pks', label: 'Perjanjian Kerja Sama (PKS)', stage: 1, stageName: '1. Persiapan & RAB', pdfType: 'pks' },
    { key: 'pakta_integritas', label: 'Pakta Integritas', stage: 1, stageName: '1. Persiapan & RAB', pdfType: 'pakta_integritas' },
    { key: 'sptjm', label: 'Surat Pernyataan (SPTJM)', stage: 1, stageName: '1. Persiapan & RAB', pdfType: 'sptjm' },
    { key: 'rab', label: 'Rencana Anggaran Biaya (RAB)', stage: 1, stageName: '1. Persiapan & RAB', pdfType: 'rab' },
    // Tahap 2 — Penyaluran Dana (Lampiran X)
    { key: 'laporan_awal', label: 'Laporan Awal', stage: 2, stageName: '2. Penyaluran Dana', pdfType: 'laporan_awal' },
    // Tahap 3 — Pengadaan SIPLah (Lampiran VIII + upload)
    { key: 'perbandingan_siplah', label: 'Perbandingan Produk SIPLah', stage: 3, stageName: '3. Pengadaan SIPLah', pdfType: 'perbandingan_siplah' },
    { key: 'surat_pemesanan_siplah', label: 'Surat Pemesanan SIPLah', stage: 3, stageName: '3. Pengadaan SIPLah', pdfType: null },
    { key: 'invoice_siplah', label: 'Invoice / Faktur SIPLah', stage: 3, stageName: '3. Pengadaan SIPLah', pdfType: null },
    // Tahap 4 — Penerimaan Barang (Lampiran VII)
    { key: 'bast', label: 'Berita Acara Serah Terima (BAST)', stage: 4, stageName: '4. Penerimaan Barang', pdfType: 'bast' },
    // Tahap 5 — Inventarisasi
    { key: 'buku_inventaris', label: 'Buku Inventaris', stage: 5, stageName: '5. Inventarisasi', pdfType: 'buku_inventaris' },
    // Tahap 6 — Pemanfaatan
    { key: 'dokumentasi_pemanfaatan', label: 'Dokumentasi Pemanfaatan', stage: 6, stageName: '6. Pemanfaatan', pdfType: 'dokumentasi_pemanfaatan' },
    // Tahap 7 — Pelaporan & LPJ (Lampiran XI, XII, XIII + upload)
    { key: 'laporan_akhir', label: 'Laporan Akhir', stage: 7, stageName: '7. Pelaporan & LPJ', pdfType: 'laporan_akhir' },
    { key: 'pengantar_lpj', label: 'Pengantar Laporan Pertanggungjawaban', stage: 7, stageName: '7. Pelaporan & LPJ', pdfType: 'pengantar_lpj' },
    { key: 'lpj', label: 'Laporan Pertanggungjawaban Penggunaan Dana', stage: 7, stageName: '7. Pelaporan & LPJ', pdfType: 'lpj' },
    { key: 'bukti_setor_sisa_dana', label: 'Bukti Setor Sisa Dana (jika ada)', stage: 7, stageName: '7. Pelaporan & LPJ', pdfType: null },
];

type TabKey = 'dokumen' | 'rab' | 'profile';
const fmt = (n: number) => new Intl.NumberFormat('id-ID').format(n);
const PAGU = 69364000;

export default function SekolahDashboard({ sekolah }: Props) {
    const [tab, setTab] = useState<TabKey>('dokumen');
    const [uploadDocKey, setUploadDocKey] = useState<string | null>(null);
    const [previewModalDoc, setPreviewModalDoc] = useState<{ id: number; title: string; filePath: string; status: string; catatanRevisi?: string | null } | null>(null);
    const [deleteDocTarget, setDeleteDocTarget] = useState<{ id: number; title: string } | null>(null);
    const [isDeletingDoc, setIsDeletingDoc] = useState(false);
    const [searchDocQuery, setSearchDocQuery] = useState('');
    const [filterDocStatus, setFilterDocStatus] = useState('semua');

    const formProfil = useForm({
        alamat: sekolah.alamat ?? '',
        rt: sekolah.rt ?? '',
        rw: sekolah.rw ?? '',
        nomor_bangunan: sekolah.nomor_bangunan ?? '',
        desa_kelurahan: sekolah.desa_kelurahan ?? '',
        kecamatan: sekolah.kecamatan ?? '',
        kode_pos: sekolah.kode_pos ?? '',
        nama_kepsek: sekolah.nama_kepsek ?? '',
        nip_kepsek: sekolah.nip_kepsek ?? '',
        nama_bendahara: sekolah.nama_bendahara ?? '',
        nip_bendahara: sekolah.nip_bendahara ?? '',
        nama_bank: sekolah.nama_bank ?? '',
        nomor_rekening: sekolah.nomor_rekening ?? '',
        atas_nama_rekening: sekolah.atas_nama_rekening ?? '',
        no_telepon: sekolah.no_telepon ?? '',
        email_sekolah: sekolah.email_sekolah ?? '',
        nama_ketua_komite: sekolah.nama_ketua_komite ?? '',
    });

    interface RabItemRow {
        merek_tipe_laptop: string;
        spesifikasi_ringkas: string;
        jumlah_unit: number;
        harga_satuan: number;
    }

    const isDraft = !sekolah.rab || sekolah.rab.status === 'Draft';

    const initialRabItems: RabItemRow[] = !isDraft && sekolah.rab?.items && sekolah.rab.items.length > 0
        ? sekolah.rab.items.map(it => ({
            merek_tipe_laptop: it.merek_tipe_laptop || '',
            spesifikasi_ringkas: it.spesifikasi_ringkas || '',
            jumlah_unit: it.jumlah_unit || 8,
            harga_satuan: it.harga_satuan || 0,
        }))
        : [{
            merek_tipe_laptop: isDraft ? '' : (sekolah.rab?.merek_tipe_laptop ?? ''),
            spesifikasi_ringkas: isDraft ? '' : (sekolah.rab?.spesifikasi_ringkas ?? ''),
            jumlah_unit: 8,
            harga_satuan: isDraft ? 0 : (sekolah.rab?.harga_satuan ?? 0),
        }];

    const [rabRows, setRabRows] = useState<RabItemRow[]>(initialRabItems);

    const formRab = useForm({
        items: initialRabItems,
        merek_tipe_laptop: initialRabItems[0]?.merek_tipe_laptop ?? '',
        spesifikasi_ringkas: initialRabItems[0]?.spesifikasi_ringkas ?? '',
        jumlah_unit: initialRabItems[0]?.jumlah_unit ?? 8,
        harga_satuan: initialRabItems[0]?.harga_satuan ?? 0,
    });

    const updateRabRow = (index: number, field: keyof RabItemRow, value: any) => {
        setRabRows((prev) => {
            const next = [...prev];
            next[index] = { ...next[index], [field]: value };
            return next;
        });
    };

    const addRabRow = () => {
        if (rabRows.length >= 4) return;
        setRabRows((prev) => [
            ...prev,
            {
                merek_tipe_laptop: '',
                spesifikasi_ringkas: '',
                jumlah_unit: 1,
                harga_satuan: 0,
            },
        ]);
    };

    const removeRabRow = (index: number) => {
        if (rabRows.length <= 1) return;
        setRabRows((prev) => prev.filter((_, idx) => idx !== index));
    };

    const totalUnitRAB = useMemo(() => {
        return rabRows.reduce((acc, row) => acc + (Number(row.jumlah_unit) || 0), 0);
    }, [rabRows]);

    const totalBiayaRAB = useMemo(() => {
        return rabRows.reduce((acc, row) => acc + ((Number(row.jumlah_unit) || 0) * (Number(row.harga_satuan) || 0)), 0);
    }, [rabRows]);

    // Single upload form (standard documents)
    const formUpload = useForm({ jenis_dokumen: '', file: null as File | null });

    // 6-Slot Photo upload form (for dokumentasi_pemanfaatan)
    const formUpload6 = useForm({
        jenis_dokumen: 'dokumentasi_pemanfaatan',
        slot_1: null as File | null,
        slot_2: null as File | null,
        slot_3: null as File | null,
        slot_4: null as File | null,
        slot_5: null as File | null,
        slot_6: null as File | null,
    });

    const [slotPreviews, setSlotPreviews] = useState<Record<number, string>>({});

    const handleSlotFileChange = (slotIndex: number, file: File | null) => {
        formUpload6.setData(`slot_${slotIndex}` as any, file);
        if (file) {
            const previewUrl = URL.createObjectURL(file);
            setSlotPreviews((prev) => ({ ...prev, [slotIndex]: previewUrl }));
        } else {
            setSlotPreviews((prev) => {
                const next = { ...prev };
                delete next[slotIndex];
                return next;
            });
        }
    };

    const openUploadModal = (docKey: string) => {
        setUploadDocKey(docKey);
        formUpload.setData({ jenis_dokumen: docKey, file: null });
        formUpload.clearErrors();
    };

    const rabApproved = sekolah.rab?.status === 'Disetujui';
    const danaDisalurkan = sekolah.status_dana === 'Dana Sudah Disalurkan / Ditransfer';

    const currentStage = useMemo(() => {
        if (!rabApproved) return 1;
        if (!danaDisalurkan) return 2;
        const siplahDone = sekolah.dokumens.some(d => d.jenis_dokumen === 'invoice_siplah' && d.status === 'Disetujui');
        if (!siplahDone) return 3;
        const bastDone = sekolah.dokumens.some(d => d.jenis_dokumen === 'bast' && d.status === 'Disetujui');
        if (!bastDone) return 4;
        const inventarisDone = sekolah.dokumens.some(d => d.jenis_dokumen === 'buku_inventaris' && d.status === 'Disetujui');
        if (!inventarisDone) return 5;
        const pemanfaatanDone = sekolah.dokumens.some(d => d.jenis_dokumen === 'dokumentasi_pemanfaatan' && d.status === 'Disetujui');
        if (!pemanfaatanDone) return 6;
        const lpjDone = sekolah.dokumens.some(d => d.jenis_dokumen === 'lpj' && d.status === 'Disetujui');
        if (!lpjDone) return 7;
        return 7;
    }, [rabApproved, danaDisalurkan, sekolah.dokumens]);

    const [filterStage, setFilterStage] = useState<string>(String(currentStage));

    const filteredDocs = useMemo(() => {
        return docItems.filter((item) => {
            const doc = sekolah.dokumens.find((d) => d.jenis_dokumen === item.key);
            const status = doc?.status ?? 'Belum Diunggah';
            const matchesSearch = item.label.toLowerCase().includes(searchDocQuery.toLowerCase());
            const matchesStatus = filterDocStatus === 'semua' || status === filterDocStatus;
            const matchesStage = filterStage === 'semua' || item.stage === parseInt(filterStage);
            return matchesSearch && matchesStatus && matchesStage;
        });
    }, [sekolah.dokumens, searchDocQuery, filterDocStatus, filterStage]);

    const handleProfil = (e: React.FormEvent) => {
        e.preventDefault();
        formProfil.post('/sekolah/profil');
    };

    const handleRab = (e: React.FormEvent) => {
        e.preventDefault();
        formRab.transform(() => ({
            items: rabRows,
            merek_tipe_laptop: rabRows.map(r => r.merek_tipe_laptop).filter(Boolean).join(' & '),
            spesifikasi_ringkas: rabRows.map(r => r.spesifikasi_ringkas).filter(Boolean).join(' | '),
            jumlah_unit: totalUnitRAB,
            harga_satuan: rabRows[0]?.harga_satuan || 0,
        }));
        formRab.post('/sekolah/rab');
    };

    const handleUpload = (e: React.FormEvent) => {
        e.preventDefault();
        if (!uploadDocKey || !formUpload.data.file) return;
        formUpload.transform(() => ({
            jenis_dokumen: uploadDocKey,
            file: formUpload.data.file,
        }));
        formUpload.post('/sekolah/dokumen/upload', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setUploadDocKey(null);
                formUpload.reset();
            },
        });
    };

    const handleUpload6 = (e: React.FormEvent) => {
        e.preventDefault();
        formUpload6.transform(() => ({
            ...formUpload6.data,
            jenis_dokumen: 'dokumentasi_pemanfaatan',
        }));
        formUpload6.post('/sekolah/dokumen/upload', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setUploadDocKey(null);
                formUpload6.reset();
                setSlotPreviews({});
            },
        });
    };

    const handleDeleteDoc = () => {
        if (!deleteDocTarget) return;
        setIsDeletingDoc(true);
        router.delete(`/sekolah/dokumen/${deleteDocTarget.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setDeleteDocTarget(null);
                if (previewModalDoc?.id === deleteDocTarget.id) {
                    setPreviewModalDoc(null);
                }
            },
            onFinish: () => setIsDeletingDoc(false),
        });
    };

    const docApproved = sekolah.dokumens.filter((d) => d.status === 'Disetujui').length;
    const totalBiaya = formRab.data.jumlah_unit * formRab.data.harga_satuan;
    const sisaDana = PAGU - (sekolah.rab?.total_harga ?? 0);
    const hasSisaDana = sisaDana > 0 && sekolah.rab?.status === 'Disetujui';
    const isUangPas = sisaDana <= 0 && sekolah.rab?.status === 'Disetujui';
    const targetTotalDocs = hasSisaDana ? 15 : 14;
    const buktiSetorDoc = sekolah.dokumens.find(d => d.jenis_dokumen === 'bukti_setor_sisa_dana');

    const tabs: { key: TabKey; label: string }[] = [
        { key: 'dokumen', label: 'Dokumen & Berkas' },
        { key: 'rab', label: 'RAB Laptop' },
        { key: 'profile', label: 'Profile Sekolah' },
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
                                <span className={`inline-block mt-0.5 rounded border px-2 py-0.5 text-xs font-semibold ${
                                    sekolah.status_dana === 'Dana Sudah Disalurkan / Ditransfer'
                                        ? 'border-emerald-300 bg-emerald-50 text-emerald-800'
                                        : 'border-amber-300 bg-amber-50 text-amber-800'
                                }`}>
                                    {sekolah.status_dana === 'Dana Sudah Disalurkan / Ditransfer' ? 'Sudah Disalurkan' : 'Belum Disalurkan'}
                                </span>
                            </div>
                            <div>
                                <p className="text-xs text-slate-400 uppercase tracking-wide">Kelengkapan Berkas</p>
                                <p className="mt-0.5 font-semibold text-slate-700">{docApproved} / {targetTotalDocs} dokumen disetujui</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Sisa Dana Notification Card — Only shown when school reaches Stage 7 (Pelaporan & LPJ) */}
                {currentStage >= 7 && (
                    hasSisaDana ? (
                        <div className={`rounded-xl border p-4 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 ${
                            buktiSetorDoc?.status === 'Disetujui'
                                ? 'border-emerald-200 bg-emerald-50/80 text-emerald-900'
                                : buktiSetorDoc?.status === 'Menunggu Verifikasi'
                                ? 'border-amber-200 bg-amber-50/80 text-amber-900'
                                : 'border-red-200 bg-red-50/80 text-red-900'
                        }`}>
                            <div className="flex items-start gap-3">
                                <div className={`p-2 rounded-lg mt-0.5 ${
                                    buktiSetorDoc?.status === 'Disetujui'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : buktiSetorDoc?.status === 'Menunggu Verifikasi'
                                        ? 'bg-amber-100 text-amber-700'
                                        : 'bg-red-100 text-red-700'
                                }`}>
                                    {buktiSetorDoc?.status === 'Disetujui' ? <CheckCircle size={20} /> : <AlertTriangle size={20} />}
                                </div>
                                <div>
                                    <div className="flex items-center gap-2 flex-wrap">
                                        <h4 className="text-sm font-bold">
                                            Terdapat Sisa Dana Bantuan: Rp {fmt(sisaDana)}
                                        </h4>
                                        <span className={`text-[11px] font-bold px-2 py-0.5 rounded-md ${
                                            buktiSetorDoc?.status === 'Disetujui'
                                                ? 'bg-emerald-200 text-emerald-800'
                                                : buktiSetorDoc?.status === 'Menunggu Verifikasi'
                                                ? 'bg-amber-200 text-amber-800'
                                                : 'bg-red-200 text-red-800'
                                        }`}>
                                            {buktiSetorDoc?.status === 'Disetujui'
                                                ? 'Sudah Dikembalikan & Disetujui'
                                                : buktiSetorDoc?.status === 'Menunggu Verifikasi'
                                                ? 'Menunggu Verifikasi Bukti Setor'
                                                : 'Belum Dikembalikan ke Kas Negara'}
                                        </span>
                                    </div>
                                    <p className="text-xs mt-1 leading-relaxed opacity-90">
                                        {buktiSetorDoc?.status === 'Disetujui'
                                            ? 'Bukti Penerimaan Negara (BPN) pengembalian sisa dana telah diverifikasi dan disetujui oleh Verifikator.'
                                            : buktiSetorDoc?.status === 'Menunggu Verifikasi'
                                            ? 'Berkas bukti setor sisa dana sedang diperiksa oleh Verifikator. Mohon menunggu hasil verifikasi.'
                                            : 'Sesuai ketentuan, sisa dana belanja laptop wajib disetorkan kembali ke Kas Negara melalui bank persepsi. Silakan unggah Bukti Penerimaan Negara (BPN) pada daftar dokumen Tahap 7.'}
                                    </p>
                                    {buktiSetorDoc?.catatan_revisi && (
                                        <p className="text-xs font-semibold text-red-700 mt-1">
                                            Catatan Revisi: {buktiSetorDoc.catatan_revisi}
                                        </p>
                                    )}
                                </div>
                            </div>
                            {buktiSetorDoc?.status !== 'Disetujui' && (
                                <button
                                    onClick={() => openUploadModal('bukti_setor_sisa_dana')}
                                    className="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-[#1e2d5a] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors shadow-sm"
                                >
                                    <Upload size={13} />
                                    {buktiSetorDoc?.file_path ? 'Unggah Ulang Bukti Setor' : 'Unggah Bukti Setor (BPN)'}
                                </button>
                            )}
                        </div>
                    ) : isUangPas ? (
                        <div className="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-sm flex items-center gap-3 text-emerald-900">
                            <div className="p-2 rounded-lg bg-emerald-100 text-emerald-700">
                                <CheckCircle size={20} />
                            </div>
                            <div>
                                <h4 className="text-sm font-bold text-emerald-950">
                                    Dana Bantuan Habis Digunakan (Uang Pas Rp 69.364.000)
                                </h4>
                                <p className="text-xs text-emerald-700 mt-0.5">
                                    Total belanja laptop sesuai pagu bantuan (Rp 0 sisa dana). Sekolah tidak perlu menyetorkan sisa dana ke Kas Negara.
                                </p>
                            </div>
                        </div>
                    ) : null
                )}

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
                        className="inline-flex items-center gap-2 rounded-lg bg-[#1e2d5a] px-4 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors flex-shrink-0 ml-4"
                    >
                        <Printer size={14} />
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
                                    Daftar 15 Berkas ({filteredDocs.length} ditampilkan)
                                </span>
                                <div className="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                                    <select
                                        value={filterStage}
                                        onChange={(e) => setFilterStage(e.target.value)}
                                        className="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                    >
                                        <option value="semua">Semua Tahap (1-7)</option>
                                        <option value="1">Tahap 1: Persiapan & RAB {currentStage === 1 ? '(Tahap Berjalan)' : ''}</option>
                                        <option value="2">Tahap 2: Penyaluran Dana {currentStage === 2 ? '(Tahap Berjalan)' : ''}</option>
                                        <option value="3">Tahap 3: Pengadaan SIPLah {currentStage === 3 ? '(Tahap Berjalan)' : ''}</option>
                                        <option value="4">Tahap 4: Penerimaan Barang {currentStage === 4 ? '(Tahap Berjalan)' : ''}</option>
                                        <option value="5">Tahap 5: Inventarisasi {currentStage === 5 ? '(Tahap Berjalan)' : ''}</option>
                                        <option value="6">Tahap 6: Pemanfaatan {currentStage === 6 ? '(Tahap Berjalan)' : ''}</option>
                                        <option value="7">Tahap 7: Pelaporan & LPJ {currentStage === 7 ? '(Tahap Berjalan)' : ''}</option>
                                    </select>
                                    <div className="relative w-full sm:w-48">
                                        <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
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
                                            {['No', 'Nama Dokumen', 'Status', 'Catatan Revisi', 'Unduh Format / Berkas', 'Aksi'].map((h) => (
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
                                                const isSisaDoc = item.key === 'bukti_setor_sisa_dana';
                                                const docNotNeeded = isSisaDoc && isUangPas;

                                                let status = doc?.status ?? 'Belum Diunggah';
                                                if (docNotNeeded) {
                                                    status = 'Tidak Diperlukan (Uang Pas)';
                                                } else if (isSisaDoc && hasSisaDana && status === 'Belum Diunggah') {
                                                    status = 'Belum Diunggah (Wajib)';
                                                }

                                                const isApproved = doc?.status === 'Disetujui';
                                                return (
                                                    <tr key={item.key} className="hover:bg-slate-50 transition-colors">
                                                        <td className="px-5 py-3.5 text-slate-400 text-xs">{idx + 1}</td>
                                                        <td className="px-5 py-3.5 font-medium text-slate-800 max-w-xs">
                                                            <div>{item.label}</div>
                                                            {isSisaDoc && hasSisaDana && (
                                                                <span className="text-[11px] font-semibold text-red-600 block mt-0.5">
                                                                    Wajib setor sisa dana Rp {fmt(sisaDana)} ke Kas Negara
                                                                </span>
                                                            )}
                                                            {isSisaDoc && isUangPas && (
                                                                <span className="text-[11px] text-emerald-600 block mt-0.5">
                                                                    Uang pas Rp 69.364.000 (tidak ada sisa dana)
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-5 py-3.5">
                                                            <span className={`inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs ${
                                                                docNotNeeded
                                                                    ? 'bg-slate-100 text-slate-600 border border-slate-200'
                                                                    : status === 'Belum Diunggah (Wajib)'
                                                                    ? 'bg-red-50 text-red-700 border border-red-200 font-semibold'
                                                                    : statusStyle[doc?.status ?? 'Belum Diunggah'] ?? 'bg-slate-100 text-slate-500'
                                                            }`}>
                                                                {docNotNeeded ? <CheckCircle size={13} /> : statusIcon[doc?.status ?? '']}
                                                                {status}
                                                            </span>
                                                        </td>
                                                        <td className="px-5 py-3.5 text-xs text-red-600 max-w-xs font-medium">
                                                            {docNotNeeded ? 'Dana bantuan habis sesuai RAB' : (doc?.catatan_revisi ?? '—')}
                                                        </td>
                                                        <td className="px-5 py-3.5">
                                                            <div className="flex flex-col gap-1.5">
                                                                {item.key === 'perbandingan_siplah' ? (
                                                                    <>
                                                                        <a
                                                                            href="/sekolah/dokumen/download/perbandingan_siplah"
                                                                            target="_blank"
                                                                            rel="noreferrer"
                                                                            className="inline-flex items-center gap-1 text-xs font-medium text-[#1e2d5a] hover:underline"
                                                                        >
                                                                            <Download size={12} />
                                                                            1. Format Perbandingan Produk
                                                                        </a>
                                                                        <a
                                                                            href="/sekolah/dokumen/download/survey_harga"
                                                                            target="_blank"
                                                                            rel="noreferrer"
                                                                            className="inline-flex items-center gap-1 text-xs font-medium text-[#1e2d5a] hover:underline"
                                                                        >
                                                                            <Download size={12} />
                                                                            2. Format Survei Harga Pasar
                                                                        </a>
                                                                    </>
                                                                ) : item.pdfType ? (
                                                                    <a
                                                                        href={`/sekolah/dokumen/download/${item.pdfType}`}
                                                                        target="_blank"
                                                                        rel="noreferrer"
                                                                        className="inline-flex items-center gap-1 text-xs font-medium text-[#1e2d5a] hover:underline"
                                                                    >
                                                                        <Download size={12} />
                                                                        Unduh Format DOCX
                                                                    </a>
                                                                ) : null}
                                                                {doc?.id && doc.file_path && (
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => setPreviewModalDoc({
                                                                            id: doc.id,
                                                                            title: item.label,
                                                                            filePath: doc.file_path!,
                                                                            status: doc.status,
                                                                            catatanRevisi: doc.catatan_revisi,
                                                                        })}
                                                                        className="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 hover:text-emerald-900 hover:underline text-left cursor-pointer"
                                                                    >
                                                                        <Eye size={12} />
                                                                        Preview Berkas Terunggah
                                                                    </button>
                                                                )}
                                                                {!item.pdfType && item.key !== 'perbandingan_siplah' && !doc?.file_path && (
                                                                    <span className="text-xs text-slate-300">—</span>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="px-5 py-3.5">
                                                            {docNotNeeded ? (
                                                                <span className="text-xs text-slate-400 font-medium italic">Tidak Perlu Unggah</span>
                                                            ) : (
                                                                <div className="flex items-center gap-1.5 flex-wrap">
                                                                    <button
                                                                        onClick={() => openUploadModal(item.key)}
                                                                        className="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-400 transition-colors shadow-xs"
                                                                    >
                                                                        <Upload size={12} />
                                                                        {doc?.file_path ? 'Unggah Ulang' : 'Unggah'}
                                                                    </button>
                                                                    {doc?.id && doc.file_path && !isApproved && (
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => setDeleteDocTarget({ id: doc.id, title: item.label })}
                                                                            title="Hapus berkas ini"
                                                                            className="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50/70 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100 hover:text-red-700 transition-colors shadow-xs"
                                                                        >
                                                                            <Trash2 size={12} />
                                                                            Hapus
                                                                        </button>
                                                                    )}
                                                                </div>
                                                            )}
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
                            {sekolah.rab && sekolah.rab.status !== 'Draft' && (
                                <div className="mb-5 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
                                    <p className="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Status RAB Saat Ini</p>
                                    <div className="flex flex-wrap gap-6 items-center">
                                        <div>
                                            <p className="text-xs text-slate-400 uppercase font-medium">Merek / Tipe</p>
                                            <p className="font-bold text-slate-800 mt-0.5">{sekolah.rab.merek_tipe_laptop}</p>
                                        </div>
                                        <div className="flex-1 min-w-[180px]">
                                            <p className="text-xs text-slate-400 uppercase font-medium">Spesifikasi</p>
                                            <p className="font-medium text-slate-700 mt-0.5 text-xs leading-relaxed">{sekolah.rab.spesifikasi_ringkas}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-slate-400 uppercase font-medium">Total Anggaran</p>
                                            <p className="font-bold text-[#1e2d5a] mt-0.5">Rp {fmt(sekolah.rab.total_harga)}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-slate-400 uppercase font-medium">Sisa Dana</p>
                                            <p className={`font-bold mt-0.5 ${sisaDana >= 0 ? 'text-emerald-700' : 'text-red-600'}`}>
                                                Rp {fmt(sisaDana)}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-slate-400 uppercase font-medium">Status</p>
                                            <span className={`inline-flex items-center gap-1.5 mt-0.5 px-2.5 py-1 text-xs rounded-md ${statusStyle[sekolah.rab.status] ?? 'bg-slate-100 text-slate-500'}`}>
                                                {statusIcon[sekolah.rab.status]}
                                                {sekolah.rab.status}
                                            </span>
                                        </div>
                                    </div>
                                    {sekolah.rab.catatan_revisi && (
                                        <p className="mt-3 text-xs text-red-600 font-semibold border-t border-slate-200 pt-3">
                                            Catatan Revisi: {sekolah.rab.catatan_revisi}
                                        </p>
                                    )}
                                </div>
                            )}

                            {rabApproved ? (
                                <div className="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                    <p className="font-semibold">RAB telah disetujui dan dikunci.</p>
                                    <p className="mt-1 text-xs text-emerald-600">RAB yang sudah disetujui tidak dapat diubah lagi. Hubungi Verifikator jika perlu revisi.</p>
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                                    {/* Left: RAB Form (Multi-Item Supported) */}
                                    <form onSubmit={handleRab} className="space-y-4 lg:col-span-6">
                                        <div className="space-y-4">
                                            {rabRows.map((row, index) => (
                                                <div key={index} className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-3 relative">
                                                    <div className="flex items-center justify-between pb-2 border-b border-slate-100">
                                                        <span className="text-xs font-bold text-slate-800 uppercase tracking-wide">
                                                            Item #{index + 1} Pengadaan Laptop
                                                        </span>
                                                        {rabRows.length > 1 && (
                                                            <button
                                                                type="button"
                                                                onClick={() => removeRabRow(index)}
                                                                className="text-xs text-red-500 hover:text-red-700 flex items-center gap-1"
                                                                title="Hapus Baris Item"
                                                            >
                                                                <Trash2 size={13} />
                                                                Hapus
                                                            </button>
                                                        )}
                                                    </div>

                                                    <div>
                                                        <label className="block text-xs font-semibold text-slate-700 mb-1">Merek & Tipe Laptop</label>
                                                        <input
                                                            type="text"
                                                            required
                                                            value={row.merek_tipe_laptop}
                                                            onChange={(e) => updateRabRow(index, 'merek_tipe_laptop', e.target.value)}
                                                            placeholder="Contoh: ASUS Chromebook C423 / Acer Aspire 3"
                                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                        />
                                                    </div>

                                                    <div>
                                                        <div className="flex items-center justify-between mb-1">
                                                            <label className="block text-xs font-semibold text-slate-700">
                                                                Spesifikasi Laptop
                                                            </label>
                                                            <button
                                                                type="button"
                                                                onClick={() => updateRabRow(index, 'spesifikasi_ringkas', 'Processor 4 Core / 8 Thread, Layar 14 inch, RAM 8GB, SSD 256GB, OS GUI Legal, Port HDMI/USB, Garansi 1 Tahun')}
                                                                className="text-[11px] font-medium text-[#1e2d5a] hover:underline"
                                                            >
                                                                Gunakan Rekomendasi Standar
                                                            </button>
                                                        </div>
                                                        <textarea
                                                            rows={3}
                                                            required
                                                            value={row.spesifikasi_ringkas}
                                                            onChange={(e) => updateRabRow(index, 'spesifikasi_ringkas', e.target.value)}
                                                            placeholder="Tuliskan spesifikasi detail laptop yang diajukan..."
                                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a] resize-none"
                                                        />
                                                    </div>

                                                    <div className="grid grid-cols-2 gap-3">
                                                        <div>
                                                            <label className="block text-xs font-semibold text-slate-700 mb-1">Jumlah Unit</label>
                                                            <input
                                                                type="number"
                                                                min={1}
                                                                required
                                                                value={row.jumlah_unit || ''}
                                                                onChange={(e) => updateRabRow(index, 'jumlah_unit', parseInt(e.target.value) || 0)}
                                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                            />
                                                        </div>
                                                        <div>
                                                            <label className="block text-xs font-semibold text-slate-700 mb-1">Harga Satuan (Maks. 8.625.000)</label>
                                                            <input
                                                                type="number"
                                                                max={8625000}
                                                                required
                                                                value={row.harga_satuan || ''}
                                                                onChange={(e) => updateRabRow(index, 'harga_satuan', parseFloat(e.target.value) || 0)}
                                                                placeholder="Contoh: 8600000"
                                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                            />
                                                        </div>
                                                    </div>

                                                    <div className="text-right pt-1">
                                                        <span className="text-xs text-slate-500">Subtotal Item: </span>
                                                        <span className="text-xs font-bold text-slate-800">
                                                            Rp {fmt((Number(row.jumlah_unit) || 0) * (Number(row.harga_satuan) || 0))}
                                                        </span>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>

                                        {rabRows.length < 4 && (
                                            <button
                                                type="button"
                                                onClick={addRabRow}
                                                className="w-full flex items-center justify-center gap-1.5 rounded-lg border border-dashed border-blue-300 bg-blue-50/40 py-2.5 text-xs font-semibold text-[#1e2d5a] hover:bg-blue-50 transition-colors"
                                            >
                                                <Plus size={14} />
                                                Tambah Baris Laptop (Jika Membeli Berbeda Tipe / Varian)
                                            </button>
                                        )}

                                        <div className="rounded-lg border border-slate-200 bg-slate-50 p-4 space-y-2">
                                            <div className="flex items-center justify-between text-xs text-slate-600">
                                                <span>Total Unit yang Diajukan:</span>
                                                <span className={`font-bold ${totalUnitRAB < 8 ? 'text-red-600' : 'text-emerald-700'}`}>
                                                    {totalUnitRAB} Unit {totalUnitRAB < 8 ? '(Wajib Min. 8 Unit)' : '✓ (Sesuai Ketentuan)'}
                                                </span>
                                            </div>
                                            <div className="flex items-center justify-between border-t border-slate-200 pt-2">
                                                <div>
                                                    <p className="text-xs text-slate-500 font-medium">Total Biaya RAB</p>
                                                    <p className={`mt-1 text-2xl font-bold ${totalBiayaRAB > PAGU ? 'text-red-600' : 'text-[#1e2d5a]'}`}>
                                                        Rp {fmt(totalBiayaRAB)}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-xs text-slate-500 font-medium">Alokasi Pagu Bantuan</p>
                                                    <p className="mt-1 text-sm font-semibold text-slate-700">Rp {fmt(PAGU)}</p>
                                                </div>
                                            </div>
                                            {totalBiayaRAB > PAGU && (
                                                <p className="mt-1 text-xs text-red-600 font-medium">⚠️ Melebihi nilai pagu bantuan Rp {fmt(PAGU)}</p>
                                            )}
                                        </div>

                                        <button
                                            type="submit"
                                            disabled={formRab.processing || totalBiayaRAB > PAGU || totalUnitRAB < 8}
                                            className="rounded-lg bg-[#1e2d5a] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors shadow-sm"
                                        >
                                            {formRab.processing ? 'Menyimpan...' : 'Simpan & Ajukan RAB'}
                                        </button>
                                    </form>

                                    {/* Right: Rekomendasi Spesifikasi Minimum Table */}
                                    <div className="lg:col-span-6 rounded-xl border border-blue-100 bg-gradient-to-b from-blue-50/50 to-white p-5 shadow-sm">
                                        <div className="flex items-center justify-between pb-3 border-b border-blue-100 mb-3">
                                            <div>
                                                <h4 className="text-xs font-bold text-[#1e2d5a] uppercase tracking-wider">
                                                    Lampiran I : Rekomendasi Spesifikasi Minimum
                                                </h4>
                                                <p className="text-[11px] text-slate-500 mt-0.5">Mengacu kepada Hasil Kajian Bantuan Laptop TIK SMP</p>
                                            </div>
                                            <span className="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-semibold text-blue-800">
                                                Standar Resmi
                                            </span>
                                        </div>

                                        <div className="overflow-x-auto">
                                            <table className="min-w-full text-left text-xs divide-y divide-slate-200">
                                                <thead className="bg-slate-100/70 text-slate-700 font-semibold">
                                                    <tr>
                                                        <th className="py-2 px-2.5 w-8">No</th>
                                                        <th className="py-2 px-3 w-40">Komponen / Item</th>
                                                        <th className="py-2 px-3">Spesifikasi Minimum</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-slate-100 text-slate-600">
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">1</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Prosesor / CPU</td>
                                                        <td className="py-2 px-3">Processor 4 core / 8 thread atau setara</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">2</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Ukuran Layar</td>
                                                        <td className="py-2 px-3">Ukuran: 13 - 14 inch</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">3</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Penyimpanan Internal</td>
                                                        <td className="py-2 px-3">Jenis: SSD, Kapasitas: 256 GB</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">4</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">RAM / Memory</td>
                                                        <td className="py-2 px-3">8 GB</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">5</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">I/O Port</td>
                                                        <td className="py-2 px-3">HDMI, USB-C, USB-A</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">6</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Koneksi Jaringan</td>
                                                        <td className="py-2 px-3">Wi-Fi, Bluetooth</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">7</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Audio & Kamera</td>
                                                        <td className="py-2 px-3">Audio & Kamera Terintegrasi</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">8</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Operating System</td>
                                                        <td className="py-2 px-3">Terinstal OS berbasis GUI yang legal (pre-installed)</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">9</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Kelengkapan</td>
                                                        <td className="py-2 px-3">Adaptor & kabel power, dus box, buku manual, kartu garansi</td>
                                                    </tr>
                                                    <tr className="hover:bg-blue-50/30">
                                                        <td className="py-2 px-2.5 font-medium text-slate-500">10</td>
                                                        <td className="py-2 px-3 font-medium text-slate-800">Garansi</td>
                                                        <td className="py-2 px-3 font-semibold text-emerald-700">Minimal 1 Tahun</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Tab: Profile */}
                    {tab === 'profile' && (
                        <div className="p-6">
                            <form onSubmit={handleProfil} className="space-y-5 max-w-3xl">
                                <div>
                                    <h4 className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">
                                        Alamat Lengkap Satuan Pendidikan (SMP)
                                    </h4>
                                    <div className="space-y-3.5">
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                Nama Jalan / Alamat
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Contoh: Jl. Pendidikan No. 45"
                                                value={formProfil.data.alamat}
                                                onChange={(e) => formProfil.setData('alamat', e.target.value)}
                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                            />
                                            {formProfil.errors.alamat && <p className="mt-1 text-xs text-red-600">{formProfil.errors.alamat}</p>}
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                    RT (Rukun Tetangga)
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Contoh: 002"
                                                    value={formProfil.data.rt}
                                                    onChange={(e) => formProfil.setData('rt', e.target.value)}
                                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                />
                                                {formProfil.errors.rt && <p className="mt-1 text-xs text-red-600">{formProfil.errors.rt}</p>}
                                            </div>
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                    RW (Rukun Warga)
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Contoh: 005"
                                                    value={formProfil.data.rw}
                                                    onChange={(e) => formProfil.setData('rw', e.target.value)}
                                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                />
                                                {formProfil.errors.rw && <p className="mt-1 text-xs text-red-600">{formProfil.errors.rw}</p>}
                                            </div>
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                    No. Bangunan / Gedung
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Contoh: 45 / 12A"
                                                    value={formProfil.data.nomor_bangunan}
                                                    onChange={(e) => formProfil.setData('nomor_bangunan', e.target.value)}
                                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                />
                                                {formProfil.errors.nomor_bangunan && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nomor_bangunan}</p>}
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                    Desa / Kelurahan
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Contoh: Pasir Putih"
                                                    value={formProfil.data.desa_kelurahan}
                                                    onChange={(e) => formProfil.setData('desa_kelurahan', e.target.value)}
                                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                />
                                                {formProfil.errors.desa_kelurahan && <p className="mt-1 text-xs text-red-600">{formProfil.errors.desa_kelurahan}</p>}
                                            </div>
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                    Kecamatan
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Contoh: Woyla Timur"
                                                    value={formProfil.data.kecamatan}
                                                    onChange={(e) => formProfil.setData('kecamatan', e.target.value)}
                                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                />
                                                {formProfil.errors.kecamatan && <p className="mt-1 text-xs text-red-600">{formProfil.errors.kecamatan}</p>}
                                            </div>
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                    Kode Pos
                                                </label>
                                                <input
                                                    type="text"
                                                    placeholder="Contoh: 23685"
                                                    value={formProfil.data.kode_pos}
                                                    onChange={(e) => formProfil.setData('kode_pos', e.target.value)}
                                                    className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                                />
                                                {formProfil.errors.kode_pos && <p className="mt-1 text-xs text-red-600">{formProfil.errors.kode_pos}</p>}
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200">
                                            <div>
                                                <span className="font-semibold text-slate-700">Kabupaten / Kota:</span> {sekolah.kabupaten}
                                            </div>
                                            <div>
                                                <span className="font-semibold text-slate-700">Provinsi:</span> {sekolah.provinsi}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="border-t border-slate-200 pt-5">
                                    <h4 className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">
                                        Kepala Sekolah & Bendahara
                                    </h4>
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 mb-1">
                                            Nama Kepala Sekolah
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Nama lengkap beserta gelar"
                                            value={formProfil.data.nama_kepsek}
                                            onChange={(e) => formProfil.setData('nama_kepsek', e.target.value)}
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {formProfil.errors.nama_kepsek && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nama_kepsek}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 mb-1">
                                            NIP / NIK Kepala Sekolah
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Contoh: 197501012000031001"
                                            value={formProfil.data.nip_kepsek}
                                            onChange={(e) => formProfil.setData('nip_kepsek', e.target.value)}
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {formProfil.errors.nip_kepsek && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nip_kepsek}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 mb-1">
                                            Nama Bendahara Sekolah
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Nama lengkap bendahara"
                                            value={formProfil.data.nama_bendahara}
                                            onChange={(e) => formProfil.setData('nama_bendahara', e.target.value)}
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {formProfil.errors.nama_bendahara && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nama_bendahara}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 mb-1">
                                            NIP / NIK Bendahara
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Contoh: 198002022005012002"
                                            value={formProfil.data.nip_bendahara}
                                            onChange={(e) => formProfil.setData('nip_bendahara', e.target.value)}
                                            className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                        />
                                        {formProfil.errors.nip_bendahara && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nip_bendahara}</p>}
                                    </div>
                                </div>

                                <div className="border-t border-slate-200 pt-4">
                                    <h4 className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Rekening Bank Bantuan</h4>
                                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                Nama Bank
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Contoh: Bank BRI / Bank Aceh"
                                                value={formProfil.data.nama_bank}
                                                onChange={(e) => formProfil.setData('nama_bank', e.target.value)}
                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                            />
                                            {formProfil.errors.nama_bank && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nama_bank}</p>}
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                Nomor Rekening
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Contoh: 0123-01-000000-50-1"
                                                value={formProfil.data.nomor_rekening}
                                                onChange={(e) => formProfil.setData('nomor_rekening', e.target.value)}
                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                            />
                                            {formProfil.errors.nomor_rekening && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nomor_rekening}</p>}
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                Atas Nama Rekening
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Contoh: SMP NEGERI 3 WOYLA TIMUR"
                                                value={formProfil.data.atas_nama_rekening}
                                                onChange={(e) => formProfil.setData('atas_nama_rekening', e.target.value)}
                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                            />
                                            {formProfil.errors.atas_nama_rekening && <p className="mt-1 text-xs text-red-600">{formProfil.errors.atas_nama_rekening}</p>}
                                        </div>
                                    </div>
                                </div>

                                <div className="border-t border-slate-200 pt-4">
                                    <h4 className="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Kontak & Komite Sekolah</h4>
                                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                Nomor Telepon Sekolah
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Contoh: 0651-12345"
                                                value={formProfil.data.no_telepon}
                                                onChange={(e) => formProfil.setData('no_telepon', e.target.value)}
                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                            />
                                            {formProfil.errors.no_telepon && <p className="mt-1 text-xs text-red-600">{formProfil.errors.no_telepon}</p>}
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                Email Sekolah
                                            </label>
                                            <input
                                                type="email"
                                                placeholder="Contoh: smpn3@sch.id"
                                                value={formProfil.data.email_sekolah}
                                                onChange={(e) => formProfil.setData('email_sekolah', e.target.value)}
                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                            />
                                            {formProfil.errors.email_sekolah && <p className="mt-1 text-xs text-red-600">{formProfil.errors.email_sekolah}</p>}
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 mb-1">
                                                Nama Ketua Komite Sekolah
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Contoh: H. Ahmad Sulaiman, S.Pd."
                                                value={formProfil.data.nama_ketua_komite}
                                                onChange={(e) => formProfil.setData('nama_ketua_komite', e.target.value)}
                                                className="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                            />
                                            {formProfil.errors.nama_ketua_komite && <p className="mt-1 text-xs text-red-600">{formProfil.errors.nama_ketua_komite}</p>}
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-2">
                                    <button
                                        type="submit"
                                        disabled={formProfil.processing}
                                        className="rounded-lg bg-[#1e2d5a] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors shadow-sm"
                                    >
                                        {formProfil.processing ? 'Menyimpan...' : 'Simpan Profile'}
                                    </button>
                                </div>
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
                            <div>
                                <h3 className="text-sm font-semibold text-slate-800">Unggah Berkas</h3>
                                <p className="text-xs text-slate-400 mt-0.5">
                                    {docItems.find(d => d.key === uploadDocKey)?.label}
                                </p>
                            </div>
                            <button onClick={() => setUploadDocKey(null)} className="text-slate-400 hover:text-slate-600">
                                <X size={18} />
                            </button>
                        </div>
                        <form onSubmit={handleUpload} className="px-6 py-5 space-y-4">
                            <div>
                                <p className="text-xs text-slate-500 mb-3">Format yang diizinkan: PDF, JPG, PNG, DOC, DOCX (Maks. 20 MB)</p>
                                <input
                                    type="file"
                                    required
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                    onChange={(e) => {
                                        formUpload.clearErrors();
                                        formUpload.setData('file', e.target.files?.[0] ?? null);
                                    }}
                                    className="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                                />
                                {formUpload.errors.file && (
                                    <p className="mt-2 text-xs font-semibold text-red-600 bg-red-50 p-2 rounded border border-red-200">
                                        {formUpload.errors.file}
                                    </p>
                                )}
                                {formUpload.errors.jenis_dokumen && (
                                    <p className="mt-2 text-xs font-semibold text-red-600 bg-red-50 p-2 rounded border border-red-200">
                                        {formUpload.errors.jenis_dokumen}
                                    </p>
                                )}
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
                                    disabled={formUpload.processing || !formUpload.data.file}
                                    className="rounded-lg bg-[#1e2d5a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#162247] disabled:opacity-70 transition-colors shadow-sm"
                                >
                                    {formUpload.processing ? 'Mengunggah...' : 'Unggah'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Document Preview Modal */}
            {previewModalDoc && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-2 sm:p-5 backdrop-blur-sm">
                    <div className="flex flex-col w-full max-w-5xl max-h-[94vh] rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
                        {/* Header */}
                        <div className="flex items-center justify-between border-b border-slate-100 px-5 sm:px-6 py-3.5 bg-slate-50 flex-shrink-0">
                            <div className="min-w-0 pr-3">
                                <div className="flex items-center gap-2 flex-wrap">
                                    <h3 className="text-sm font-bold text-slate-800 truncate">{previewModalDoc.title}</h3>
                                    <span className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold ${statusStyle[previewModalDoc.status] ?? 'bg-slate-100 text-slate-700'}`}>
                                        {statusIcon[previewModalDoc.status]}
                                        {previewModalDoc.status}
                                    </span>
                                </div>
                                {previewModalDoc.catatanRevisi && (
                                    <p className="text-xs text-red-600 font-medium mt-1">Catatan: {previewModalDoc.catatanRevisi}</p>
                                )}
                            </div>
                            <div className="flex items-center gap-2 flex-shrink-0">
                                {previewModalDoc.status !== 'Disetujui' && (
                                    <button
                                        type="button"
                                        onClick={() => setDeleteDocTarget({ id: previewModalDoc.id, title: previewModalDoc.title })}
                                        className="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 sm:px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition-colors shadow-xs"
                                    >
                                        <Trash2 size={13} />
                                        <span className="hidden sm:inline">Hapus Berkas</span>
                                    </button>
                                )}
                                <a
                                    href={`/dokumen/file/${previewModalDoc.id}`}
                                    download
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors shadow-xs"
                                >
                                    <Download size={13} />
                                    Unduh File
                                </a>
                                <button
                                    onClick={() => setPreviewModalDoc(null)}
                                    className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors"
                                >
                                    <X size={18} />
                                </button>
                            </div>
                        </div>

                        {/* Content Viewer */}
                        <div className="flex-1 overflow-auto bg-slate-100 p-2 sm:p-4 flex items-center justify-center min-h-[60vh]">
                            {previewModalDoc.filePath.toLowerCase().endsWith('.pdf') ? (
                                <iframe
                                    src={`/dokumen/file/${previewModalDoc.id}#toolbar=1`}
                                    className="w-full h-[72vh] rounded-lg border border-slate-200 bg-white shadow-inner"
                                    title={previewModalDoc.title}
                                />
                            ) : previewModalDoc.filePath.toLowerCase().endsWith('.docx') ? (
                                <div className="w-full h-full max-h-[75vh] overflow-auto">
                                    <DocxViewer
                                        url={`/dokumen/file/${previewModalDoc.id}`}
                                        downloadUrl={`/dokumen/file/${previewModalDoc.id}`}
                                    />
                                </div>
                            ) : previewModalDoc.filePath.match(/\.(jpg|jpeg|png|webp|gif)$/i) ? (
                                <div className="flex items-center justify-center w-full h-full py-2">
                                    <img
                                        src={`/dokumen/file/${previewModalDoc.id}`}
                                        alt={previewModalDoc.title}
                                        className="max-h-[72vh] max-w-full rounded-lg shadow-md object-contain border border-slate-200 bg-white"
                                    />
                                </div>
                            ) : (
                                <div className="text-center py-12 px-6 max-w-md mx-auto bg-white rounded-xl shadow-sm border border-slate-200">
                                    <FileText size={48} className="mx-auto text-slate-400 mb-3" />
                                    <h4 className="text-sm font-bold text-slate-800">Berkas Dokumen ({previewModalDoc.filePath.split('.').pop()?.toUpperCase()})</h4>
                                    <p className="text-xs text-slate-500 mt-1 mb-4">Format berkas ini dapat dibuka secara langsung menggunakan aplikasi di komputer/ponsel Anda.</p>
                                    <a
                                        href={`/dokumen/file/${previewModalDoc.id}`}
                                        className="inline-flex items-center gap-1.5 rounded-lg bg-[#1e2d5a] px-4 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors shadow-sm"
                                    >
                                        <Download size={14} />
                                        Unduh & Buka Berkas
                                    </a>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* Delete Confirmation Modal */}
            {deleteDocTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-xs">
                    <div className="w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-center justify-between border-b border-red-100 bg-red-50/70 px-6 py-4">
                            <div className="flex items-center gap-2.5 text-red-700">
                                <div className="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                                    <AlertTriangle size={17} />
                                </div>
                                <div>
                                    <h3 className="text-sm font-bold text-slate-900">Hapus Berkas Dokumen</h3>
                                    <p className="text-xs text-red-600 font-medium">Konfirmasi penghapusan</p>
                                </div>
                            </div>
                            <button
                                onClick={() => setDeleteDocTarget(null)}
                                disabled={isDeletingDoc}
                                className="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors"
                            >
                                <X size={18} />
                            </button>
                        </div>
                        <div className="px-6 py-5 space-y-3">
                            <p className="text-sm text-slate-700 leading-relaxed">
                                Apakah Anda yakin ingin menghapus berkas untuk <span className="font-bold text-slate-900">{deleteDocTarget.title}</span>?
                            </p>
                            <div className="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 space-y-1">
                                <p className="font-semibold flex items-center gap-1.5">
                                    <AlertCircle size={14} className="text-amber-600 shrink-0" />
                                    Perhatian:
                                </p>
                                <p className="text-amber-700 pl-5 leading-normal">
                                    Berkas fisik yang tersimpan di server akan dihapus permanen dan status berkas akan kembali menjadi <span className="font-semibold text-slate-700">Belum Diunggah</span>. Anda dapat mengunggah berkas baru setelahnya.
                                </p>
                            </div>
                            <div className="flex justify-end gap-2.5 pt-3">
                                <button
                                    type="button"
                                    onClick={() => setDeleteDocTarget(null)}
                                    disabled={isDeletingDoc}
                                    className="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="button"
                                    onClick={handleDeleteDoc}
                                    disabled={isDeletingDoc}
                                    className="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700 disabled:opacity-50 transition-colors shadow-sm inline-flex items-center gap-1.5"
                                >
                                    <Trash2 size={13} />
                                    {isDeletingDoc ? 'Menghapus...' : 'Ya, Hapus Berkas'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}