<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoriHistory extends Model
{
    protected $table = 'inventori_history';
    protected $fillable = [
        'inventori_id',
        'hostname',
        'username',
        'email',
        'status',
        'kategori',
        'effective_date'
    ];

    public function inventori() { 
        return $this->belongsTo(Inventori::class); 
    } 
}
