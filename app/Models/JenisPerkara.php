<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model ini tidak digunakan — jenis perkara diambil langsung
// dari kolom jenis_perkara_nama di tabel perkara
class JenisPerkara extends Model
{
    protected $table = 'perkara'; // safety fallback, tapi model ini tidak di-query
}