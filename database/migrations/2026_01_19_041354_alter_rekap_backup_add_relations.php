<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekap_backup', function (Blueprint $table) {

        $table->foreignId('perusahaan_id')
            ->after('inventori_id')
            ->constrained('perusahaan')
            ->cascadeOnDelete();

        $table->foreignId('departemen_id')
            ->after('perusahaan_id')
            ->constrained('departemen')
            ->cascadeOnDelete();

        $table->foreignId('periode_id')
            ->after('departemen_id')
            ->constrained('periode_backup')
            ->cascadeOnDelete();

        $table->unique(['inventori_id', 'periode_id']);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
