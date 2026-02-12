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
            $table->foreignId('inventori_id')
                ->references('id')->on('inventori')
                ->onDelete('cascade');
            $table->date('periode');
            $table->bigInteger('size_data')->default(0);
            $table->bigInteger('size_email')->default(0);
            $table->enum('status', [
                'pending',
                'partial',
                'completed'
            ])->default('pending');
            $table->enum('status_data', [
                'data belum di backup',
                'proses backup',
                'file di main folder aman',
                'file di cd aman'
            ])->default('data belum di backup');
            $table->integer('jumlah_cd700')->default(0);
            $table->integer('jumlah_dvd47')->default(0);
            $table->integer('jumlah_dvd85')->default(0);
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
