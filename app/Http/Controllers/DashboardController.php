<?php

namespace App\Http\Controllers;

use App\Models\Perkara;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $bulanIni   = Carbon::now()->startOfMonth()->toDateString();
        $bulanAkhir = Carbon::now()->endOfMonth()->toDateString();

        // Ambil semua perkara bulan ini beserta relasi
        $rows = Perkara::with(['efiling', 'putusan'])
            ->whereBetween('tanggal_pendaftaran', [$bulanIni, $bulanAkhir])
            ->get();

        $totalMasukBulanIni = $rows->count();
        $putus       = 0;
        $minutasi    = 0;
        $dalamProses = 0;
        $counterJenis = [];

        foreach ($rows as $p) {
            $tglMinutasi = $p->putusan?->tanggal_minutasi;
            $tglPutusan  = $p->putusan?->tanggal_putusan;

            if ($tglMinutasi && $tglMinutasi !== '') {
                $minutasi++;
            } elseif ($tglPutusan && $tglPutusan !== '') {
                $putus++;
            } else {
                $dalamProses++;
            }

            // Grouping langsung pakai jenis_perkara_nama dari kolom perkara
            $namaJenis = $p->jenis_perkara_nama ?? 'Lainnya';
            $counterJenis[$namaJenis] = ($counterJenis[$namaJenis] ?? 0) + 1;
        }

        // Data chart — sudah pakai nama langsung, tidak perlu join ke tabel lain
        $jenisPerkaraData = collect($counterJenis)
            ->map(fn($count, $nama) => [
                'nama'   => $nama,
                'jumlah' => $count,
            ])
            ->sortByDesc('jumlah')
            ->values()
            ->filter(fn($i) => $i['jumlah'] > 0)
            ->take(8);

        // Notifikasi
        $notifikasi = collect([
            ['pesan' => 'Beberapa perkara mendekati batas waktu minutasi', 'waktu' => 'Hari ini', 'warna' => '#e74c3c'],
            ['pesan' => 'Laporan bulan lalu siap diunduh', 'waktu' => 'Kemarin', 'warna' => '#1D9E75'],
        ]);

        $mendekatiDeadline = 0;

        return view('dashboard', compact(
            'totalMasukBulanIni', 'putus', 'minutasi', 'dalamProses',
            'jenisPerkaraData', 'notifikasi', 'mendekatiDeadline'
        ));
    }
}