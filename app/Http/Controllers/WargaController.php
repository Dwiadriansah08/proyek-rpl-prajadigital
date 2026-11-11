<?php

namespace App\Http\Controllers;

use App\Http\Traits\FiltersByWilayah;
use App\Models\Role;
use App\Models\Rt;
use App\Models\User;
use App\Models\Warga;
use App\Services\VillageStatistikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Controller untuk mengelola data Warga (penduduk).
 *
 * Menampilkan data warga sesuai wilayah user:
 * - super_admin: semua warga
 * - rw: warga di RW-nya
 * - rt: warga di RT-nya
 * - warga: hanya data diri sendiri
 */
class WargaController extends Controller
{
    use FiltersByWilayah;

    /**
     * Tampilkan daftar warga dengan filter wilayah dan pencarian.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Warga::query()->with(['rw', 'rt', 'user.role']);

        if ($user->hasRole('rw')) {
            $query->where('rw_id', $user->rw_id);
        } elseif ($user->hasRole('rt')) {
            $query->where('rt_id', $user->rt_id);
        } elseif ($user->hasRole('warga')) {
            $query->where('nik', $user->warga?->nik);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%'.$request->search.'%')
                    ->orWhere('nik', 'like', '%'.$request->search.'%');
            });
        }

        $warga = $query->paginate(15);

        return view('warga.index', compact('warga'));
    }

    /**
     * Tampilkan form untuk menambah warga baru.
     */
    public function create(): View
    {
        $user = auth()->user();
        if (! $user->hasRole(['super_admin', 'rw', 'rt'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menambahkan warga.');
        }

        $rws = $this->getAccessibleRws();
        $rts = $this->getAccessibleRts();

        return view('warga.create', compact('rws', 'rts'));
    }

    /**
     * Simpan data warga baru beserta akun login ke database dalam transaksi.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->hasRole(['super_admin', 'rw', 'rt'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menambahkan warga.');
        }

        // Batasi RT hanya boleh membuat warga di wilayah RT-nya sendiri
        if ($user->hasRole('rt')) {
            if ($request->filled('rt_id') && (int) $request->rt_id !== (int) $user->rt_id) {
                abort(403, 'Anda hanya dapat mendaftarkan warga di wilayah RT Anda sendiri.');
            }
            if ($request->filled('rw_id') && (int) $request->rw_id !== (int) $user->rw_id) {
                abort(403, 'Anda hanya dapat mendaftarkan warga di wilayah RW Anda sendiri.');
            }
            $request->merge([
                'rw_id' => $user->rw_id,
                'rt_id' => $user->rt_id,
            ]);
        } elseif ($user->hasRole('rw')) {
            if ($request->filled('rw_id') && (int) $request->rw_id !== (int) $user->rw_id) {
                abort(403, 'Anda hanya dapat mendaftarkan warga di wilayah RW Anda sendiri.');
            }
            $request->merge([
                'rw_id' => $user->rw_id,
            ]);
        }

        $rules = [
            'nik' => 'required|string|digits:16|unique:warga,nik',
            'nomor_kk' => 'required|string|digits:16',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'nullable|string|max:50',
            'status_perkawinan' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255|unique:users,email|unique:warga,email',
            'username' => 'nullable|string|max:50|alpha_dash|unique:users,username',
            'password' => 'nullable|string|min:8|confirmed',
            'alamat' => 'nullable|string',
            'rw_id' => 'required|exists:rw,id',
            'rt_id' => 'required|exists:rt,id',
            'status_dalam_keluarga' => 'nullable|string|max:50',
            'status_kependudukan' => 'nullable|string|max:50',
        ];

        // Jika password diisi, email wajib diisi untuk kredensial akun
        if ($request->filled('password')) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email|unique:warga,email';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $request, $user) {
            $wargaData = $validated;
            unset($wargaData['password'], $wargaData['password_confirmation'], $wargaData['username']);

            $warga = Warga::create($wargaData);

            // Buat akun login jika password dan email disediakan
            if ($request->filled('password') && ! empty($validated['email'])) {
                $roleWarga = Role::where('name', 'warga')->firstOrFail();
                $rt = Rt::with('rw')->find($validated['rt_id']);
                $desaId = $rt?->rw?->desa_id ?? $user->desa_id;

                User::create([
                    'name' => $validated['nama_lengkap'],
                    'email' => $validated['email'],
                    'nomor_telepon' => $validated['nomor_telepon'] ?? null,
                    'username' => $request->filled('username') ? $validated['username'] : null,
                    'password' => Hash::make($validated['password']),
                    'role_id' => $roleWarga->id,
                    'desa_id' => $desaId,
                    'rw_id' => $validated['rw_id'],
                    'rt_id' => $validated['rt_id'],
                    'is_active' => true,
                ]);
            }
        });

        VillageStatistikService::clearStatsCache();

        return redirect()->route('warga.index')->with('success', 'Data warga berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail data warga.
     */
    public function show(Warga $warga): View
    {
        $this->authorize('view', $warga);

        return view('warga.show', compact('warga'));
    }

    /**
     * Tampilkan form untuk mengubah data warga.
     */
    public function edit(Warga $warga): View
    {
        $this->authorize('update', $warga);

        $rws = $this->getAccessibleRws();
        $rts = $this->getAccessibleRts();

        return view('warga.edit', compact('warga', 'rws', 'rts'));
    }

    /**
     * Perbarui data warga di database.
     */
    public function update(Request $request, Warga $warga): RedirectResponse
    {
        $this->authorize('update', $warga);

        $validated = $request->validate([
            'nik' => 'required|string|digits:16|unique:warga,nik,'.$warga->id,
            'nomor_kk' => 'required|string|digits:16',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'nullable|string|max:50',
            'status_perkawinan' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'rw_id' => 'nullable|exists:rw,id',
            'rt_id' => 'nullable|exists:rt,id',
            'status_dalam_keluarga' => 'nullable|string|max:50',
            'status_kependudukan' => 'nullable|string|max:50',
        ]);

        $warga->update($validated);

        // Sinkronisasi nomor telepon ke akun user jika warga memiliki akun terhubung
        if ($warga->user && array_key_exists('nomor_telepon', $validated)) {
            $warga->user->update(['nomor_telepon' => $validated['nomor_telepon']]);
        }

        VillageStatistikService::clearStatsCache();

        return redirect()->route('warga.index')->with('success', 'Data warga berhasil diperbarui.');
    }

    /**
     * Hapus data warga beserta akun login yang terhubung dari database.
     *
     * Dibungkus dalam transaksi untuk memastikan atomisitas: jika salah satu
     * operasi gagal, seluruh operasi dibatalkan agar tidak ada data setengah-
     * terhapus yang dapat menyebabkan inkonsistensi statistik (termasuk KK).
     */
    public function destroy(Warga $warga): RedirectResponse
    {
        $this->authorize('delete', $warga);

        DB::transaction(function () use ($warga) {
            // Hapus akun login yang terhubung (relasi via email) agar tidak ada
            // orphaned user yang masih bisa login setelah data warga dihapus.
            if ($warga->user) {
                $warga->user->delete();
            }

            $warga->delete();
        });

        VillageStatistikService::clearStatsCache();

        return redirect()->route('warga.index')->with('success', 'Data warga berhasil dihapus.');
    }
}
