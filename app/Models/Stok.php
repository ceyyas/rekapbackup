<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'rekapburning';

    // pastikan kolom 'pemakaian' sudah ada di tabel rekapburning
    protected $fillable = [
        'nomor_sppb',
        'nama_barang',
        'jumlah_barang',
        'pemakaian',
    ];

    // accessor tersisa dihitung dari jumlah_barang - pemakaian
    public function getTersisaAttribute()
    {
        return max(0, $this->jumlah_barang - $this->pemakaian);
    }
}
