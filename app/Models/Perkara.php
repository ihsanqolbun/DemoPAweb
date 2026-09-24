<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perkara extends Model
{
    protected $table      = 'perkara';
    protected $primaryKey = 'perkara_id';
    public    $timestamps = false;

    protected $fillable = [
        'nomor_perkara',
        'jenis_perkara_id',
        'jenis_perkara_nama',
        'jenis_perkara_kode',
        'tanggal_pendaftaran',
        'pihak1_text',
        'pihak2_text',
    ];

    // Relasi ke efiling
    public function efiling()
    {
        return $this->hasOne(PerkaraEfilingId::class, 'perkara_id', 'perkara_id');
    }

    // Relasi ke putusan
    public function putusan()
    {
        return $this->hasOne(PerkaraPutusan::class, 'perkara_id', 'perkara_id');
    }

    // Relasi ke hakim aktif urutan 1
    public function hakim()
    {
        return $this->hasOne(PerkaraHakimPn::class, 'perkara_id', 'perkara_id')
                    ->where('urutan', '1')
                    ->where('aktif', 'Y');
    }

    // Scope filter tanggal pendaftaran
    public function scopePeriode($query, $mulai, $akhir)
    {
        return $query->whereBetween('tanggal_pendaftaran', [$mulai, $akhir]);
    }
}