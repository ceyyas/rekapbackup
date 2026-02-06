<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventori extends Model
{
    protected $table = 'inventori';
    protected $fillable = [
        'perusahaan_id',
        'departemen_id',
        'hostname',
        'username',
        'email',
        'status',
        'kategori'
    ];
    
    public function perusahaan() {
        return $this->belongsTo(Perusahaan::class);
    }

    public function departemen() {
        return $this->belongsTo(Departemen::class);
    }

    public function rekap_backup() {
        return $this->hasMany(RekapBackup::class);
    }

}
