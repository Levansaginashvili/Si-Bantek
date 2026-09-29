<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            $table->json('items')->nullable()->after('spesifikasi_ringkas');
        });

        Schema::table('dokumens', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            $table->dropColumn('items');
        });

        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
