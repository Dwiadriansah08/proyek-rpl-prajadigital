<?php

namespace App\Http\Controllers;

use App\Http\Traits\FiltersByWilayah;
use App\Models\Role;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Controller untuk mengelola data Rukun Tetangga (RT).
 *
 * Diakses oleh super_admin, rw (RT di bawah RW-nya), dan rt (RT miliknya)
 * melalui middleware role di routes dan RtPolicy untuk otorisasi resource.
 */
class RtController extends Controller
{
    use FiltersByWilayah;

    /**
     * Tampilkan daftar RT dengan filter wilayah dan pencarian.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Rt::with('rw')->withCount('wargas');

        if ($user->hasRole('rw')) {
            $query->where('rw_id', $user->rw_id);
        }

        if ($request->rw_id) {
            $query->where('rw_id', $request->rw_id);
        }

        if ($request->search) {
            $query->where('nomor_rt', 'like', '%'.$request->search.'%')
                ->orWhere('ketua_rt', 'like', '%'.$request->search.'%');
        }

        $rts = $query->paginate(15);

        // Untuk dropdown filter RW (hanya super_admin yang butuh ini)
        $rwList = collect();
        if ($user->hasRole('super_admin')) {
            $rwList = $this->getAccessibleRws();
        }

        return view('rt.index', compact('rts', 'rwList'));
    }

    /**
     * Tampilkan form untuk membuat RT baru.
     */
    public function create(): View
    {
        $this->authorize('create', Rt::class);

        $rws = $this->getAccessibleRws();

        return view('rt.create', compact('rws'));
    }

    /**
     * Simpan data RT baru beserta akun login ketua RT ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Rt::class);

        $rules = [
            'rw_id' => 'required|exists:rw,id',
            'nomor_rt' => 'required|string|max:10',
            'ketua_rt' => 'required|string|max:255',
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
            $rt = Rt::create([
                'rw_id' => $validated['rw_id'],
                'nomor_rt' => $validated['nomor_rt'],
                'ketua_rt' => $validated['ketua_rt'],
                'kontak' => $validated['kontak'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);

            if ($request->filled('password') && ! empty($validated['email'])) {
                $roleRt = Role::where('name', 'rt')->firstOrFail();
                $rw = $rt->rw;

                User::create([
                    'name' => $validated['ketua_rt'],
                    'email' => $validated['email'],
                    'username' => $request->filled('username') ? $validated['username'] : null,
                    'password' => Hash::make($validated['password']),
                    'role_id' => $roleRt->id,
                    'desa_id' => $rw?->desa_id,
                    'rw_id' => $rt->rw_id,
                    'rt_id' => $rt->id,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('rt.index')->with('success', 'Data RT dan akun login berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail RT beserta relasi RW-nya.
     */
    public function show(Rt $rt): View
    {
        $this->authorize('view', $rt);

        $rt->load('rw');

        return view('rt.show', compact('rt'));
    }

    /**
     * Tampilkan form untuk mengubah data RT.
     */
    public function edit(Rt $rt): View
    {
        $this->authorize('update', $rt);

        $rws = $this->getAccessibleRws();

        return view('rt.edit', compact('rt', 'rws'));
    }

    /**
     * Perbarui data RT di database.
     */
    public function update(Request $request, Rt $rt): RedirectResponse
    {
        $this->authorize('update', $rt);

        $validated = $request->validate([
            'rw_id' => 'required|exists:rw,id',
            'nomor_rt' => 'required|string|max:10',
            'ketua_rt' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ]);

        $rt->update($validated);

        return redirect()->route('rt.index')->with('success', 'Data RT berhasil diperbarui.');
    }

    /**
     * Hapus data RT dari database.
     */
    public function destroy(Rt $rt): RedirectResponse
    {
        $this->authorize('delete', $rt);

        if ($rt->warga()->count() > 0) {
            return redirect()->route('rt.index')->with('error', 'Tidak dapat menghapus RT karena masih memiliki data Warga terkait.');
        }

        $rt->delete();

        return redirect()->route('rt.index')->with('success', 'Data RT berhasil dihapus.');
    }
}
