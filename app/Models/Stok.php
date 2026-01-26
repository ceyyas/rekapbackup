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
}
