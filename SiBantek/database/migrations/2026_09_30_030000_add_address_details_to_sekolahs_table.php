<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->string('rt', 10)->nullable()->after('alamat');
            $table->string('rw', 10)->nullable()->after('rt');
            $table->string('nomor_bangunan', 20)->nullable()->after('rw');
            $table->string('desa_kelurahan', 100)->nullable()->after('nomor_bangunan');
            $table->string('kecamatan', 100)->nullable()->after('desa_kelurahan');
            $table->string('kode_pos', 10)->nullable()->after('provinsi');
        });
    }

    public function down(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropColumn([
                'rt',
                'rw',
                'nomor_bangunan',
                'desa_kelurahan',
                'kecamatan',
                'kode_pos',
            ]);
        });
    }
};
