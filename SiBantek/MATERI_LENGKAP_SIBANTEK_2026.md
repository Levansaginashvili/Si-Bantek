# 📚 MATERI LENGKAP & BUKU PANDUAN SISTEM SI-BANTEK 2026
**Sistem Informasi Bantuan Peralatan Pembelajaran TIK SMP Tahun Anggaran 2026**

---

## 1. PENDAHULUAN & LATAR BELAKANG

Program Bantuan Peralatan Pembelajaran TIK SMP Tahun Anggaran 2026 merupakan program strategis Kementerian Pendidikan dalam mendorong percepatan digitalisasi pendidikan dan pelaksanaan Asesmen Nasional Berbasis Komputer (ANBK).

* **Alokasi Dana**: Setiap sekolah penerima mendapatkan dana bantuan sebesar **Rp 69.364.000,-**.
* **Sasaran Program**: **173 Sekolah Menengah Pertama (SMP)** penerima bantuan.
* **Tantangan Utama**: Mengelola dan memvalidasi 15 jenis dokumen administrasi juknis resmi secara seragam, akurat, tepat waktu, dan akuntabel.

---

## 2. CARA KERJA & TEKNOLOGI MESIN AUTO-FILL DOKUMEN (DOCX)

Otomatisasi dokumen adalah fitur inovasi inti pada SiBantek yang menghilangkan beban pengetikan manual berulang bagi operator sekolah.

```
┌─────────────────────────────────────────────────────────────┐
│                       DATABASE MYSQL                        │
│ Data Profil, Alamat RT/RW, Kepala Sekolah, Komite, Bank,   │
│ Rincian Belanja Laptop (RAB), Serial Number Laptop          │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│             DocxTemplateService.php (Laravel)               │
│ - PhpOffice\PhpWord\TemplateProcessor                       │
│ - Text Sanitization & Placeholder Mapper                     │
│ - Dynamic Table Row Cloning ($processor->cloneRow)          │
│ - Terbilang Helper (Kalkulasi & Kalimat Rupiah)             │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│          13 Master Dokumen Resmi di /SiBantek_doc/          │
│  (BAST.docx, RAB.docx, PaktaIntegritas.docx, SPTJM.docx,    │
│   PerjanjianKerjasama.docx, LaporanAkhir.docx, dll.)        │
└──────────────────────────────┬──────────────────────────────┘
                               │
            ┌──────────────────┴──────────────────┐
            ▼                                     ▼
┌───────────────────────────────┐   ┌───────────────────────────────┐
│     DOWNLOAD DRAF (.docx)     │   │      IN-BROWSER PREVIEW       │
│ Berkas Microsoft Word siap    │   │ Ditinjau langsung di web via  │
│ cetak dan tanda tangan/stempel│   │ DocxViewer (docx-preview JS)  │
└───────────────────────────────┘   └───────────────────────────────┘
```

### A. Komponen & Library yang Digunakan
1. **Backend Engine**: `PhpOffice\PhpWord\TemplateProcessor` (PHP 8.3 / Laravel 13).
2. **Frontend Viewer**: `docx-preview` / `mammoth.js` yang diintegrasikan ke dalam komponen React `DocxViewer.tsx` dan modal `DocumentDraftModal.tsx`.

### B. Alur Eksekusi Pembuatan Dokumen Otomatis:
1. **Pemetaan Placeholder Tunggal (Single Value Replacement)**:
   Sistem membaca parameter sekolah dan langsung mengisi placeholder di file Word:
   - **Data Legalitas Sekolah**: `${nama_sekolah}`, `${npsn}`, `${alamat_jalan}`, `${rt_rw}`, `${desa_kelurahan}`, `${kecamatan}`, `${kabupaten_kota}`, `${provinsi}`, `${kode_pos}`, `${telepon}`, `${email}`.
   - **Pejabat & Penanggung Jawab**: `${nama_kepsek}`, `${nip_kepsek}`, `${jabatan_kepsek}`, `${nama_komite}`, `${nama_bendahara}`, `${nip_bendahara}`.
   - **Rekening Penyaluran Bantuan**: `${nama_bank}`, `${no_rekening}`, `${nama_rekening}`.
   - **Penanggalan Indonesia**: `${tanggal_hari_ini_indonesia}`, `${tahun_anggaran}` (2026).
2. **Penggandaan Baris Tabel Belanja Dinamis (Dynamic Row Cloning)**:
   Pada dokumen **RAB** dan **BAST**, jumlah barang bervariasi. Sistem menggunakan `cloneRowAndSetValues('item_nama#1', $items)` untuk:
   - Membuat baris tabel belanja secara otomatis sesuai jumlah item barang yang disetujui.
   - Menghitung subtotal otomatis (`Qty * Harga Satuan`).
   - Menghitung total pagu bantuan dan menyisipkan kalimat **terbilang rupiah** resmi (contoh: *"Enam Puluh Sembilan Juta Tiga Ratus Enam Puluh Empat Ribu Rupiah"*).
