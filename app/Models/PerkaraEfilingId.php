<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraEfilingId extends Model
{
    protected $table      = 'perkara_efiling_id';
    protected $primaryKey = 'perkara_id';
    public    $timestamps = false;

    protected $fillable = [
        'perkara_id',
        'efiling_id',
    ];
}