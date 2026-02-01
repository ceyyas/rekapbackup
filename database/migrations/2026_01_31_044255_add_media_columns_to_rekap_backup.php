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
            $table->integer('jumlah_cd700')->default(0)->after('status');
            $table->integer('jumlah_dvd47')->default(0)->after('jumlah_cd700');
            $table->integer('jumlah_dvd85')->default(0)->after('jumlah_dvd47');
        });
    }

    public function down()
    {
        Schema::table('rekap_backup', function (Blueprint $table) {
            $table->dropColumn(['jumlah_cd700', 'jumlah_dvd47', 'jumlah_dvd85']);
        });
    }

};
