<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->string('no_telepon')->nullable()->after('atas_nama_rekening');
            $table->string('email_sekolah')->nullable()->after('no_telepon');
            $table->string('nama_ketua_komite')->nullable()->after('email_sekolah');
        });
    }

    public function down(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropColumn(['no_telepon', 'email_sekolah', 'nama_ketua_komite']);
        });
    }
};
