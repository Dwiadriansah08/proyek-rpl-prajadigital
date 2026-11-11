<x-app-layout>
    <x-slot name="header">
        Edit Data Penduduk
    </x-slot>

    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 font-semibold">Ubah Data Penduduk</h5>
            <a href="{{ route('warga.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
        
        <form action="{{ route('warga.update', $warga->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                    <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $warga->nik) }}" required maxlength="16">
                    @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Nomor KK <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_kk" class="form-control @error('nomor_kk') is-invalid @enderror" value="{{ old('nomor_kk', $warga->nomor_kk) }}" required maxlength="16">
                    @error('nomor_kk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $warga->nama_lengkap) }}" required>
                    @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $warga->tempat_lahir) }}">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $warga->tanggal_lahir) }}">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="Laki-laki" {{ old('jenis_kelamin', $warga->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $warga->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Agama</label>
                    <select name="agama" class="form-select">
                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Kepercayaan Lainnya'] as $agama)
                            <option value="{{ $agama }}" {{ old('agama', $warga->agama) == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Status Perkawinan</label>
                    <select name="status_perkawinan" class="form-select">
                        @foreach(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'] as $status)
                            <option value="{{ $status }}" {{ old('status_perkawinan', $warga->status_perkawinan) == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pendidikan Terakhir</label>
                    <select name="pendidikan" class="form-select @error('pendidikan') is-invalid @enderror">
                        <option value="">-- Pilih Pendidikan --</option>
                        @foreach(['Tidak/Belum Sekolah', 'Belum Tamat SD/Sederajat', 'Tamat SD/Sederajat', 'SLTP/Sederajat', 'SLTA/Sederajat', 'Diploma I/II', 'Akademi/Diploma III/Sarjana Muda', 'Diploma IV/Strata I', 'Strata II', 'Strata III'] as $pend)
                            <option value="{{ $pend }}" {{ old('pendidikan', $warga->pendidikan) == $pend ? 'selected' : '' }}>{{ $pend }}</option>
                        @endforeach
                    </select>
                    @error('pendidikan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" name="pekerjaan" class="form-control @error('pekerjaan') is-invalid @enderror" value="{{ old('pekerjaan', $warga->pekerjaan) }}" placeholder="Contoh: Karyawan Swasta, Pedagang, Petani">
                    @error('pekerjaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $warga->alamat) }}</textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">RW <span class="text-danger">*</span></label>
                    <select name="rw_id" class="form-select @error('rw_id') is-invalid @enderror" required>
                        <option value="">Pilih RW</option>
                        @foreach($rws as $rw)
                            <option value="{{ $rw->id }}" {{ old('rw_id', $warga->rw_id) == $rw->id ? 'selected' : '' }}>RW {{ $rw->nomor_rw }}</option>
                        @endforeach
                    </select>
                    @error('rw_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">RT <span class="text-danger">*</span></label>
                    <select name="rt_id" class="form-select @error('rt_id') is-invalid @enderror" required>
                        <option value="">Pilih RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" {{ old('rt_id', $warga->rt_id) == $rt->id ? 'selected' : '' }}>RT {{ $rt->nomor_rt }} (RW {{ $rt->rw->nomor_rw ?? '' }})</option>
                        @endforeach
                    </select>
                    @error('rt_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Status Hubungan Dalam Keluarga</label>
                    <select name="status_dalam_keluarga" class="form-select">
                        @foreach(['Kepala Keluarga', 'Suami', 'Istri', 'Anak', 'Menantu', 'Cucu', 'Orangtua', 'Mertua', 'Famili Lain', 'Pembantu', 'Lainnya'] as $shdk)
                            <option value="{{ $shdk }}" {{ old('status_dalam_keluarga', $warga->status_dalam_keluarga) == $shdk ? 'selected' : '' }}>{{ $shdk }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Status Kependudukan</label>
                    <select name="status_kependudukan" class="form-select">
                        <option value="Tetap" {{ old('status_kependudukan', $warga->status_kependudukan) == 'Tetap' ? 'selected' : '' }}>Tetap</option>
                        <option value="Sementara/Kontrak" {{ old('status_kependudukan', $warga->status_kependudukan) == 'Sementara/Kontrak' ? 'selected' : '' }}>Sementara/Kontrak</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <a href="{{ route('warga.index') }}" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Update Data</button>
            </div>
        </form>
    </div>
</x-app-layout>
