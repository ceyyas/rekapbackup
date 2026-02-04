<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'rekapburning';
    protected $fillable = ['nomor_sppb', 'nama_barang', 'jumlah_barang'];

    public $timestamps = false;
    public function getPemakaianAttribute()
    {
        return match ($this->nama_barang) {
            'CD 700 MB' => \App\Models\RekapBackup::sum('jumlah_cd700'),
            'DVD 4.7 GB' => \App\Models\RekapBackup::sum('jumlah_dvd47'),
            'DVD 8.5 GB' => \App\Models\RekapBackup::sum('jumlah_dvd85'),
        };
    }

    public function getTersisaAttribute()
    {
        return $this->jumlah_barang - $this->pemakaian;
    }

}
