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
        'status'
    ];
    
    public function inventori()
    {
        return $this->belongsTo(Inventori::class);
    }

}
