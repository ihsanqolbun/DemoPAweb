<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX idx_perkara_tanggal_pendaftaran ON perkara(tanggal_pendaftaran)');
        
        DB::statement('CREATE INDEX idx_perkara_efiling_perkara_id ON perkara_efiling_id(perkara_id)');
        
        DB::statement('CREATE INDEX idx_perkara_putusan_perkara_id ON perkara_putusan(perkara_id)');
        
        DB::statement('CREATE INDEX idx_perkara_hakim_pn_composite ON perkara_hakim_pn(perkara_id, urutan, aktif)');
        
        DB::statement('CREATE INDEX idx_perkara_pihak1_pihak_id ON perkara_pihak1(pihak_id)');
        
        DB::statement('CREATE INDEX idx_perkara_pihak2_pihak_id ON perkara_pihak2(pihak_id)');
        
        DB::statement('CREATE INDEX idx_perkara_jadwal_sidang_perkara_id ON perkara_jadwal_sidang(perkara_id)');
        
        DB::statement('CREATE INDEX idx_perkara_mediasi_perkara_id ON perkara_mediasi(perkara_id)');
        
        DB::statement('CREATE INDEX idx_perkara_biaya_perkara_id ON perkara_biaya(perkara_id)');
        
        DB::statement('CREATE INDEX idx_perkara_banding_detil_perkara_id ON perkara_banding_detil(perkara_id)');
        
        DB::connection('dummy_aps')->statement('CREATE INDEX idx_eac_validasi_perkara_id ON eac_validasi(perkara_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_perkara_tanggal_pendaftaran ON perkara');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_efiling_perkara_id ON perkara_efiling_id');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_putusan_perkara_id ON perkara_putusan');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_hakim_pn_composite ON perkara_hakim_pn');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_pihak1_pihak_id ON perkara_pihak1');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_pihak2_pihak_id ON perkara_pihak2');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_jadwal_sidang_perkara_id ON perkara_jadwal_sidang');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_mediasi_perkara_id ON perkara_mediasi');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_biaya_perkara_id ON perkara_biaya');
        DB::statement('DROP INDEX IF EXISTS idx_perkara_banding_detil_perkara_id ON perkara_banding_detil');
        DB::connection('dummy_aps')->statement('DROP INDEX IF EXISTS idx_eac_validasi_perkara_id ON eac_validasi');
    }
};
