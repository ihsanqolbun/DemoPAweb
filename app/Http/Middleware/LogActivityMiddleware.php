<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LogActivityMiddleware
{
    private array $routeMap = [
        'login.post'           => 'Login',
        'logout'               => 'Logout',
        'users.store'          => 'Tambah User',
        'users.update'         => 'Edit User',
        'users.destroy'        => 'Hapus User',
        'users.reset-password' => 'Reset Password User',
        'pihak.update'         => 'Edit Data Pihak',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Simpan data user SEBELUM response diproses
        // (penting untuk logout — session belum dihapus di sini)
        $user     = auth()->user();
        $userId   = $user?->id;
        $username = $user?->username;
        $role     = $user?->role;

        $response = $next($request);

        $status = $response->getStatusCode();
        if (!in_array($status, [200, 201, 302])) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        if (!$routeName || !isset($this->routeMap[$routeName])) {
            return $response;
        }

        // Khusus login — hanya catat kalau redirect bukan ke halaman login (berarti berhasil)
        if ($routeName === 'login.post') {
            $location = $response->headers->get('Location', '');
            if (str_contains($location, 'login')) return $response;

            // Setelah login berhasil, ambil user dari session yang baru dibuat
            $userId   = auth()->id();
            $username = auth()->user()?->username;
            $role     = auth()->user()?->role;
        }

        $keterangan = $this->buildKeterangan($routeName, $request);

        try {
            DB::connection('mysql')->table('activity_log')->insert([
                'user_id'    => $userId,
                'username'   => $username,
                'role'       => $role,
                'aksi'       => $this->routeMap[$routeName],
                'keterangan' => $keterangan,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Gagal catat log tidak boleh ganggu aplikasi
        }

        return $response;
    }

    private function buildKeterangan(string $routeName, Request $request): string
    {
        return match ($routeName) {
            'login.post' => 'Login berhasil',
            'logout'     => 'Logout',

            'users.store' => sprintf(
                'Menambah user baru: %s (role: %s)',
                $request->input('username', '-'),
                $request->input('role', '-')
            ),

            'users.update' => sprintf(
                'Mengedit user ID %s: username %s',
                $request->route('id', '-'),
                $request->input('username', '-')
            ),

            'users.destroy' => sprintf(
                'Menghapus user ID %s',
                $request->route('id', '-')
            ),

            'users.reset-password' => sprintf(
                'Reset password user ID %s',
                $request->route('id', '-')
            ),

            'pihak.update' => sprintf(
                'Mengedit data pihak ID %s: %s',
                $request->route('id', '-'),
                $request->input('nama', '-')
            ),

            default => '-',
        };
    }
}