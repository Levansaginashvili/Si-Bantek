import React, { useRef } from 'react';
import { X, Printer, Download, FileText } from 'lucide-react';

interface Sekolah {
    npsn: string;
    nama_sekolah: string;
    provinsi: string;
    kabupaten: string;
    alamat: string;
    nama_kepsek: string;
    nip_kepsek: string;
    nama_bendahara: string;
    nip_bendahara: string;
    status_dana: string;
    rab: {
        merek_tipe_laptop: string;
        spesifikasi_ringkas: string;
        jumlah_unit: number;
        harga_satuan: number;
        total_harga: number;
        status: string;
    } | null;
}

interface Props {
    docKey: string;
    docLabel: string;
    sekolah: Sekolah;
    onClose: () => void;
}

const DOTS = '........................................';
const v = (val: string | undefined | null) => val || DOTS;

const today = () => {
    const d = new Date();
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
};

/* ═══════════════════════════════════════════════════════════
   SPTJM PREVIEW — matches the actual SPTJM.docx structure
   ═══════════════════════════════════════════════════════════ */
const PreviewSPTJM = ({ s }: { s: Sekolah }) => {
    const namaSekolahTanpaSmp = s.nama_sekolah.replace(/^SMP\s+/i, '');

    return (
        <div style={{ fontFamily: "'Times New Roman', serif", fontSize: '12pt', lineHeight: 1.6, color: '#000' }}>
            {/* KOP */}
            <div style={{ textAlign: 'center', borderBottom: '2px solid #000', paddingBottom: '8px', marginBottom: '16px' }}>
                <p style={{ fontSize: '10pt', margin: 0 }}>KOP {s.nama_sekolah.toUpperCase()}</p>
            </div>

            {/* Lampiran header */}
            <p style={{ fontSize: '10pt', margin: '0 0 4px', color: '#666' }}>
                Lampiran VI : Format Surat Pernyataan Tanggung Jawab Mutlak (SPTJM)
            </p>

            {/* Title */}
            <h2 style={{ textAlign: 'center', fontSize: '13pt', fontWeight: 'bold', margin: '24px 0 20px', textDecoration: 'underline' }}>
                SURAT PERNYATAAN TANGGUNG JAWAB MUTLAK
            </h2>

            {/* Identity */}
            <p style={{ margin: '0 0 4px' }}>Yang bertanda tangan di bawah ini, saya :</p>
            <table style={{ fontSize: '12pt', marginLeft: '24px', marginBottom: '12px' }}>
                <tbody>
                    <tr><td style={{ paddingRight: '16px', verticalAlign: 'top', whiteSpace: 'nowrap' }}>Nama</td><td>: {v(s.nama_kepsek)}</td></tr>
                    <tr><td style={{ paddingRight: '16px', verticalAlign: 'top', whiteSpace: 'nowrap' }}>NIP/NIK</td><td>: {v(s.nip_kepsek)}</td></tr>
                    <tr><td style={{ paddingRight: '16px', verticalAlign: 'top', whiteSpace: 'nowrap' }}>Jabatan</td><td>: Kepala Sekolah</td></tr>
                    <tr><td style={{ paddingRight: '16px', verticalAlign: 'top', whiteSpace: 'nowrap' }}>Alamat</td><td>: {v(s.alamat)}</td></tr>
                </tbody>
            </table>

            {/* Body */}
            <p style={{ margin: '0 0 8px' }}>bertindak atas nama jabatan, dengan ini menyatakan bahwa:</p>
            <ol style={{ margin: '0 0 16px', paddingLeft: '20px' }}>
                <li style={{ marginBottom: '8px' }}>Memiliki surat keputusan pengangkatan sebagai kepala satuan pendidikan dari pejabat yang berwenang.</li>
                <li style={{ marginBottom: '8px' }}>Sanggup memulai pekerjaan Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 selambat-lambatnya 14 (empat belas) hari kalender sejak dana masuk ke rekening sekolah.</li>
                <li style={{ marginBottom: '8px' }}>Sanggup untuk melaksanakan dan menyelesaikan pekerjaan Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 sesuai Perjanjian Kerja Sama (PKS).</li>
                <li style={{ marginBottom: '8px' }}>Sanggup memberikan laporan pertanggungjawaban akhir pelaksanaan pekerjaan Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 selambat-lambatnya 14 (empat belas) hari kerja setelah jangka waktu pelaksanaan pekerjaan selesai.</li>
                <li style={{ marginBottom: '8px' }}>Bersedia bertanggung jawab penuh atas semua pengeluaran dan pemanfaatan seluruh dana yang digunakan dalam rangka pelaksanaan Bantuan Pemerintah Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) mengacu Panduan Pelaksanaan dan Laporan Bantuan Pemerintah Program Pengadaan Sarana TIK Satuan Pendidikan Sekolah Menengah Pertama (SMP) Tahun Anggaran 2026 serta Perjanjian Kerja Sama (PKS).</li>
                <li style={{ marginBottom: '8px' }}>Apabila pernyataan ini tidak benar dan atau di kemudian hari saya melakukan tidak memenuhi pernyataan tersebut atau lalai, maka saya bersedia mempertanggungjawabkannya sesuai dengan ketentuan perundang-undangan.</li>
            </ol>

            {/* Signature */}
            <div style={{ marginTop: '24px' }}>
                <table style={{ marginLeft: 'auto', textAlign: 'center' }}>
                    <tbody>
                        <tr><td>Dibuat di : {v(s.kabupaten)}</td></tr>
                        <tr><td>Tanggal: {today()}</td></tr>
                        <tr><td style={{ paddingTop: '8px' }}>Kepala Sekolah Menengah Pertama {namaSekolahTanpaSmp}</td></tr>
                        <tr><td style={{ paddingTop: '8px' }}>
                            <div style={{ border: '1px solid #999', display: 'inline-block', padding: '4px 12px', fontSize: '10pt', color: '#666' }}>
                                Materai<br />Rp 10.000,00
                            </div>
                        </td></tr>
                        <tr><td style={{ paddingTop: '48px' }}>
                            <span style={{ fontWeight: 'bold', textDecoration: 'underline' }}>{v(s.nama_kepsek)}</span>
                        </td></tr>
                        <tr><td>NIP. {v(s.nip_kepsek)}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    );
};

