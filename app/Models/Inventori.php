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

    protected static function booted()
    {
        static::created(function ($inventori) {
            InventoriHistory::create([
                'inventori_id' => $inventori->id,
                'hostname' => $inventori->hostname,
                'username' => $inventori->username,
                'email' => $inventori->email,
                'status' => $inventori->status,
                'kategori' => $inventori->kategori,
                'effective_date' => $inventori->created_at,
            ]);
        });

        static::updating(function ($inventori) {
            InventoriHistory::create([
                'inventori_id' => $inventori->id,
                'hostname' => $inventori->getOriginal('hostname'),
                'username' => $inventori->getOriginal('username'),
                'email' => $inventori->getOriginal('email'),
                'status' => $inventori->getOriginal('status'),
                'kategori' => $inventori->getOriginal('kategori'),
                'effective_date' => now(),
            ]);
        });
    }
}
