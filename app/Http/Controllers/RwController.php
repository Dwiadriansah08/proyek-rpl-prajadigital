<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rw;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Controller untuk mengelola data Rukun Warga (RW).
 *
 * Diakses oleh super_admin (pengelolaan penuh) dan rw (lihat wilayahnya)
 * melalui middleware role di routes dan RwPolicy untuk otorisasi resource.
 */
class RwController extends Controller
{
    /**
     * Tampilkan daftar RW dengan fitur pencarian.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Rw::with(['desa', 'rts']);

        if ($user->hasRole('rw') && $user->rw_id) {
            $query->where('id', $user->rw_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_rw', 'like', '%'.$request->search.'%')
                    ->orWhere('ketua_rw', 'like', '%'.$request->search.'%');
            });
        }

        $rws = $query->paginate(15);

        return view('rw.index', compact('rws'));
    }

    /**
     * Tampilkan form untuk membuat RW baru.
     */
    public function create(): View
    {
        $this->authorize('create', Rw::class);

        $desas = Desa::all();

        return view('rw.create', compact('desas'));
    }

    /**
     * Simpan data RW baru beserta akun login ketua RW ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Rw::class);

        $rules = [
            'desa_id' => 'required|exists:desa,id',
            'nomor_rw' => 'required|string|max:10',
            'ketua_rw' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'username' => 'nullable|string|max:50|alpha_dash|unique:users,username',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        if ($request->filled('password')) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $request) {
            $rw = Rw::create([
                'desa_id' => $validated['desa_id'],
                'nomor_rw' => $validated['nomor_rw'],
                'ketua_rw' => $validated['ketua_rw'],
                'kontak' => $validated['kontak'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);

            if ($request->filled('password') && ! empty($validated['email'])) {
                $roleRw = Role::where('name', 'rw')->firstOrFail();

                User::create([
                    'name' => $validated['ketua_rw'],
                    'email' => $validated['email'],
                    'username' => $request->filled('username') ? $validated['username'] : null,
                    'password' => Hash::make($validated['password']),
                    'role_id' => $roleRw->id,
                    'desa_id' => $rw->desa_id,
                    'rw_id' => $rw->id,
                    'rt_id' => null,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('rw.index')->with('success', 'Data RW dan akun login berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail RW beserta daftar RT-nya.
     */
    public function show(Rw $rw): View
    {
        $this->authorize('view', $rw);

        $rw->load(['rts', 'desa']);

        return view('rw.show', compact('rw'));
    }

    /**
     * Tampilkan form untuk mengubah data RW.
     */
    public function edit(Rw $rw): View
    {
        $this->authorize('update', $rw);

        $desas = Desa::all();

        return view('rw.edit', compact('rw', 'desas'));
    }

    /**
     * Perbarui data RW di database.
     */
    public function update(Request $request, Rw $rw): RedirectResponse
    {
        $this->authorize('update', $rw);

        $validated = $request->validate([
            'desa_id' => 'required|exists:desa,id',
            'nomor_rw' => 'required|string|max:10',
            'ketua_rw' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ]);

        $rw->update($validated);

        return redirect()->route('rw.index')->with('success', 'Data RW berhasil diperbarui.');
    }

    /**
     * Hapus data RW dari database.
     */
    public function destroy(Rw $rw): RedirectResponse
    {
        $this->authorize('delete', $rw);

        if ($rw->rt()->count() > 0 || $rw->warga()->count() > 0) {
            return redirect()->route('rw.index')->with('error', 'Tidak dapat menghapus RW karena masih memiliki data RT atau Warga terkait.');
        }

        $rw->delete();

        return redirect()->route('rw.index')->with('success', 'Data RW berhasil dihapus.');
    }
}