/* ═══════════════════════════════════════════════════════════
   GENERIC PLACEHOLDER — for documents not yet templated
   ═══════════════════════════════════════════════════════════ */
const PreviewPlaceholder = ({ docLabel }: { docLabel: string }) => (
    <div className="text-center py-12">
        <FileText size={48} className="text-slate-300 mx-auto mb-4" />
        <p className="text-sm text-slate-600 font-semibold mb-1">{docLabel}</p>
        <p className="text-xs text-slate-400">Preview belum tersedia untuk dokumen ini.</p>
        <p className="text-xs text-slate-400 mt-1">Gunakan tombol "Unduh DOCX" untuk mengunduh dokumen yang sudah auto-fill.</p>
    </div>
);

/* ═══════════════════════════════════════════════════════════
   MAIN MODAL
   ═══════════════════════════════════════════════════════════ */
export default function DocumentDraftModal({ docKey, docLabel, sekolah, onClose }: Props) {
    const printRef = useRef<HTMLDivElement>(null);

    const hasPreview = docKey === 'sptjm'; // tambah docKey lain nanti satu per satu

    const handlePrint = () => {
        const content = printRef.current;
        if (!content) return;

        const printWindow = window.open('', '_blank');
        if (!printWindow) return;

        printWindow.document.write(`<!DOCTYPE html><html><head>
            <title>${docLabel} — ${sekolah.nama_sekolah}</title>
            <style>
                @page { size: A4; margin: 2cm; }
                body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; margin: 0; padding: 2cm; }
                table { border-collapse: collapse; }
                th, td { vertical-align: top; }
                ol { padding-left: 20px; }
                li { margin-bottom: 8px; }
            </style>
        </head><body>${content.innerHTML}</body></html>`);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => { printWindow.print(); printWindow.close(); }, 500);
    };

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center bg-black/50 overflow-y-auto p-4">
            <div className="w-full max-w-3xl bg-white rounded-xl shadow-2xl my-4">
                {/* Header */}
                <div className="flex items-center justify-between border-b border-slate-200 px-6 py-4 sticky top-0 bg-white rounded-t-xl z-10">
                    <div>
                        <h3 className="text-sm font-bold text-slate-800">{docLabel}</h3>
                        <p className="text-[11px] text-slate-500 mt-0.5">
                            {hasPreview ? 'Preview dokumen — data terisi otomatis dari Profile' : 'Unduh dokumen auto-fill'}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <a
                            href={`/sekolah/dokumen/download/${docKey}`}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                        >
                            <Download size={13} />
                            Unduh DOCX
                        </a>
                        {hasPreview && (
                            <button
                                onClick={handlePrint}
                                className="inline-flex items-center gap-1.5 rounded-lg bg-[#1e2d5a] px-3 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors"
                            >
                                <Printer size={13} />
                                Cetak
                            </button>
                        )}
                        <button onClick={onClose} className="text-slate-400 hover:text-slate-600 transition-colors ml-1">
                            <X size={18} />
                        </button>
                    </div>
                </div>

                {/* Content */}
                <div className="px-8 py-6">
                    {hasPreview ? (
                        <div ref={printRef} className="bg-white border border-slate-200 rounded-lg p-8 shadow-inner" style={{ minHeight: '500px' }}>
                            {docKey === 'sptjm' && <PreviewSPTJM s={sekolah} />}
                        </div>
                    ) : (
                        <PreviewPlaceholder docLabel={docLabel} />
                    )}
                </div>
            </div>
        </div>
    );
}
