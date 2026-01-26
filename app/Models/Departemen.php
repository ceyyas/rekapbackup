<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departemen extends Model
{
    use HasFactory;

    protected $table = 'departemen';
    protected $fillable = ['nama_departemen', 'perusahaan_id']; 

    public function perusahaan() {
        return $this->belongsTo(Perusahaan::class);
    }

    public function inventori() {
        return $this->hasMany(Inventori::class);
    }
    
    public function rekapBackup()
    {
        return $this->hasMany(RekapBackup::class);
    }
}
