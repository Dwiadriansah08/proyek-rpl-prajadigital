<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $rws = Rw::all();
        $rts = Rt::all();

        return view('auth.register', compact('rws', 'rts'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nik' => ['required', 'string', 'max:16', 'unique:warga'],
            'nomor_kk' => ['required', 'string', 'max:16'],
            'rw_id' => ['required', 'exists:rw,id'],
            'rt_id' => ['required', 'exists:rt,id'],
        ]);

        $roleWarga = Role::where('name', 'warga')->first();
        $rt = Rt::find($request->rt_id);
        $rw = Rw::find($request->rw_id);
        $desaId = $rw ? $rw->desa_id : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleWarga ? $roleWarga->id : null,
            'desa_id' => $desaId,
            'rw_id' => $request->rw_id,
            'rt_id' => $request->rt_id,
        ]);

        // Create Warga record automatically
        Warga::create([
            'nik' => $request->nik,
            'nomor_kk' => $request->nomor_kk,
            'nama_lengkap' => $request->name,
            'email' => $request->email,
            'rw_id' => $request->rw_id,
            'rt_id' => $request->rt_id,
            'tempat_lahir' => '-',
            'tanggal_lahir' => now()->toDateString(),
            'jenis_kelamin' => 'Laki-laki',
            'agama' => '-',
            'status_perkawinan' => '-',
            'pendidikan' => '-',
            'pekerjaan' => '-',
            'status_dalam_keluarga' => 'Anggota Keluarga',
            'status_kependudukan' => 'Tetap',
            'alamat' => $rt ? $rt->alamat : '-',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
