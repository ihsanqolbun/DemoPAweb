<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private array $validRoles = ['super_admin', 'admin', 'pimpinan', 'hakim'];
    private string $defaultPassword = 'password123';

    // ================================================
    // Index — list semua user
    // ================================================
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50]) ? (int)$request->get('per_page') : 10;
        $sortBy  = in_array($request->get('sort_by'), ['name', 'username', 'role', 'created_at'])
            ? $request->get('sort_by') : 'created_at';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = DB::connection('mysql')->table('users')
            ->select('id', 'name', 'username', 'role', 'created_at', 'updated_at');

        if ($search) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name',       'LIKE', $like)
                  ->orWhere('username', 'LIKE', $like)
                  ->orWhere('role',     'LIKE', $like);
            });
        }

        $rows = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();

        return view('users.index', [
            'rows'    => $rows,
            'search'  => $search,
            'perPage' => $perPage,
            'sortBy'  => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    // ================================================
    // Create — form tambah user
    // ================================================
    public function create()
    {
        return view('users.create', [
            'validRoles' => $this->validRoles,
        ]);
    }

    // ================================================
    // Store — simpan user baru
    // ================================================
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:' . implode(',', $this->validRoles),
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan, pilih username lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required'      => 'Role wajib dipilih.',
            'role.in'            => 'Role tidak valid.',
        ]);

        DB::connection('mysql')->table('users')->insert([
            'name'       => $request->name,
            'username'   => $request->username,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User "' . $request->username . '" berhasil ditambahkan.');
    }

    // ================================================
    // Edit — form edit user
    // ================================================
    public function edit(int $id)
    {
        $user = DB::connection('mysql')->table('users')
            ->select('id', 'name', 'username', 'role', 'created_at')
            ->where('id', $id)
            ->first();

        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User tidak ditemukan.');
        }

        return view('users.edit', [
            'user'       => $user,
            'validRoles' => $this->validRoles,
        ]);
    }

    // ================================================
    // Update — simpan perubahan user
    // ================================================
    public function update(Request $request, int $id)
    {
        $user = DB::connection('mysql')->table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User tidak ditemukan.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username,' . $id,
            'role'     => 'required|in:' . implode(',', $this->validRoles),
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan, pilih username lain.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
        ]);

        DB::connection('mysql')->table('users')->where('id', $id)->update([
            'name'       => $request->name,
            'username'   => $request->username,
            'role'       => $request->role,
            'updated_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Data user "' . $request->username . '" berhasil diperbarui.');
    }

    // ================================================
    // Destroy — hapus user
    // ================================================
    public function destroy(int $id)
    {
        $user = DB::connection('mysql')->table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User tidak ditemukan.');
        }

        if (auth()->id() === $id) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun yang sedang aktif.');
        }

        DB::connection('mysql')->table('users')->where('id', $id)->delete();

        return redirect()->route('users.index')
            ->with('success', 'User "' . $user->username . '" berhasil dihapus.');
    }

    // ================================================
    // Reset Password — set ke password default
    // ================================================
    public function resetPassword(int $id)
    {
        $user = DB::connection('mysql')->table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User tidak ditemukan.');
        }

        DB::connection('mysql')->table('users')->where('id', $id)->update([
            'password'   => Hash::make($this->defaultPassword),
            'updated_at' => now(),
        ]);

        return redirect()->route('users.edit', $id)
            ->with('success', 'Password "' . $user->username . '" berhasil direset ke password default. Informasikan ke user.');
    }
}