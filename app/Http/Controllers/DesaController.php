<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mengelola data profil Desa.
 *
 * Hanya dapat diakses oleh super_admin melalui middleware
 * role di routes dan DesaPolicy untuk otorisasi resource.
 */
class DesaController extends Controller
{
    /**
     * Tampilkan halaman profil desa.
     *
     * Jika belum ada data desa, redirect ke halaman create.
     */
    public function index(): View|RedirectResponse
    {
        $desa = Desa::first();

        if (! $desa) {
            return redirect()->route('desa.create');
        }

        return view('desa.show', compact('desa'));
    }

    /**
     * Tampilkan form untuk membuat profil desa baru.
     */
    public function create(): View
    {
        return view('desa.create');
    }

    /**
     * Simpan profil desa baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kabupaten_kota' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'kode_pos' => 'nullable|string|max:10',
            'kepala_desa' => 'nullable|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        Desa::create($validated);

        return redirect()->route('desa.index')->with('success', 'Profil Desa berhasil disimpan.');
    }

    /**
     * Tampilkan detail profil desa.
     */
    public function show(Desa $desa): View
    {
        return view('desa.show', compact('desa'));
    }

    /**
     * Tampilkan form untuk mengubah profil desa.
     */
    public function edit(Desa $desa): View
    {
        return view('desa.edit', compact('desa'));
    }

    /**
     * Perbarui data profil desa di database.
     */
    public function update(Request $request, Desa $desa): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kabupaten_kota' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'kode_pos' => 'nullable|string|max:10',
            'kepala_desa' => 'nullable|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        $desa->update($validated);

        return redirect()->route('desa.index')->with('success', 'Profil Desa berhasil diperbarui.');
    }

    /**
     * Hapus data profil desa dari database.
     */
    public function destroy(Desa $desa): RedirectResponse
    {
        $desa->delete();

        return redirect()->route('desa.index')->with('success', 'Profil Desa berhasil dihapus.');
    }
}
