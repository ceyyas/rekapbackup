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
