<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraPutusan extends Model
{
    protected $table      = 'perkara_putusan';
    protected $primaryKey = 'perkara_id';
    public    $timestamps = false;

    protected $fillable = [
        'perkara_id',
        'tanggal_putusan',
        'tanggal_minutasi',
    ];
}