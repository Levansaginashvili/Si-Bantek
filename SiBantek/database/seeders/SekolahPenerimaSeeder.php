<?php

namespace Database\Seeders;

use App\Models\Dokumen;
use App\Models\Rab;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SekolahPenerimaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Daftar 173 Sekolah Penerima Bantuan Peralatan TIK SMP Tahun 2026
     * Berdasarkan Lampiran XIV SK PPK Direktorat SMP Kemendikdasmen.
     */
    public function run(): void
    {
        $schools = [
            ['no' => 1, 'npsn' => '10110698', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Barat', 'nama_sekolah' => 'SMP NEGERI 3 WOYLA TIMUR'],
            ['no' => 2, 'npsn' => '69945399', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Selatan', 'nama_sekolah' => 'SMP NEGERI 3 KLUET TENGAH'],
            ['no' => 3, 'npsn' => '10102697', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Selatan', 'nama_sekolah' => 'SMP.NEGERI 1 KLUET TENGAH'],
            ['no' => 4, 'npsn' => '10111445', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Singkil', 'nama_sekolah' => 'UPTD SPF SMP Negeri 2 Danau Paris'],
            ['no' => 5, 'npsn' => '10107510', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Tengah', 'nama_sekolah' => 'SMPN 26 TAKENGON'],
            ['no' => 6, 'npsn' => '10101926', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Timur', 'nama_sekolah' => 'SMPN 1 LOKOP'],
            ['no' => 7, 'npsn' => '69966397', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Utara', 'nama_sekolah' => 'SMP PPTQ ACEH UTARA'],
            ['no' => 8, 'npsn' => '70008040', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Utara', 'nama_sekolah' => 'SMP TASTAFI TERPADU YAYASAN DAYAH NURUL HUDA'],
            ['no' => 9, 'npsn' => '70035644', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Aceh Utara', 'nama_sekolah' => 'SMPS IT INSAN MUDA'],
            ['no' => 10, 'npsn' => '10111318', 'provinsi' => 'Prov. Aceh', 'kabupaten' => 'Kab. Pidie', 'nama_sekolah' => 'SMPN 5 TANGSE'],
            ['no' => 11, 'npsn' => '69754630', 'provinsi' => 'Prov. Gorontalo', 'kabupaten' => 'Kab. Boalemo', 'nama_sekolah' => 'SMP NEGERI 11 SATU ATAP WONOSARI'],
            ['no' => 12, 'npsn' => '10506847', 'provinsi' => 'Prov. Jambi', 'kabupaten' => 'Kab. Sarolangun', 'nama_sekolah' => 'SMP NEGERI 21 SAROLANGUN'],
            ['no' => 13, 'npsn' => '10506878', 'provinsi' => 'Prov. Jambi', 'kabupaten' => 'Kab. Sarolangun', 'nama_sekolah' => 'SMP NEGERI SATU ATAP 19 SAROLANGUN'],
            ['no' => 14, 'npsn' => '10505943', 'provinsi' => 'Prov. Jambi', 'kabupaten' => 'Kab. Tanjung Jabung Barat', 'nama_sekolah' => 'SMP Negeri 43 Tanjung Jabung Barat'],
            ['no' => 15, 'npsn' => '20217055', 'provinsi' => 'Prov. Jawa Barat', 'kabupaten' => 'Kab. Subang', 'nama_sekolah' => 'SMP NEGERI 1 CIASEM'],
            ['no' => 16, 'npsn' => '30108572', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Bengkayang', 'nama_sekolah' => 'SMP NEGERI 2 LEMBAH BAWANG'],
            ['no' => 17, 'npsn' => '30107424', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Kubu Raya', 'nama_sekolah' => 'SMP NEGERI 3 TERENTANG'],
            ['no' => 18, 'npsn' => '69762679', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Kubu Raya', 'nama_sekolah' => 'SMP NEGERI 8 SATAP BATU AMPAR'],
            ['no' => 19, 'npsn' => '30107402', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Kubu Raya', 'nama_sekolah' => 'SMP SANTA URSULA'],
            ['no' => 20, 'npsn' => '30109783', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Landak', 'nama_sekolah' => 'SMP NEGERI 4 MENYUKE'],
            ['no' => 21, 'npsn' => '30108099', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Melawi', 'nama_sekolah' => 'SMP NEGERI 5 SAYAN'],
            ['no' => 22, 'npsn' => '30109691', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Melawi', 'nama_sekolah' => 'SMP NEGERI 5 TANAH PINOH BARAT'],
            ['no' => 23, 'npsn' => '60724676', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Melawi', 'nama_sekolah' => 'SMP NEGERI 8 SATAP MENUKUNG'],
            ['no' => 24, 'npsn' => '69831637', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Mempawah', 'nama_sekolah' => 'SMP NEGERI 4 SADANIANG'],
            ['no' => 25, 'npsn' => '30112525', 'provinsi' => 'Prov. Kalimantan Barat', 'kabupaten' => 'Kab. Sanggau', 'nama_sekolah' => 'SMP NEGERI 03 SATAP NOYAN'],
            ['no' => 26, 'npsn' => '30311979', 'provinsi' => 'Prov. Kalimantan Selatan', 'kabupaten' => 'Kab. Tabalong', 'nama_sekolah' => 'SMP NEGERI 9 MUARA UYA'],
            ['no' => 27, 'npsn' => '69949257', 'provinsi' => 'Prov. Kalimantan Tengah', 'kabupaten' => 'Kab. Barito Utara', 'nama_sekolah' => 'SMP NEGERI 1 TEWEH BARU'],
            ['no' => 28, 'npsn' => '30204170', 'provinsi' => 'Prov. Kalimantan Tengah', 'kabupaten' => 'Kab. Katingan', 'nama_sekolah' => 'SMP NEGERI 6 KATINGAN TENGAH'],
            ['no' => 29, 'npsn' => '30204274', 'provinsi' => 'Prov. Kalimantan Tengah', 'kabupaten' => 'Kab. Kotawaringin Timur', 'nama_sekolah' => 'SMP NEGERI SATU ATAP 1 CEMPAGA HULU'],
            ['no' => 30, 'npsn' => '30406120', 'provinsi' => 'Prov. Kalimantan Timur', 'kabupaten' => 'Kab. Paser', 'nama_sekolah' => 'SMP NEGERI 4 LONG KALI'],
            ['no' => 31, 'npsn' => '69864683', 'provinsi' => 'Prov. Kalimantan Timur', 'kabupaten' => 'Kab. Paser', 'nama_sekolah' => 'SMP NEGERI 6 TANJUNG HARAPAN'],
            ['no' => 32, 'npsn' => '11002810', 'provinsi' => 'Prov. Kepulauan Riau', 'kabupaten' => 'Kab. Lingga', 'nama_sekolah' => 'SMP NEGERI 1 KEPULAUAN POSEK'],
            ['no' => 33, 'npsn' => '10810844', 'provinsi' => 'Prov. Lampung', 'kabupaten' => 'Kab. Pesisir Barat', 'nama_sekolah' => 'SMP NEGERI 26 KRUI'],
            ['no' => 34, 'npsn' => '10810846', 'provinsi' => 'Prov. Lampung', 'kabupaten' => 'Kab. Pesisir Barat', 'nama_sekolah' => 'SMP NEGERI SATU ATAP 2 KRUI'],
            ['no' => 35, 'npsn' => '10811028', 'provinsi' => 'Prov. Lampung', 'kabupaten' => 'Kab. Tanggamus', 'nama_sekolah' => 'SMP NEGERI SATU ATAP PEMATANG SAWA'],
            ['no' => 36, 'npsn' => '69980311', 'provinsi' => 'Prov. Lampung', 'kabupaten' => 'Kab. Tanggamus', 'nama_sekolah' => 'SMP SATU ATAP 2 CUKUH BALAK'],
            ['no' => 37, 'npsn' => '69787356', 'provinsi' => 'Prov. Lampung', 'kabupaten' => 'Kab. Tulang Bawang', 'nama_sekolah' => 'SMP NEGERI SATU ATAP 2 DENTE TELADAS'],
            ['no' => 38, 'npsn' => '60101819', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Kepulauan Aru', 'nama_sekolah' => 'SMP KRISTEN 2 LONGGAR APARA'],
            ['no' => 39, 'npsn' => '60101825', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Kepulauan Aru', 'nama_sekolah' => 'SMP NASKAT KABALSIANG BENJURING'],
            ['no' => 40, 'npsn' => '60101360', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Kepulauan Tanimbar', 'nama_sekolah' => 'SMP NEGERI 2 TANIMBAR UTARA'],
            ['no' => 41, 'npsn' => '69787151', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Kepulauan Tanimbar', 'nama_sekolah' => 'SMP NEGERI 2 WERMAKTIAN'],
            ['no' => 42, 'npsn' => '70003800', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Kepulauan Tanimbar', 'nama_sekolah' => 'SMP NEGERI 3 WERMAKTIAN'],
            ['no' => 43, 'npsn' => '60103189', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Maluku Barat Daya', 'nama_sekolah' => 'SMP NEGERI 6 AHANARI'],
            ['no' => 44, 'npsn' => '60102511', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Seram Bagian Timur', 'nama_sekolah' => 'SMP NEGERI 21 SERAM BAGIAN TIMUR'],
            ['no' => 45, 'npsn' => '60101656', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Seram Bagian Timur', 'nama_sekolah' => 'SMP NEGERI 3 SERAM BAGIAN TIMUR'],
            ['no' => 46, 'npsn' => '60103747', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Seram Bagian Timur', 'nama_sekolah' => 'SMP NEGERI 41 SERAM BAGIAN TIMUR'],
            ['no' => 47, 'npsn' => '60102953', 'provinsi' => 'Prov. Maluku', 'kabupaten' => 'Kab. Seram Bagian Timur', 'nama_sekolah' => 'SMP PGRI KILGA'],
            ['no' => 48, 'npsn' => '60201615', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Halmahera Selatan', 'nama_sekolah' => 'SMP NEGERI 34 HALMAHERA SELATAN'],
            ['no' => 49, 'npsn' => '70034146', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Halmahera Utara', 'nama_sekolah' => 'SMP KRISTEN GEORGE RORIWO'],
            ['no' => 50, 'npsn' => '60202503', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Kepulauan Sula', 'nama_sekolah' => 'SMP NEGERI 1 MANGOLI TENGAH'],
            ['no' => 51, 'npsn' => '60202279', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Kepulauan Sula', 'nama_sekolah' => 'SMP NEGERI 1 SANANA UTARA'],
            ['no' => 52, 'npsn' => '60200521', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Kepulauan Sula', 'nama_sekolah' => 'SMP NEGERI 1 SULABESI BARAT'],
            ['no' => 53, 'npsn' => '60202277', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Kepulauan Sula', 'nama_sekolah' => 'SMP NEGERI 1 SULABESI SELATAN'],
            ['no' => 54, 'npsn' => '60202559', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Kepulauan Sula', 'nama_sekolah' => 'SMP NEGERI 3 SATU ATAP MANGOLI BARAT'],
            ['no' => 55, 'npsn' => '60202560', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Kepulauan Sula', 'nama_sekolah' => 'SMP NEGERI 3 SATU ATAP MANGOLI UTARA'],
            ['no' => 56, 'npsn' => '69787402', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Kepulauan Sula', 'nama_sekolah' => 'SMP NEGERI 4 SANANA UTARA'],
            ['no' => 57, 'npsn' => '70032463', 'provinsi' => 'Prov. Maluku Utara', 'kabupaten' => 'Kab. Pulau Taliabu', 'nama_sekolah' => 'SMP NEGERI 9 TALIABU UTARA'],
            ['no' => 58, 'npsn' => '70049624', 'provinsi' => 'Prov. Nusa Tenggara Barat', 'kabupaten' => 'Kab. Lombok Utara', 'nama_sekolah' => 'SMP ISLAM AL-JUNARIS BAYANUL HAQ'],
            ['no' => 59, 'npsn' => '50219430', 'provinsi' => 'Prov. Nusa Tenggara Barat', 'kabupaten' => 'Kab. Lombok Utara', 'nama_sekolah' => 'SMPN SATAP 1 KAYANGAN'],
            ['no' => 60, 'npsn' => '50300212', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Kupang', 'nama_sekolah' => 'UPTD SMP NEGERI 1 AMFOANG UTARA KEC. AMFOANG UTARA'],
            ['no' => 61, 'npsn' => '69989069', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Kupang', 'nama_sekolah' => 'UPTD SMP NEGERI 5 AMARASI TIMUR KEC. AMARASI TIMUR'],
            ['no' => 62, 'npsn' => '50309366', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Malaka', 'nama_sekolah' => 'SMP NEGERI METAMAUK'],
            ['no' => 63, 'npsn' => '69899281', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Malaka', 'nama_sekolah' => 'SMP Negeri Wederok'],
            ['no' => 64, 'npsn' => '70051720', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Malaka', 'nama_sekolah' => 'SMP PLUS ST JHON FISHER WEKBELAR'],
            ['no' => 65, 'npsn' => '50308611', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Manggarai Timur', 'nama_sekolah' => 'SMP NEGERI 6 BORONG'],
            ['no' => 66, 'npsn' => '69727394', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Sabu Raijua', 'nama_sekolah' => 'UPTD SMP NEGERI 1 SABU LIAE'],
            ['no' => 67, 'npsn' => '50308043', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Sumba Barat', 'nama_sekolah' => 'SMP NEGERI 3 LAMBOYA'],
            ['no' => 68, 'npsn' => '69727622', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Sumba Barat Daya', 'nama_sekolah' => 'SMP NEGERI 1 KODI BALAGHAR'],
            ['no' => 69, 'npsn' => '50304045', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Sumba Barat Daya', 'nama_sekolah' => 'SMP NEGERI 1 WEWEWA TIMUR'],
            ['no' => 70, 'npsn' => '50304059', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Sumba Barat Daya', 'nama_sekolah' => 'SMP NEGERI 2 KODI'],
            ['no' => 71, 'npsn' => '69727785', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Sumba Barat Daya', 'nama_sekolah' => 'SMP NEGERI 4 KODI'],
            ['no' => 72, 'npsn' => '50304049', 'provinsi' => 'Prov. Nusa Tenggara Timur', 'kabupaten' => 'Kab. Sumba Barat Daya', 'nama_sekolah' => 'SMPK WONA KAKA'],
            ['no' => 73, 'npsn' => '60300337', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Biak Numfor', 'nama_sekolah' => 'SMP NEGERI 1 YENDIDORI'],
            ['no' => 74, 'npsn' => '60301712', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Biak Numfor', 'nama_sekolah' => 'SMP NEGERI 4 BIAK BARAT'],
            ['no' => 75, 'npsn' => '60303097', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Keerom', 'nama_sekolah' => 'SMP NEGERI 1 ARSO'],
            ['no' => 76, 'npsn' => '60303511', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Keerom', 'nama_sekolah' => 'SMP NEGERI 5 ARSO'],
            ['no' => 77, 'npsn' => '60303099', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Keerom', 'nama_sekolah' => 'SMP NEGERI 6 ARSO'],
            ['no' => 78, 'npsn' => '69725911', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Keerom', 'nama_sekolah' => 'SMP NEGERI 7 YETTI'],
            ['no' => 79, 'npsn' => '69964912', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Keerom', 'nama_sekolah' => 'SMP SATU ATAP BOMPAI'],
            ['no' => 80, 'npsn' => '70037364', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Keerom', 'nama_sekolah' => 'SMP YPK BETLEHEM'],
            ['no' => 81, 'npsn' => '69767772', 'provinsi' => 'Prov. Papua', 'kabupaten' => 'Kab. Waropen', 'nama_sekolah' => 'SMP YPK ALFA OMEGA UREIFAISEI'],
            ['no' => 82, 'npsn' => '60403622', 'provinsi' => 'Prov. Papua Barat', 'kabupaten' => 'Kab. Manokwari Selatan', 'nama_sekolah' => 'SMP NEGERI 24 DATARAN ISIM'],
            ['no' => 83, 'npsn' => '69889080', 'provinsi' => 'Prov. Papua Barat', 'kabupaten' => 'Kab. Manokwari Selatan', 'nama_sekolah' => 'SMP SATAP MAWI'],
            ['no' => 84, 'npsn' => '60403872', 'provinsi' => 'Prov. Papua Barat', 'kabupaten' => 'Kab. Pegunungan Arfak', 'nama_sekolah' => 'SMP NEGERI 05 CATUBOUW'],
            ['no' => 85, 'npsn' => '60401946', 'provinsi' => 'Prov. Papua Barat', 'kabupaten' => 'Kab. Teluk Bintuni', 'nama_sekolah' => 'SMPN SIBENA'],
            ['no' => 86, 'npsn' => '70035490', 'provinsi' => 'Prov. Papua Barat Daya', 'kabupaten' => 'Kab. Sorong', 'nama_sekolah' => 'SMP Negeri 31 Kabupaten Sorong'],
            ['no' => 87, 'npsn' => '60301553', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP KRISTEN BALIEM TERPADU'],
            ['no' => 88, 'npsn' => '69896511', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP KRISTEN BIJI SESAWI WAMENA'],
            ['no' => 89, 'npsn' => '60301554', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP KRISTEN WAMENA'],
            ['no' => 90, 'npsn' => '60300791', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP NEGERI 1 ASOLOGAIMA'],
            ['no' => 91, 'npsn' => '60301557', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP NEGERI I KURULU'],
            ['no' => 92, 'npsn' => '60303892', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP NEGERI TAGIME'],
            ['no' => 93, 'npsn' => '69980270', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP YAPESLI WAMENA'],
            ['no' => 94, 'npsn' => '60302960', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP YASORES WAMENA'],
            ['no' => 95, 'npsn' => '60301587', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Jayawijaya', 'nama_sekolah' => 'SMP YPPGI ANIGOU WAMENA'],
            ['no' => 96, 'npsn' => '60303329', 'provinsi' => 'Prov. Papua Pegunungan', 'kabupaten' => 'Kab. Yahukimo', 'nama_sekolah' => 'SMP NINIA'],
            ['no' => 97, 'npsn' => '70034555', 'provinsi' => 'Prov. Papua Selatan', 'kabupaten' => 'Kab. Boven Digoel', 'nama_sekolah' => 'SMP ANUGERAH TERINDAH'],
            ['no' => 98, 'npsn' => '69980064', 'provinsi' => 'Prov. Papua Selatan', 'kabupaten' => 'Kab. Boven Digoel', 'nama_sekolah' => 'SMP Negeri 2 Krounjendit Mindiptana'],
            ['no' => 99, 'npsn' => '60303792', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Dogiyai', 'nama_sekolah' => 'SMP NEGERI 1 DOGIYAI'],
            ['no' => 100, 'npsn' => '60304164', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Dogiyai', 'nama_sekolah' => 'SMP NEGERI 1 KAMU TIMUR'],
            ['no' => 101, 'npsn' => '60300804', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Dogiyai', 'nama_sekolah' => 'SMP YPPGI GOLGUTA IKRAR'],
            ['no' => 102, 'npsn' => '60302872', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Mimika', 'nama_sekolah' => 'SMP NEGERI 6 MIMIKA'],
            ['no' => 103, 'npsn' => '69859734', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Mimika', 'nama_sekolah' => 'SMP NEGERI 9 MIMIKA'],
            ['no' => 104, 'npsn' => '70013168', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Paniai', 'nama_sekolah' => 'SMP YAPIS AL-IJTIHAAD MADI'],
            ['no' => 105, 'npsn' => '70013167', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Paniai', 'nama_sekolah' => 'SMP YPPGI KEPAS KOPO'],
            ['no' => 106, 'npsn' => '60302447', 'provinsi' => 'Prov. Papua Tengah', 'kabupaten' => 'Kab. Paniai', 'nama_sekolah' => 'SMP YPPK EPOUTO'],
            ['no' => 107, 'npsn' => '10494439', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Indragiri Hilir', 'nama_sekolah' => 'SMPN SATU ATAP BELANTARAYA'],
            ['no' => 108, 'npsn' => '69855408', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Kampar', 'nama_sekolah' => 'UPT SMP NEGERI SATU ATAP BALUNG'],
            ['no' => 109, 'npsn' => '10495302', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Kepulauan Meranti', 'nama_sekolah' => 'SMP NEGERI 3 PULAU MERBAU'],
            ['no' => 110, 'npsn' => '69939832', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Kepulauan Meranti', 'nama_sekolah' => 'SMP NEGERI 4 MERBAU'],
            ['no' => 111, 'npsn' => '69815329', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Kepulauan Meranti', 'nama_sekolah' => 'SMP NEGERI 5 PULAU MERBAU'],
            ['no' => 112, 'npsn' => '10495432', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Kepulauan Meranti', 'nama_sekolah' => 'SMP NEGERI 6 RANGSANG'],
            ['no' => 113, 'npsn' => '10495613', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Rokan Hulu', 'nama_sekolah' => 'SMP NEGERI 6 ROKAN IV KOTO'],
            ['no' => 114, 'npsn' => '69988384', 'provinsi' => 'Prov. Riau', 'kabupaten' => 'Kab. Rokan Hulu', 'nama_sekolah' => 'SMP SWASTA PUTRI SION YUSMARSAH'],
            ['no' => 115, 'npsn' => '40604265', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamasa', 'nama_sekolah' => 'SMP NEGERI 2 SATAP MESSAWA'],
            ['no' => 116, 'npsn' => '69908989', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamasa', 'nama_sekolah' => 'SMP NEGERI 3 PANA'],
            ['no' => 117, 'npsn' => '40605711', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamasa', 'nama_sekolah' => 'SMP SWASTA BERBUDI KOPIAN'],
            ['no' => 118, 'npsn' => '40602674', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamasa', 'nama_sekolah' => 'SMP TUNAS HARAPAN BURANA'],
            ['no' => 119, 'npsn' => '69978925', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamasa', 'nama_sekolah' => 'SMPS CAHAYA CLARA'],
            ['no' => 120, 'npsn' => '40604546', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamuju', 'nama_sekolah' => 'SMP NEGERI 2 KALUMPANG'],
            ['no' => 121, 'npsn' => '40604823', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamuju', 'nama_sekolah' => 'SMPN 3 Kalumpang'],
            ['no' => 122, 'npsn' => '69973024', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Mamuju Tengah', 'nama_sekolah' => 'UPTD SMP NEGERI SATU ATAP SALULEBO'],
            ['no' => 123, 'npsn' => '40605719', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Polewali Mandar', 'nama_sekolah' => 'SMP NEGERI SATAP DAALA TIMUR'],
            ['no' => 124, 'npsn' => '69788001', 'provinsi' => 'Prov. Sulawesi Barat', 'kabupaten' => 'Kab. Polewali Mandar', 'nama_sekolah' => 'SMP NEGERI SATU ATAP PUPPURING'],
            ['no' => 125, 'npsn' => '40309285', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Luwu', 'nama_sekolah' => 'SMP NEGERI SATAP KAILI'],
            ['no' => 126, 'npsn' => '40309280', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Luwu', 'nama_sekolah' => 'SMP PESANTREN SINERGI MULYA'],
            ['no' => 127, 'npsn' => '40314258', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Luwu', 'nama_sekolah' => 'SMPN 1 ULUSALU'],
            ['no' => 128, 'npsn' => '40318173', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Luwu', 'nama_sekolah' => 'SMPN SATAP DAMPAN'],
            ['no' => 129, 'npsn' => '69886182', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Pangkajene Dan Kepulauan', 'nama_sekolah' => 'UPT SMPN 13 SATAP LIUKANG TANGAYA'],
            ['no' => 130, 'npsn' => '40309799', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Tana Toraja', 'nama_sekolah' => 'UPT SMPN 1 SIMBUANG'],
            ['no' => 131, 'npsn' => '40316759', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Tana Toraja', 'nama_sekolah' => 'UPT SMPN 3 BONGGAKARADENG'],
            ['no' => 132, 'npsn' => '40309766', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Tana Toraja', 'nama_sekolah' => 'UPT SMPN SATAP 2 SIMBUANG'],
            ['no' => 133, 'npsn' => '40318116', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Tana Toraja', 'nama_sekolah' => 'UPT SMPN SATAP 3 MAPPAK'],
            ['no' => 134, 'npsn' => '40318439', 'provinsi' => 'Prov. Sulawesi Selatan', 'kabupaten' => 'Kab. Toraja Utara', 'nama_sekolah' => 'SMP NEGERI 3 BARUPPU SATAP'],
            ['no' => 135, 'npsn' => '69770321', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMP LPPKD Tolongano'],
            ['no' => 136, 'npsn' => '40200663', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMP NEGERI 2 SOJOL'],
            ['no' => 137, 'npsn' => '69765063', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMP NEGERI 3 LABUAN'],
            ['no' => 138, 'npsn' => '40200637', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMP NEGERI 4 DAMPELAS'],
            ['no' => 139, 'npsn' => '69762722', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMP NEGERI 8 Satap Mbuwu Banawa Selatan'],
            ['no' => 140, 'npsn' => '69787776', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMP NEGERI SATAP 4 SINDUE TOBATA'],
            ['no' => 141, 'npsn' => '69896187', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMPN 7 Balaesang Tanjung'],
            ['no' => 142, 'npsn' => '69786467', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMPN SATAP 5 BALAESANG'],
            ['no' => 143, 'npsn' => '40206269', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Donggala', 'nama_sekolah' => 'SMPN SATAP 7 DAMPELAS'],
            ['no' => 144, 'npsn' => '40200669', 'provinsi' => 'Prov. Sulawesi Tengah', 'kabupaten' => 'Kab. Sigi', 'nama_sekolah' => 'SMP NEGERI 14 SIGI'],
            ['no' => 145, 'npsn' => '69760832', 'provinsi' => 'Prov. Sulawesi Tenggara', 'kabupaten' => 'Kab. Bombana', 'nama_sekolah' => 'SMP NEGERI 25 POLEANG BARAT'],
            ['no' => 146, 'npsn' => '40404877', 'provinsi' => 'Prov. Sulawesi Tenggara', 'kabupaten' => 'Kab. Buton Utara', 'nama_sekolah' => 'SMP NEGERI 3 KULISUSU UTARA'],
            ['no' => 147, 'npsn' => '40403724', 'provinsi' => 'Prov. Sulawesi Tenggara', 'kabupaten' => 'Kab. Kolaka', 'nama_sekolah' => 'SMP NEGERI 5 WATUBANGGA'],
            ['no' => 148, 'npsn' => '69953300', 'provinsi' => 'Prov. Sulawesi Tenggara', 'kabupaten' => 'Kab. Kolaka', 'nama_sekolah' => 'SMPN SATAP 1 TANGGETADA'],
            ['no' => 149, 'npsn' => '69820628', 'provinsi' => 'Prov. Sulawesi Tenggara', 'kabupaten' => 'Kab. Kolaka Timur', 'nama_sekolah' => 'SMP NEGERI SATAP 2 AERE'],
            ['no' => 150, 'npsn' => '40405253', 'provinsi' => 'Prov. Sulawesi Tenggara', 'kabupaten' => 'Kab. Kolaka Utara', 'nama_sekolah' => 'SMP Negeri Satu Atap 9 Kolaka Utara'],
            ['no' => 151, 'npsn' => '69961531', 'provinsi' => 'Prov. Sumatera Barat', 'kabupaten' => 'Kab. Kepulauan Mentawai', 'nama_sekolah' => 'SMP KRISTEN CAHAYA BANGSA TUAPEJAT'],
            ['no' => 152, 'npsn' => '10310814', 'provinsi' => 'Prov. Sumatera Barat', 'kabupaten' => 'Kab. Kepulauan Mentawai', 'nama_sekolah' => 'SMP LENTERA HARAPAN SIBERUT SELATAN'],
            ['no' => 153, 'npsn' => '70013741', 'provinsi' => 'Prov. Sumatera Barat', 'kabupaten' => 'Kab. Kepulauan Mentawai', 'nama_sekolah' => 'SMP NEGERI 3 SIBERUT BARAT DAYA'],
            ['no' => 154, 'npsn' => '10307826', 'provinsi' => 'Prov. Sumatera Barat', 'kabupaten' => 'Kab. Solok', 'nama_sekolah' => 'SMP N 4 HILIRAN GUMANTI'],
            ['no' => 155, 'npsn' => '10306959', 'provinsi' => 'Prov. Sumatera Barat', 'kabupaten' => 'Kab. Solok Selatan', 'nama_sekolah' => 'SMP NEGERI 18 SOLOK SELATAN'],
            ['no' => 156, 'npsn' => '70013289', 'provinsi' => 'Prov. Sumatera Selatan', 'kabupaten' => 'Kab. Musi Banyuasin', 'nama_sekolah' => 'SMP NEGERI 14 BAYUNG LENCIR'],
            ['no' => 157, 'npsn' => '10646488', 'provinsi' => 'Prov. Sumatera Selatan', 'kabupaten' => 'Kab. Musi Rawas Utara', 'nama_sekolah' => 'SMP NEGERI PULAU LEBAR'],
            ['no' => 158, 'npsn' => '70001923', 'provinsi' => 'Prov. Sumatera Selatan', 'kabupaten' => 'Kab. Musi Rawas Utara', 'nama_sekolah' => 'SMP NEGERI SUNGAI LANANG'],
            ['no' => 159, 'npsn' => '10645855', 'provinsi' => 'Prov. Sumatera Selatan', 'kabupaten' => 'Kab. Ogan Komering Ilir', 'nama_sekolah' => 'SMPN 3 CENGAL'],
            ['no' => 160, 'npsn' => '70013629', 'provinsi' => 'Prov. Sumatera Selatan', 'kabupaten' => 'Kab. Ogan Komering Ulu Selatan', 'nama_sekolah' => 'SMP-IP YAYASAN ABDUS SALAM'],
            ['no' => 161, 'npsn' => '10646106', 'provinsi' => 'Prov. Sumatera Selatan', 'kabupaten' => 'Kab. Ogan Komering Ulu Selatan', 'nama_sekolah' => 'UPT SMP NEGERI 2 BPR RANAU TENGAH'],
            ['no' => 162, 'npsn' => '10212741', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias', 'nama_sekolah' => 'SMP NEGERI 1 BOTOMUZOI'],
            ['no' => 163, 'npsn' => '10260046', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias', 'nama_sekolah' => 'SMP NEGERI 1 MAU'],
            ['no' => 164, 'npsn' => '10260074', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias', 'nama_sekolah' => 'SMP NEGERI 2 HILIDUHO'],
            ['no' => 165, 'npsn' => '69761856', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias', 'nama_sekolah' => 'SMP Negeri 2 Ma u'],
            ['no' => 166, 'npsn' => '10258404', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias', 'nama_sekolah' => 'SMP NEGERI 3 BAWOLATO'],
            ['no' => 167, 'npsn' => '60726295', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias', 'nama_sekolah' => 'SMP NEGERI 4 GIDO'],
            ['no' => 168, 'npsn' => '69725692', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias', 'nama_sekolah' => 'SMPN 5 Bawolato'],
            ['no' => 169, 'npsn' => '10258377', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias Barat', 'nama_sekolah' => 'UPTD SMP NEGERI 1 MORO O'],
            ['no' => 170, 'npsn' => '10260550', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias Selatan', 'nama_sekolah' => 'SMP NEGERI 3 HURUNA'],
            ['no' => 171, 'npsn' => '10258690', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias Utara', 'nama_sekolah' => 'SMP N 2 NAMOHALU ESIWA'],
            ['no' => 172, 'npsn' => '10212754', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias Utara', 'nama_sekolah' => 'SMP N 3 ALASA TALUMUZOI'],
            ['no' => 173, 'npsn' => '69943460', 'provinsi' => 'Prov. Sumatera Utara', 'kabupaten' => 'Kab. Nias Utara', 'nama_sekolah' => 'SMP NEGERI 5 LAHEWA'],
        ];

        $docTypes = [
            'pks',
            'pakta_integritas',
            'sptjm',
            'rab',
            'laporan_awal',
            'perbandingan_siplah',
            'surat_pemesanan_siplah',
            'invoice_siplah',
            'bast',
            'buku_inventaris',
            'dokumentasi_pemanfaatan',
            'laporan_akhir',
            'pengantar_lpj',
            'lpj',
            'bukti_setor_sisa_dana',
        ];

        $csvRows = [];
        $csvRows[] = ['No', 'NPSN', 'Nama Sekolah', 'Provinsi', 'Kabupaten', 'Username Login', 'Password'];

        foreach ($schools as $item) {
            $no = $item['no'];
            $npsn = $item['npsn'];
            $namaSekolah = $item['nama_sekolah'];
            $provinsi = $item['provinsi'];
            $kabupaten = $item['kabupaten'];

            // Easy to remember password pattern: Bantek@ + last 4 digits of NPSN (e.g. Bantek@0698)
            $plainPassword = 'Bantek@'.substr($npsn, -4);
            $hashedPassword = Hash::make($plainPassword);

            // 1. Create or Update Sekolah
            $sekolah = Sekolah::updateOrCreate(
                ['npsn' => $npsn],
                [
                    'nama_sekolah' => $namaSekolah,
                    'provinsi' => $provinsi,
                    'kabupaten' => $kabupaten,
                    'alamat' => "Jl. Pendidikan, {$kabupaten}, {$provinsi}",
                    'status_dana' => 'Belum Disalurkan',
                    'status_dokumen' => 'Belum Lengkap',
                ]
            );

            // 2. Create or Update User Account
            $email = "smp_{$npsn}@kemendikdasmen.go.id";
            User::updateOrCreate(
                ['npsn' => $npsn],
                [
                    'name' => $namaSekolah,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role' => 'sekolah',
                    'sekolah_id' => $sekolah->id,
                    'status' => 'aktif',
                ]
            );

            // 3. Initialize Required Documents
            foreach ($docTypes as $type) {
                Dokumen::firstOrCreate(
                    [
                        'sekolah_id' => $sekolah->id,
                        'jenis_dokumen' => $type,
                    ],
                    [
                        'status' => 'Belum Diunggah',
                    ]
                );
            }

            // 4. Initialize Default Draft RAB
            Rab::firstOrCreate(
                ['sekolah_id' => $sekolah->id],
                [
                    'merek_tipe_laptop' => '',
                    'spesifikasi_ringkas' => '',
                    'jumlah_unit' => 8,
                    'harga_satuan' => 0,
                    'total_harga' => 0,
                    'status' => 'Draft',
                ]
            );

            $csvRows[] = [$no, $npsn, $namaSekolah, $provinsi, $kabupaten, $npsn, $plainPassword];
        }

        // Export CSV with UTF-8 BOM for Excel
        $csvContent = "\xEF\xBB\xBF";
        foreach ($csvRows as $row) {
            $csvContent .= implode(',', array_map(function ($val) {
                return '"'.str_replace('"', '""', (string) $val).'"';
            }, $row))."\r\n";
        }

        file_put_contents(public_path('daftar_akun_173_sekolah_tik_2026.csv'), $csvContent);
        if (! is_dir(storage_path('app/public'))) {
            mkdir(storage_path('app/public'), 0755, true);
        }
        file_put_contents(storage_path('app/public/daftar_akun_173_sekolah_tik_2026.csv'), $csvContent);

        // Also export formatted Excel HTML (.xls)
        $xlsContent = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $xlsContent .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $xlsContent .= '<style>
            table { border-collapse: collapse; width: 100%; font-family: Calibri, sans-serif; font-size: 11pt; }
            th { background-color: #1e2d5a; color: #ffffff; font-weight: bold; border: 1px solid #000; padding: 6px 10px; text-align: center; }
            td { border: 1px solid #cccccc; padding: 5px 8px; }
            .center { text-align: center; }
            .bold { font-weight: bold; }
        </style></head><body>';
        $xlsContent .= '<h2 style="font-family: Calibri, sans-serif; color: #1e2d5a;">DAFTAR AKUN & KREDENSIAL LOGIN 173 SEKOLAH PENERIMA BANTUAN TIK SMP 2026</h2>';
        $xlsContent .= '<p style="font-family: Calibri, sans-serif; font-size: 10pt; color: #555555;">Pola Password: <b>Bantek@[4 digit terakhir NPSN]</b></p>';
        $xlsContent .= '<table>';
        $xlsContent .= '<thead><tr>
            <th>No</th>
            <th>NPSN</th>
            <th>Nama Sekolah</th>
            <th>Provinsi</th>
            <th>Kabupaten / Kota</th>
            <th>Username Login</th>
            <th>Password</th>
        </tr></thead><tbody>';

        foreach (array_slice($csvRows, 1) as $r) {
            $xlsContent .= "<tr>
                <td class='center'>{$r[0]}</td>
                <td class='center bold'>'{$r[1]}</td>
                <td>{$r[2]}</td>
                <td>{$r[3]}</td>
                <td>{$r[4]}</td>
                <td class='center bold'>'{$r[5]}</td>
                <td class='center bold' style='background-color: #f0fdf4; color: #166534;'>{$r[6]}</td>
            </tr>";
        }
        $xlsContent .= '</tbody></table></body></html>';

        file_put_contents(public_path('daftar_akun_173_sekolah_tik_2026.xls'), $xlsContent);
        file_put_contents(storage_path('app/public/daftar_akun_173_sekolah_tik_2026.xls'), $xlsContent);
    }
}
