<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapBackup extends Model
{
    protected $table = 'rekap_backup';

    protected $fillable = [
        'inventori_id',
        'periode',
        'size_data',
        'size_email',
        'status',
        'jumlah_cd700',
        'jumlah_dvd47',
        'jumlah_dvd85'
    ];
    
    public function inventori()
    {
        return $this->belongsTo(Inventori::class);
    }

}
