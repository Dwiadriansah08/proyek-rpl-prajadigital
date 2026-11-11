<?php

namespace App\Http\Controllers;

use App\Http\Traits\FiltersByWilayah;
use App\Models\Keluarga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mengelola data Keluarga (Kartu Keluarga).
 *
 * Menampilkan data keluarga sesuai wilayah user:
 * - super_admin: semua keluarga
 * - rw: keluarga di RW-nya
 * - rt: keluarga di RT-nya
 */
class KeluargaController extends Controller
{
    use FiltersByWilayah;

    /**
     * Tampilkan daftar keluarga dengan filter wilayah dan pencarian.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Keluarga::with(['rw', 'rt']);

        if ($user->hasRole('rw')) {
            $query->where('rw_id', $user->rw_id);
        } elseif ($user->hasRole('rt')) {
            $query->where('rt_id', $user->rt_id);
        }

        if ($request->search) {
            $query->where('nomor_kk', 'like', '%'.$request->search.'%')
                ->orWhere('kepala_keluarga', 'like', '%'.$request->search.'%');
        }

        $keluargas = $query->paginate(15);

        return view('keluarga.index', compact('keluargas'));
    }

    /**
     * Tampilkan form untuk membuat data keluarga baru.
     */
    public function create(): View
    {
        $rws = $this->getAccessibleRws();
        $rts = $this->getAccessibleRts();

        return view('keluarga.create', compact('rws', 'rts'));
    }

    /**
     * Simpan data keluarga baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_kk' => 'required|string|digits:16|unique:keluarga',
            'kepala_keluarga' => 'required|string|max:255',
            'alamat' => 'required|string',
            'rw_id' => 'required|exists:rw,id',
            'rt_id' => 'required|exists:rt,id',
        ]);

        Keluarga::create($validated);

        return redirect()->route('keluarga.index')->with('success', 'Data Keluarga berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail keluarga beserta anggotanya.
     */
    public function show(Keluarga $keluarga): View
    {
        $this->authorize('view', $keluarga);

        $keluarga->load(['rw', 'rt', 'members']);

        return view('keluarga.show', compact('keluarga'));
    }

    /**
     * Tampilkan form untuk mengubah data keluarga.
     */
    public function edit(Keluarga $keluarga): View
    {
        $this->authorize('update', $keluarga);

        $rws = $this->getAccessibleRws();
        $rts = $this->getAccessibleRts();

        return view('keluarga.edit', compact('keluarga', 'rws', 'rts'));
    }

    /**
     * Perbarui data keluarga di database.
     */
    public function update(Request $request, Keluarga $keluarga): RedirectResponse
    {
        $this->authorize('update', $keluarga);

        $validated = $request->validate([
            'nomor_kk' => 'required|string|digits:16|unique:keluarga,nomor_kk,'.$keluarga->id,
            'kepala_keluarga' => 'required|string|max:255',
            'alamat' => 'required|string',
        ]);

        $keluarga->update($validated);

        return redirect()->route('keluarga.index')->with('success', 'Data Keluarga berhasil diperbarui.');
    }

    /**
     * Hapus data keluarga dari database.
     */
    public function destroy(Keluarga $keluarga): RedirectResponse
    {
        $this->authorize('delete', $keluarga);

        $keluarga->delete();

        return redirect()->route('keluarga.index')->with('success', 'Data Keluarga berhasil dihapus.');
    }
}
