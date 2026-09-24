<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraHakimPn extends Model
{
    protected $table      = 'perkara_hakim_pn';
    protected $primaryKey = 'perkara_id';
    public    $timestamps = false;

    protected $fillable = [
        'perkara_id',
        'hakim_nama',
        'urutan',
        'aktif',
    ];
}