3. **In-Browser Document Previewer**:
   Sekolah tidak perlu membuka aplikasi Microsoft Word hanya untuk mengecek draf; draf langsung tampil rapi dalam jendela pop-up di browser.

---

## 3. STRUKTUR HAK AKSES & RINCIAN FITUR LENGKAP PER ROLE

---

### 🏫 A. ROLE: SEKOLAH PENERIMA (173 SMP)
Akun khusus untuk Kepala Sekolah, Operator Sekolah, dan Tim Pelaksana Bantuan TIK.

#### Rincian Fitur Sekolah:
1. **Manajemen Profil & Legalitas Sekolah (Tahap 1)**:
   - Form data identitas sekolah (NPSN, Nama Sekolah, Status, Alamat lengkap dengan kolom spesifik: Jalan, RT/RW, Desa/Kelurahan, Kecamatan, Kabupaten/Kota, Provinsi, Kode Pos).
   - Form Data Tim Pelaksana: Kepala Sekolah (+ NIP), Ketua Komite Sekolah, Bendahara Bantuan (+ NIP).
   - Form Rekening Bank Bantuan: Nama Bank, Nomor Rekening, dan Nama Pemilik Rekening.
2. **Smart RAB Builder dengan Proteksi Pagu (Tahap 2)**:
   - Input rincian belanja peralatan TIK (Nama Alat/Laptop, Spesifikasi, Qty, Harga Satuan).
   - Kalkulasi otomatis Subtotal, Total Belanja, dan Sisa Dana bantuan.
   - Proteksi Pagu: Validasi ketat membatasi total anggaran maksimal Rp 69.364.000.
   - Kunci Pengajuan (Lock): RAB yang sudah diajukan akan dikunci untuk mencegah perubahan sepihak selama proses verifikasi.
3. **Pusat Unduh Draf Dokumen Juknis (Smart Downloader)**:
   - Unduh 13 template resmi juknis berformat Word (`.docx`) yang sudah terisi data sekolah secara otomatis.
   - Pratinjau draf dokumen langsung di browser sebelum dicetak.
4. **Manajemen Unggah Dokumen Bertanda Tangan (Tahap 3 – 7)**:
   - Unggah berkas fisik bertanda tangan & cap basah (PDF, DOCX, JPG, PNG hingga 10 MB).
   - **Fitur Hapus Draf Berkas**: Fleksibilitas bagi sekolah untuk menghapus atau mengganti berkas yang salah unggah selama belum disetujui Verifikator.
   - Notifikasi Catatan Revisi: Membaca catatan perbaikan spesifik dari Verifikator jika dokumen ditolak.
5. **Pencatatan Inventarisasi & Serial Number (Tahap 6)**:
   - Input Serial Number (SN) dan MAC Address untuk setiap unit laptop bantuan yang diterima.
   - Unggah foto dokumentasi fisik barang datang dan kondisi ruangan.
6. **Logika & Pelaporan Pengembalian Sisa Dana (Tahap 7)**:
   - **Kondisi Uang Pas (Sisa Rp 0)**: Dokumen Bukti Setor Sisa Dana otomatis dinyatakan *Tidak Diperlukan*, status kelengkapan cukup 14/14 Dokumen.
   - **Kondisi Ada Sisa Dana (> Rp 0)**: Peringatan setor sisa kas negara **hanya akan aktif ketika sekolah memasuki Tahap 7**. Sekolah mengunggah Bukti Penerimaan Negara (BPN) sebagai dokumen ke-15 untuk disahkan.

---

### 🔍 B. ROLE: VERIFIKATOR (PPK / TIM TEKNIS DINAS)
Akun khusus untuk Pejabat Pembuat Komitmen (PPK) dan tim verifikator dinas pendidikan.

#### Rincian Fitur Verifikator:
1. **Executive Dashboard Verifikasi**:
   - Ringkasan statistik 173 sekolah penerima (*Lengkap*, *Dalam Proses*, *Perlu Revisi*, *Belum Lapor*).
   - Filter sekolah berdasarkan Kecamatan, Status Kelengkapan, dan Tahapan Bantuan aktif (Tahap 1 - 7).
   - Indikator khusus **Sisa Dana & Status Pengembalian Kas Negara** (hanya memunculkan status belum setor jika sekolah sudah di Tahap 7).
2. **Modul Verifikasi Anggaran (RAB)**:
   - Menelaah kesesuaian harga satuan dan rincian belanja laptop yang diajukan sekolah.
   - Tindakan: **Setujui RAB** (membuka akses ke tahap berikutnya) atau **Tolak RAB** (dengan input catatan perbaikan).
