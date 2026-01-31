<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode_backup';

    protected $fillable = [
        'bulan',
        'nama_bulan',
        'tahun'
    ];

    public function rekapBackup()
    {
        return $this->hasMany(RekapBackup::class);
    }
}
