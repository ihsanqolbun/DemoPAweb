<?php

namespace App\Console\Commands;

use App\Services\RekapPerkaraService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:warm-rekap-cache')]
#[Description('Pre-warm cache untuk rekap periode hari ini dalam 24 jam ke depan')]
class WarmRekapCache extends Command
{
    protected $signature = 'app:warm-rekap-cache {--date=} : Tanggal target (format Y-m-d), default hari ini';

    protected $description = 'Pre-warm cache untuk rekap periode hari ini dalam 24 jam ke depan';

    public function __construct(
        private RekapPerkaraService $rekapService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dateInput = $this->option('date') ?? today()->toDateString();
        $mulai = $dateInput;
        $akhir = $dateInput;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $mulai)) {
            $this->error('Format tanggal salah. Gunakan format Y-m-d (contoh: 2024-01-01)');
            return self::FAILURE;
        }

        $this->info("Originate pre-warming cache untuk periode: {$mulai} s/d {$akhir}");

        try {
            $now = now();
            $cacheKey = "rekap_stats_{$mulai}_{$akhir}";

            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $this->warn('Cache sudah ada, skip pre-warming.');
                return self::SUCCESS;
            }

            $startTime = microtime(true);
            $rekapService->getStats($mulai, $akhir);
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime), 2);

            $this->info("✓ Cache berhasil di-warm ({$duration}s)");
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Gagal warm cache: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
