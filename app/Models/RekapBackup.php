<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapBackup extends Model
{
    protected $table = 'rekap_backup';

    protected $fillable = [
        'inventori_id',
        'perusahaan_id',
        'departemen_id',
        'periode_id',
        'size_data',
        'size_email',
        'status'
    ];
    
    public function inventori()
    {
        return $this->belongsTo(Inventori::class);
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeBackup::class);
    }

}
