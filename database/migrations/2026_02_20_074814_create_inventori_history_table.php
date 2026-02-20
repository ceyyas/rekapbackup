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
        Schema::create('inventori_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventori_id')->constrained('inventori')->onDelete('cascade');
            $table->string('hostname');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
            $table->enum('kategori',['PC','Laptop'])->default('PC');
            $table->date('effective_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventori_history');
    }
};