3. **Workspace Verifikasi 15 Dokumen**:
   - Matriks verifikasi 15 berkas juknis per sekolah dalam satu layar terpadu.
   - Pratinjau file unggahan (PDF/DOCX/Gambar) langsung tanpa perlu download ke lokal komputer.
   - Aksi **Setujui (Approve)**: Mengubah status dokumen menjadi disetujui (hijau).
   - Aksi **Tolak (Reject)**: Menolak dokumen disertai catatan alasan yang wajib diperbaiki sekolah.
4. **Verifikasi Bukti Setor Kas Negara**:
   - Memvalidasi keabsahan Bukti Penerimaan Negara (BPN) dan Nomor Transaksi Penerimaan Negara (NTPN).
   - Menyetujui bukti setor untuk mengubah status sisa dana menjadi *Sudah Dikembalikan* (15/15 Dokumen Lengkap).

---

### ⚙️ C. ROLE: ADMINISTRATOR (ADMIN IT & MANAJEMEN PUSAT)
Akun pusat dengan hak akses menyeluruh untuk mengelola sistem dan rekapitulasi data.

#### Rincian Fitur Administrator:
1. **Pusat Monitoring Nasional**:
   - Grafik progres capaian penyaluran bantuan dan kelengkapan dokumen seluruh sekolah.
2. **Manajemen Pengguna (User Management)**:
   - Daftar seluruh akun (Sekolah, Verifikator, Admin).
   - Tambah akun baru, edit role pengguna, dan aktivasi/nonaktifkan akun.
   - **Fitur Reset Password Terstandarisasi**: Mereset password akun sekolah yang terkendala login ke password default.
3. **Ekspor Data & Rekapitulasi**:
   - Ekspor seluruh 173 daftar akun sekolah ke format **Excel (`.xls`)** dan **CSV**.
   - Ekspor rekapitulasi progres kelengkapan dokumen dan rekap sisa dana bantuan untuk laporan pimpinan/dinas.
4. **Generator Cetak Stiker Label Inventaris**:
   - Modul cetak stiker label inventaris aset barang negara lengkap dengan logo kementerian, nama sekolah, nomor inventaris, dan serial number siap tempel pada laptop bantuan.

---

## 4. ALUR KERJA 7 TAHAPAN SISTEM (LIFECYCLE)

```
[Tahap 1: Persiapan & Profil] ➡️ [Tahap 2: Pengajuan RAB] ➡️ [Tahap 3: Kontrak & PKS]
                                                                        ⬇️
[Tahap 6: BAST & Inventaris] ⬅️ [Tahap 5: Pelaksanaan & Salur] ⬅️ [Tahap 4: Survei & Pesan]
          ⬇️
[Tahap 7: LPJ & Sisa Dana (Final)]
```

* **Tahap 1 — Persiapan & Profil**: Pengisian data identitas, alamat RT/RW, Kepala Sekolah, Komite, Bendahara, dan rekening bank.
* **Tahap 2 — Perencanaan (RAB)**: Penyusunan rencana anggaran belanja laptop (maksimal Rp 69.364.000). Disahkan oleh Verifikator.
* **Tahap 3 — Kontrak & Komitmen**: Penandatanganan dan pengunggahan Pakta Integritas serta Surat Perjanjian Kerjasama (PKS).
* **Tahap 4 — Pemesanan & Survei Harga**: Pelaksanaan survei harga pasar dan penyusunan berkas perbandingan produk.
* **Tahap 5 — Pelaksanaan & Penyaluran**: Pelaporan awal pelaksanaan kegiatan dan konfirmasi penerimaan dana bantuan.
* **Tahap 6 — Serah Terima & Inventarisasi**: Pemeriksaan fisik barang, penandatanganan BAST, foto dokumentasi, dan input serial number laptop.
* **Tahap 7 — Pelaporan Akhir & LPJ (Final)**: Pengesahan Laporan Akhir, SPTJM, Laporan Penggunaan Dana, serta penyetoran sisa kas negara (BPN).

---

## 5. STANDAR KREDENSIAL AKUN (DEFAULT CREDENTIALS)

| Level Akun | Username / Login ID | Password Default |
| :--- | :--- | :--- |
| **Administrator** | `admin` | `Admin#1234` |
| **Verifikator (PPK)** | `197803152003121002` | `Verifikator#1234` |
| **173 Sekolah SMP** | `[NPSN Sekolah]` *(contoh: `20100001`)* | `Bantek@[4 digit terakhir NPSN]` *(contoh: `Bantek@0001`)* |

---

## 6. NILAI TAMBAH SISTEM (VALUE PROPOSITION)

1. **Efisiensi Waktu 80%**: Menghilangkan proses ketik ulang dokumen secara berulang.
2. **Kepatuhan Format Juknis 100%**: Berkas seragam dan sesuai standar kementerian.
3. **Akuntabilitas Keuangan Tinggi**: Perhitungan sisa dana riil dan bukti setor kas negara terpantau hingga tuntas.
4. **Audit Trail Lengkap**: Setiap tahapan memiliki rekam jejak digital yang jelas dan transparan.
