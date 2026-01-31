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
        Schema::create('rekap_backup', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('inventori_id');
            $table->date('tanggal_backup');
            $table->bigInteger('size_data')->default(0);
            $table->bigInteger('size_email')->default(0);
            $table->enum('status', [
                'pending',
                'partial',
                'completed'
            ])->default('pending');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_backup');
    }
};
