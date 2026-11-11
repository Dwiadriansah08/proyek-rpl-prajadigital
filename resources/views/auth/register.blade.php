<x-guest-layout>
    <div>
        <h2 class="auth-form-title">Registrasi Akun Warga</h2>
        <p class="auth-form-subtitle">Lengkapi identitas kependudukan untuk aktivasi akun portal Anda</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <x-input-label for="name" :value="__('Nama Lengkap (Sesuai KTP)')" />
            <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Contoh: Budi Santoso" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <x-input-label for="email" :value="__('Alamat Email')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="budi@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- NIK & Nomor KK in 2 columns -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <x-input-label for="nik" :value="__('NIK (16 Digit)')" />
                <x-text-input id="nik" class="block w-full" type="text" name="nik" :value="old('nik')" required maxlength="16" placeholder="3201..." />
                <x-input-error :messages="$errors->get('nik')" class="mt-1" />
            </div>
            <div class="col-6">
                <x-input-label for="nomor_kk" :value="__('No. Kartu Keluarga')" />
                <x-text-input id="nomor_kk" class="block w-full" type="text" name="nomor_kk" :value="old('nomor_kk')" required maxlength="16" placeholder="3201..." />
                <x-input-error :messages="$errors->get('nomor_kk')" class="mt-1" />
            </div>
        </div>

        <!-- RW & RT dropdowns side by side -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <x-input-label for="rw_id" :value="__('Wilayah RW')" />
                <select id="rw_id" name="rw_id" class="form-select" required>
                    <option value="">Pilih RW</option>
                    @foreach($rws as $rw)
                        <option value="{{ $rw->id }}" {{ old('rw_id') == $rw->id ? 'selected' : '' }}>RW {{ $rw->nomor_rw }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('rw_id')" class="mt-1" />
            </div>
            <div class="col-6">
                <x-input-label for="rt_id" :value="__('Wilayah RT')" />
                <select id="rt_id" name="rt_id" class="form-select" required>
                    <option value="">Pilih RT</option>
                    @foreach($rts as $rt)
                        <option value="{{ $rt->id }}" {{ old('rt_id') == $rt->id ? 'selected' : '' }}>RT {{ $rt->nomor_rt }} (RW {{ $rt->rw->nomor_rw ?? '' }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('rt_id')" class="mt-1" />
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <x-input-label for="password" :value="__('Kata Sandi')" />
            <div class="position-relative">
                <x-text-input id="password" class="block w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Ulangi Kata Sandi')" />
            <div class="position-relative">
                <x-text-input id="password_confirmation" class="block w-full pr-10"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
                <button type="button" class="toggle-password" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div>
            <x-primary-button class="w-full justify-center py-3">
                {{ __('Daftar Akun Warga') }} <i class="bi bi-check2-circle ms-2"></i>
            </x-primary-button>
        </div>

        <div class="mt-4 pt-3 text-center border-top border-slate-100">
            <span class="text-sm text-slate-500" style="font-size: 0.85rem;">Sudah memiliki akun?</span>
            <a class="text-sm font-bold text-indigo-600 hover:text-indigo-700 ms-1 text-decoration-none" style="color: #4F46E5;" href="{{ route('login') }}">
                {{ __('Masuk') }}
            </a>
        </div>
    </form>
</x-guest-layout>
