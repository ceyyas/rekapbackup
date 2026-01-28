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
        Schema::create('periode_backup', function (Blueprint $table) {
            $table->id(); 
            $table->tinyInteger('bulan'); 
            $table->string('nama_bulan');  
            $table->year('tahun');
            $table->timestamps();

            $table->unique(['bulan', 'tahun']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_backup');
    }
    
};
