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
        Schema::create('rekapburning', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sppb');
             $table->enum('nama_barang', [
                'CD 700 MB',
                'DVD 4.7 GB',
                'DVD 8.5 GB']);
            $table->tinyInteger('jumlah_barang');
            $table->integer('pemakaian')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekapburning');
    }
};
