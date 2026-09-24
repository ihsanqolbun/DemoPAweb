<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PihakController extends Controller
{
    // Kolom yang boleh di-update di tabel pihak
    private array $editableColumns = [
        'nama', 'alamat', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 'keterangan'
    ];

    // ================================================
    // Index — list pihak yang ada di eac_validasi
    // ================================================
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100])
            ? (int)$request->get('per_page') : 10;

        $query = DB::connection('mysql')->table('pihak as p')
            ->select('p.id', 'p.nama', 'p.alamat', 'p.tempat_lahir', 'p.tanggal_lahir', 'p.pekerjaan', 'p.keterangan')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from(DB::connection('aps_badilag')->getDatabaseName() . '.eac_validasi as ev')
                  ->whereColumn('ev.perkara_id', 'p.id');
            });

        if ($search) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('p.nama',         'LIKE', $like)
                  ->orWhere('p.alamat',      'LIKE', $like)
                  ->orWhere('p.pekerjaan',   'LIKE', $like)
                  ->orWhere('p.keterangan',  'LIKE', $like)
                  ->orWhere('p.id',          'LIKE', $like)
                  ->orWhere('p.tempat_lahir','LIKE', $like)
                  ->orWhere('p.tanggal_lahir','LIKE', $like);
            });
        }

        $rows = $query->orderBy('p.id')->paginate($perPage)->withQueryString();

        return view('pihak.index', [
            'rows'    => $rows,
            'search'  => $search,
            'perPage' => $perPage,
        ]);
    }

    // ================================================
    // Create — form tambah (DISABLED - uncomment kalau mau aktifkan)
    // ================================================
    public function create()
    {
        return view('pihak.create');
    }

    // ================================================
    // Store — simpan data baru (DISABLED - uncomment kalau mau aktifkan)
    // ================================================
    public function store(Request $request)
    {
        $request->validate([
            'id'       => 'required|string|max:50',
            'nama'     => 'required|string|max:500',
            'alamat'   => 'required|string|max:500',
            'pekerjaan'=> 'required|string|max:255',
            'tempat_lahir'  => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'keterangan'    => 'nullable|string|max:255',
        ]);

        // Cek ID sudah ada
        $exists = DB::connection('mysql')->table('pihak')->where('id', $request->id)->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'ID sudah ada di tabel pihak!');
        }

        DB::beginTransaction();
        try {
            // 1. Insert ke pihak
            DB::connection('mysql')->table('pihak')->insert([
                'id'            => $request->id,
                'nama'          => $request->nama,
                'alamat'        => $request->alamat,
                'tempat_lahir'  => $request->tempat_lahir ?: null,
                'tanggal_lahir' => $request->tanggal_lahir ?: null,
                'pekerjaan'     => $request->pekerjaan,
                'keterangan'    => $request->keterangan ?: null,
                'diinput_tanggal' => now(),
            ]);

            // 2. Insert ke perkara_pihak1 kalau dicentang
            if ($request->has('insert_perkara_pihak1')) {
                DB::connection('mysql')->table('perkara_pihak1')->insert([
                    'perkara_id'    => null,
                    'urutan'        => null,
                    'pihak_id'      => $request->id,
                    'jenis_pihak_id'=> null,
                    'nama'          => $request->nama,
                    'alamat'        => $request->alamat,
                    'keterangan'    => $request->keterangan ?: null,
                ]);
            }

            // 3. Insert ke perkara_pihak2 kalau dicentang
            if ($request->has('insert_perkara_pihak2')) {
                DB::connection('mysql')->table('perkara_pihak2')->insert([
                    'perkara_id'    => null,
                    'urutan'        => null,
                    'pihak_id'      => $request->id,
                    'jenis_pihak_id'=> null,
                    'nama'          => $request->nama,
                    'alamat'        => $request->alamat,
                    'keterangan'    => $request->keterangan ?: null,
                ]);
            }

            DB::commit();

            $addedTables = ['pihak'];
            if ($request->has('insert_perkara_pihak1')) $addedTables[] = 'perkara_pihak1';
            if ($request->has('insert_perkara_pihak2')) $addedTables[] = 'perkara_pihak2';

            return redirect()->route('pihak.index')
                ->with('success', 'Data berhasil ditambahkan ke: ' . implode(', ', $addedTables));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    // ================================================
    // Destroy — hapus dari eac_validasi saja (DISABLED - uncomment kalau mau aktifkan)
    // ================================================
    public function destroy(string $id)
    {
        // Cek dulu apakah ada di eac_validasi
        $exists = DB::connection('aps_badilag')->table('eac_validasi')
            ->where('perkara_id', $id)->exists();

        if (!$exists) {
            return redirect()->route('pihak.index')
                ->with('error', 'Data tidak ditemukan di aps_badilag.eac_validasi.');
        }

        try {
            // Hapus HANYA dari eac_validasi — data pihak di sipp tetap
            DB::connection('aps_badilag')->table('eac_validasi')
                ->where('perkara_id', $id)->delete();

            return redirect()->route('pihak.index')
                ->with('success', 'Data berhasil dihapus dari aps_badilag.eac_validasi. Data pihak di sipp tetap tersimpan.');

        } catch (\Exception $e) {
            return redirect()->route('pihak.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // ================================================
    // Edit — form edit
    // ================================================
    public function edit(string $id)
    {
        $pihak = DB::connection('mysql')->table('pihak')
            ->select('id', 'nama', 'alamat', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 'keterangan')
            ->where('id', $id)
            ->first();

        if (!$pihak) {
            return redirect()->route('pihak.index')
                ->with('error', 'Data pihak tidak ditemukan.');
        }

        // Cek keberadaan di tabel-tabel terkait
        $inPihak1 = DB::connection('mysql')->table('perkara_pihak1')
            ->where('pihak_id', $id)->exists();

        $inPihak2 = DB::connection('mysql')->table('perkara_pihak2')
            ->where('pihak_id', $id)->exists();

        $inEacValidasi = DB::connection('aps_badilag')->table('eac_validasi')
            ->where('perkara_id', $id)->exists();

        return view('pihak.edit', [
            'pihak'        => $pihak,
            'inPihak1'     => $inPihak1,
            'inPihak2'     => $inPihak2,
            'inEacValidasi'=> $inEacValidasi,
        ]);
    }

    // ================================================
    // Update — simpan perubahan
    // ================================================
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama'     => 'required|string|max:500',
            'alamat'   => 'required|string|max:500',
            'pekerjaan'=> 'required|string|max:255',
            'tempat_lahir'  => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'keterangan'    => 'nullable|string|max:255',
        ], [
            'nama.required'     => 'Nama wajib diisi.',
            'alamat.required'   => 'Alamat wajib diisi.',
            'pekerjaan.required'=> 'Pekerjaan wajib diisi.',
        ]);

        // Pastikan data ada
        $pihak = DB::connection('mysql')->table('pihak')->where('id', $id)->first();
        if (!$pihak) {
            return redirect()->route('pihak.index')
                ->with('error', 'Data pihak tidak ditemukan.');
        }

        $data = [
            'nama'          => $request->nama,
            'alamat'        => $request->alamat,
            'pekerjaan'     => $request->pekerjaan,
            'tempat_lahir'  => $request->tempat_lahir ?: null,
            'tanggal_lahir' => $request->tanggal_lahir ?: null,
            'keterangan'    => $request->keterangan ?: null,
            'diperbaharui_tanggal' => now(),
        ];

        DB::beginTransaction();
        try {
            // 1. Update tabel pihak (selalu)
            DB::connection('mysql')->table('pihak')->where('id', $id)->update($data);

            // 2. Update perkara_pihak1 — hanya kalau ada record dengan pihak_id ini
            $inPihak1 = DB::connection('mysql')->table('perkara_pihak1')
                ->where('pihak_id', $id)->exists();
            if ($inPihak1) {
                DB::connection('mysql')->table('perkara_pihak1')
                    ->where('pihak_id', $id)
                    ->update([
                        'nama'     => $request->nama,
                        'alamat'   => $request->alamat,
                        'keterangan' => $request->keterangan ?: null,
                        'diperbaharui_tanggal' => now(),
                    ]);
            }

            // 3. Update perkara_pihak2 — hanya kalau ada record dengan pihak_id ini
            $inPihak2 = DB::connection('mysql')->table('perkara_pihak2')
                ->where('pihak_id', $id)->exists();
            if ($inPihak2) {
                DB::connection('mysql')->table('perkara_pihak2')
                    ->where('pihak_id', $id)
                    ->update([
                        'nama'     => $request->nama,
                        'alamat'   => $request->alamat,
                        'keterangan' => $request->keterangan ?: null,
                        'diperbaharui_tanggal' => now(),
                    ]);
            }

            DB::commit();

            $updatedTables = ['pihak'];
            if ($inPihak1) $updatedTables[] = 'perkara_pihak1';
            if ($inPihak2) $updatedTables[] = 'perkara_pihak2';

            return redirect()->route('pihak.index')
                ->with('success', 'Data berhasil diperbarui. Tabel terupdate: ' . implode(', ', $updatedTables));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
}