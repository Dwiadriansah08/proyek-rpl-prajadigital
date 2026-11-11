<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use App\Services\VillageStatistikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Controller untuk manajemen User (akun pengguna sistem).
 *
 * Hanya dapat diakses oleh super_admin melalui middleware
 * role di routes dan UserPolicy untuk otorisasi resource.
 */
class UserController extends Controller
{
    /**
     * Tampilkan daftar user dengan filter role dan pencarian.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['role', 'warga', 'rw', 'rt']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhereHas('warga', function ($wq) use ($search) {
                        $wq->where('nik', 'like', '%'.$search.'%')
                            ->orWhere('nomor_kk', 'like', '%'.$search.'%')
                            ->orWhere('nama_lengkap', 'like', '%'.$search.'%')
                            ->orWhere('alamat', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->role) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->paginate(15);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Tampilkan form untuk membuat user baru.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = auth()->user()->hasRole('super_admin') ? Role::all() : Role::where('name', '!=', 'super_admin')->get();
        $rws = Rw::all();
        $rts = Rt::all();

        return view('users.create', compact('roles', 'rws', 'rts'));
    }

    /**
     * Simpan user baru ke database beserta sinkronisasi data kependudukan.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nomor_telepon' => 'nullable|string|max:20',
            'username' => 'nullable|string|max:50|alpha_dash|unique:users,username',
            'password' => 'required|confirmed|min:8',
            'role_id' => 'required|exists:roles,id',
            'desa_id' => 'nullable|exists:desa,id',
            'rw_id' => 'nullable|exists:rw,id',
            'rt_id' => 'nullable|exists:rt,id',
            'status_dalam_keluarga' => 'nullable|string|max:50',
            'nik' => 'nullable|string|digits:16|unique:warga,nik',
            'nomor_kk' => 'nullable|string|digits:16',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
        ]);

        $targetRole = Role::findOrFail($validated['role_id']);
        if ($targetRole->name === 'super_admin' && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki hak untuk menetapkan role Super Admin.');
        }

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nomor_telepon' => $validated['nomor_telepon'] ?? null,
                'username' => $request->filled('username') ? $validated['username'] : null,
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'desa_id' => $validated['desa_id'] ?? null,
                'rw_id' => $validated['rw_id'] ?? null,
                'rt_id' => $validated['rt_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            $role = Role::find($validated['role_id']);

            // Jika role adalah warga, sinkronkan pembuatan data kependudukan Warga
            if ($role && $role->name === 'warga') {
                $warga = Warga::where('email', $user->email)->first();

                if (! $warga) {
                    $nik = $request->filled('nik') ? $validated['nik'] : null;
                    if (! $nik) {
                        do {
                            $nik = '3201'.date('ymd').str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        } while (Warga::where('nik', $nik)->exists());
                    }

                    $nomorKk = $request->filled('nomor_kk') ? $validated['nomor_kk'] : ('3201'.date('ymd').str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT));
                    $statusKeluarga = $request->filled('status_dalam_keluarga') ? $validated['status_dalam_keluarga'] : 'Kepala Keluarga';
                    $jenisKelamin = $request->filled('jenis_kelamin') ? $validated['jenis_kelamin'] : 'Laki-laki';

                    Warga::create([
                        'nik' => $nik,
                        'nomor_kk' => $nomorKk,
                        'nama_lengkap' => $user->name,
                        'email' => $user->email,
                        'nomor_telepon' => $user->nomor_telepon,
                        'jenis_kelamin' => $jenisKelamin,
                        'rw_id' => $user->rw_id ?? Rw::first()?->id ?? 1,
                        'rt_id' => $user->rt_id ?? Rt::first()?->id ?? 1,
                        'status_dalam_keluarga' => $statusKeluarga,
                        'status_kependudukan' => 'Tetap',
                        'status_aktif' => (bool) $user->is_active,
                    ]);
                } else {
                    $warga->update([
                        'nama_lengkap' => $user->name,
                        'nomor_telepon' => $user->nomor_telepon,
                        'rw_id' => $user->rw_id ?? $warga->rw_id,
                        'rt_id' => $user->rt_id ?? $warga->rt_id,
                        'status_aktif' => (bool) $user->is_active,
                        'status_dalam_keluarga' => $request->filled('status_dalam_keluarga') ? $validated['status_dalam_keluarga'] : $warga->status_dalam_keluarga,
                    ]);
                }
            } elseif ($role && $role->name === 'rw' && $user->rw_id) {
                Rw::where('id', $user->rw_id)->update(['ketua_rw' => $user->name]);
            } elseif ($role && $role->name === 'rt' && $user->rt_id) {
                Rt::where('id', $user->rt_id)->update(['ketua_rt' => $user->name]);
            }

            VillageStatistikService::clearStatsCache();
        });

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail user.
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['role', 'warga.rw', 'warga.rt', 'rw', 'rt', 'desa']);

        return view('users.show', compact('user'));
    }

    /**
     * Tampilkan form untuk mengubah data user.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = auth()->user()->hasRole('super_admin') ? Role::all() : Role::where('name', '!=', 'super_admin')->get();
        $rws = Rw::all();
        $rts = Rt::all();

        return view('users.edit', compact('user', 'roles', 'rws', 'rts'));
    }

    /**
     * Perbarui data user di database beserta sinkronisasi Warga.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'nomor_telepon' => 'nullable|string|max:20',
            'username' => 'nullable|string|max:50|alpha_dash|unique:users,username,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'desa_id' => 'nullable|exists:desa,id',
            'rw_id' => 'nullable|exists:rw,id',
            'rt_id' => 'nullable|exists:rt,id',
            'status_dalam_keluarga' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
        ]);

        $targetRole = Role::findOrFail($validated['role_id']);
        if ($targetRole->name === 'super_admin' && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki hak untuk menetapkan role Super Admin.');
        }

        DB::transaction(function () use ($validated, $request, $user) {
            $data = $validated;
            $data['username'] = $request->filled('username') ? $request->username : null;
            $data['is_active'] = $request->has('is_active');

            if ($request->filled('password')) {
                $request->validate(['password' => 'confirmed|min:8']);
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            $role = Role::find($validated['role_id']);

            // Sinkronkan data ke tabel Warga jika role warga atau sudah terhubung warga
            $warga = $user->warga ?? Warga::where('email', $user->email)->first();
            if ($warga) {
                $wargaUpdate = [
                    'nama_lengkap' => $user->name,
                    'email' => $user->email,
                    'nomor_telepon' => $user->nomor_telepon,
                    'status_aktif' => (bool) $user->is_active,
                ];
                if ($user->rw_id) {
                    $wargaUpdate['rw_id'] = $user->rw_id;
                }
                if ($user->rt_id) {
                    $wargaUpdate['rt_id'] = $user->rt_id;
                }
                if ($request->filled('status_dalam_keluarga')) {
                    $wargaUpdate['status_dalam_keluarga'] = $validated['status_dalam_keluarga'];
                }
                if ($request->filled('jenis_kelamin')) {
                    $wargaUpdate['jenis_kelamin'] = $validated['jenis_kelamin'];
                }
                $warga->update($wargaUpdate);
            } elseif ($role && $role->name === 'warga') {
                do {
                    $nik = '3201'.date('ymd').str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                } while (Warga::where('nik', $nik)->exists());

                Warga::create([
                    'nik' => $nik,
                    'nomor_kk' => '3201'.date('ymd').str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'nama_lengkap' => $user->name,
                    'email' => $user->email,
                    'nomor_telepon' => $user->nomor_telepon,
                    'jenis_kelamin' => $request->jenis_kelamin ?: 'Laki-laki',
                    'rw_id' => $user->rw_id ?? Rw::first()?->id ?? 1,
                    'rt_id' => $user->rt_id ?? Rt::first()?->id ?? 1,
                    'status_dalam_keluarga' => $request->status_dalam_keluarga ?: 'Kepala Keluarga',
                    'status_kependudukan' => 'Tetap',
                    'status_aktif' => (bool) $user->is_active,
                ]);
            }

            if ($role && $role->name === 'rw' && $user->rw_id) {
                Rw::where('id', $user->rw_id)->update(['ketua_rw' => $user->name]);
            } elseif ($role && $role->name === 'rt' && $user->rt_id) {
                Rt::where('id', $user->rt_id)->update(['ketua_rt' => $user->name]);
            }

            VillageStatistikService::clearStatsCache();
        });

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus user dari database beserta data kependudukan Warga terkait.
     *
     * Menggunakan UserPolicy untuk mencegah penghapusan akun sendiri.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        DB::transaction(function () use ($user) {
            $warga = $user->warga ?? Warga::where('email', $user->email)->first();
            if ($warga) {
                $warga->delete();
            }

            $user->delete();

            VillageStatistikService::clearStatsCache();
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
