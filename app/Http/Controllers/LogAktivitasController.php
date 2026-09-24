<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $aksi    = $request->get('aksi', '');
        $role    = $request->get('role', '');
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50]) ? (int)$request->get('per_page') : 10;
        $sortBy  = in_array($request->get('sort_by'), ['created_at', 'username', 'aksi', 'role'])
            ? $request->get('sort_by') : 'created_at';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = DB::connection('mysql')->table('activity_log');

        if ($search) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('username',    'LIKE', $like)
                  ->orWhere('keterangan','LIKE', $like)
                  ->orWhere('aksi',      'LIKE', $like)
                  ->orWhere('ip_address','LIKE', $like);
            });
        }

        if ($aksi)  $query->where('aksi', $aksi);
        if ($role)  $query->where('role', $role);

        $rows = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();

        // Ambil distinct aksi untuk filter dropdown
        $daftarAksi = DB::connection('mysql')->table('activity_log')
            ->distinct()->orderBy('aksi')->pluck('aksi');

        return view('log.index', [
            'rows'       => $rows,
            'search'     => $search,
            'aksiFilter' => $aksi,
            'roleFilter' => $role,
            'perPage'    => $perPage,
            'sortBy'     => $sortBy,
            'sortDir'    => $sortDir,
            'daftarAksi' => $daftarAksi,
        ]);
    }
}