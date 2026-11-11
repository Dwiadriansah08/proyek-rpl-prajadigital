<x-app-layout>
    <x-slot name="header">
        Manajemen Pengguna & Penduduk
    </x-slot>

    <div class="card border-0 p-4" style="border-radius: 20px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
            <div>
                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">Direktori Terpadu Pengguna & Data Penduduk</h5>
                <p class="text-muted small mb-0">Kelola akun sistem, hak akses role, serta identitas kependudukan warga desa dalam satu tempat</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('warga.create') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3 py-2" style="border-radius: 12px;">
                    <i class="bi bi-person-plus-fill"></i> Tambah Data Penduduk
                </a>
                <a href="{{ route('users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 shadow-sm" style="border-radius: 12px;">
                    <i class="bi bi-shield-lock-fill"></i> Tambah User Baru
                </a>
            </div>
        </div>

        <form action="{{ route('users.index') }}" method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama, NIK, No. KK, email, atau username..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ strtoupper($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100 fw-semibold" type="submit">Filter Data</button>
                </div>
                @if(request('search') || request('role'))
                <div class="col-md-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Identitas Pengguna / Penduduk</th>
                        <th>Username</th>
                        <th>NIK &amp; No. KK</th>
                        <th>Wilayah &amp; Alamat</th>
                        <th>Email &amp; Status</th>
                        <th>Role</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $warga = $user->warga;
                        $rwNomor = $warga?->rw?->nomor_rw ?? ($user->rw?->nomor_rw ?? '-');
                        $rtNomor = $warga?->rt?->nomor_rt ?? ($user->rt?->nomor_rt ?? '-');
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 fw-bold" style="width: 38px; height: 38px; background: #EEF2FF; color: #4F46E5; font-size: 0.88rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    @if($warga && $warga->nama_lengkap !== $user->name)
                                        <div class="text-muted small" style="font-size: 0.75rem;">(Nama Warga: {{ $warga->nama_lengkap }})</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->username)
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.78rem;">{{ '@' . $user->username }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($warga)
                                <div><span class="badge font-monospace" style="background: #F1F5F9; color: #1E293B; font-size: 0.78rem;">NIK: {{ $warga->nik }}</span></div>
                                <div class="mt-1"><span class="badge font-monospace" style="background: #F8FAFC; color: #475569; font-size: 0.75rem;">KK: {{ $warga->nomor_kk ?? '-' }}</span></div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.75rem;">Bukan Profil Warga</span>
                            @endif
                        </td>
                        <td>
                            <div>
                                <span class="badge" style="background: #EEF2FF; color: #4338CA; border: 1px solid #C7D2FE;">RW {{ $rwNomor }} / RT {{ $rtNomor }}</span>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 0.78rem; max-width: 220px;">
                                {{ Str::limit($warga?->alamat ?? ($user->alamat ?? 'Alamat wilayah default'), 35) }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark" style="font-size: 0.84rem;">{{ $user->email }}</div>
                            @php
                                $displayPhone = $user->nomor_telepon ?? $warga?->nomor_telepon;
                            @endphp
                            @if($displayPhone)
                                <div class="text-muted small mt-0.5 d-flex align-items-center gap-1" style="font-size: 0.76rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone text-success"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span class="font-monospace">{{ $displayPhone }}</span>
                                </div>
                            @endif
                            <div class="mt-1">
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.72rem;"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeClass = 'bg-secondary';
                                if($user->hasRole('super_admin')) $badgeClass = 'bg-danger';
                                elseif($user->hasRole('rw')) $badgeClass = 'bg-primary';
                                elseif($user->hasRole('rt')) $badgeClass = 'bg-info text-dark';
                                elseif($user->hasRole('warga')) $badgeClass = 'bg-success';
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 0.75rem;">{{ strtoupper($user->role->name ?? 'GUEST') }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-light p-1 px-2 text-info" title="Detail Pengguna & Penduduk" style="border-radius: 8px;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-light p-1 px-2 text-primary" title="Edit Akun" style="border-radius: 8px;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($warga)
                                <a href="{{ route('warga.edit', $warga->id) }}" class="btn btn-sm btn-light p-1 px-2 text-warning" title="Edit Data Penduduk" style="border-radius: 8px;">
                                    <i class="bi bi-person-gear"></i>
                                </a>
                                @endif
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-light p-1 px-2 text-danger" title="Hapus User" style="border-radius: 8px;"
                                        data-confirm-delete
                                        data-delete-title="Hapus Pengguna Sistem"
                                        data-delete-message="Apakah Anda yakin ingin menghapus akun pengguna ini? Akun tidak akan dapat login lagi ke sistem."
                                        data-delete-detail="<strong>Nama:</strong> {{ $user->name }}<br><strong>Email:</strong> {{ $user->email }}<br><strong>Role:</strong> {{ strtoupper($user->role?->name ?? 'GUEST') }}">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block opacity-40 mb-2"></i>
                            <div class="fw-semibold">Tidak ada data pengguna atau penduduk yang cocok.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 pt-3 border-top">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
