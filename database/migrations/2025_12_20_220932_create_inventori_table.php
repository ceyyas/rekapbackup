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
        Schema::create('inventori', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('perusahaan_id');
            $table->foreignId('departemen_id');
            $table->string('hostname');
            $table->string('username');
            $table->string('email');
            $table->enum('status',['active','inactive'])->default('active');
            $table->enum('kategori',['PC','Laptop'])->default('PC');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventori');
    }
};
