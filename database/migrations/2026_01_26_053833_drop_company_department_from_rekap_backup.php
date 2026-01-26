<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('rekap_backup', function (Blueprint $table) {
            // HAPUS FK DULU JIKA ADA
            $table->dropForeign(['perusahaan_id']);
            $table->dropForeign(['departemen_id']);

            // HAPUS KOLOM
            $table->dropColumn(['perusahaan_id', 'departemen_id']);
        });
    }

    public function down()
    {
        Schema::table('rekap_backup', function (Blueprint $table) {
            $table->foreignId('perusahaan_id')->after('inventori_id');
            $table->foreignId('departemen_id')->after('perusahaan_id');
        });
    }

};
