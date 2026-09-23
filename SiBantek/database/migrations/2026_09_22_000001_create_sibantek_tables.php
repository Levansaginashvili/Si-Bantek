<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolahs', function (Blueprint $table) {
            $table->id();
            $table->string('npsn')->unique();
            $table->string('nama_sekolah');
            $table->string('provinsi');
            $table->string('kabupaten');
            $table->text('alamat')->nullable();
            $table->string('nama_kepsek')->nullable();
            $table->string('nip_kepsek')->nullable();
            $table->string('nama_bendahara')->nullable();
            $table->string('nip_bendahara')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->enum('status_dana', ['Belum Disalurkan', 'Dana Sudah Disalurkan / Ditransfer'])->default('Belum Disalurkan');
            $table->enum('status_dokumen', ['Belum Lengkap', 'Lengkap'])->default('Belum Lengkap');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'verifikator', 'sekolah'])->default('sekolah')->after('email');
            $table->foreignId('sekolah_id')->nullable()->after('role')->constrained('sekolahs')->onDelete('cascade');
        });

        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->onDelete('cascade');
            $table->string('jenis_dokumen');
            $table->string('file_path')->nullable();
            $table->enum('status', ['Belum Diunggah', 'Menunggu Verifikasi', 'Disetujui', 'Revisi'])->default('Belum Diunggah');
            $table->text('catatan_revisi')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['sekolah_id', 'jenis_dokumen']);
        });

        Schema::create('rabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->onDelete('cascade');
            $table->string('merek_tipe_laptop');
            $table->string('spesifikasi_ringkas')->default('4 Core / 8 Thread, 13-14 inch, RAM 8GB, SSD 256GB, OS GUI Legal');
            $table->integer('jumlah_unit')->default(8);
            $table->decimal('harga_satuan', 12, 2)->default(8625000.00);
            $table->decimal('total_harga', 12, 2)->default(69000000.00);
            $table->enum('status', ['Draft', 'Menunggu Verifikasi', 'Disetujui', 'Revisi'])->default('Draft');
            $table->text('catatan_revisi')->nullable();
            $table->timestamps();
        });

        Schema::create('inventaris_laptops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->onDelete('cascade');
            $table->integer('nomor_unit');
            $table->string('nomor_seri')->nullable();
            $table->string('merek_tipe')->nullable();
            $table->string('foto_stiker_path')->nullable();
            $table->string('qr_code_key')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris_laptops');
        Schema::dropIfExists('rabs');
        Schema::dropIfExists('dokumens');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropColumn(['role', 'sekolah_id']);
        });

        Schema::dropIfExists('sekolahs');
    }
};
