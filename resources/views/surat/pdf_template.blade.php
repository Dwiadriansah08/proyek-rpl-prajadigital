<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; margin: 2cm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mt-2 { margin-top: 10px; }
        .mt-4 { margin-top: 20px; }
        .mt-8 { margin-top: 40px; }
        .border-bottom { border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 5px; }
        .border-bottom-thin { border-bottom: 1px solid #000; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 3px 0; }
        .label { width: 30%; }
        .colon { width: 5%; text-align: center; }
        .value { width: 65%; }
        .signature-box { width: 40%; float: right; text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="text-center border-bottom">
        <h2 style="margin: 0;">RUKUN WARGA (RW) {{ $surat->warga->rw->nomor_rw }}</h2>
        <h3 style="margin: 0;">RUKUN TETANGGA (RT) {{ $surat->warga->rt->nomor_rt }}</h3>
        <p style="margin: 5px 0;">{{ $surat->warga->rt->alamat ?? 'Desa Sukamaju, Kecamatan Makmur' }}</p>
    </div>
    <div class="border-bottom-thin"></div>

    <div class="text-center mt-4">
        <div class="font-bold" style="text-decoration: underline; font-size: 14pt; text-transform: uppercase;">{{ $surat->template->nama_surat }}</div>
        <div>Nomor: {{ $surat->nomor_surat ?? '470/___/RW.' . $surat->warga->rw->nomor_rw . '/' . date('Y') }}</div>
    </div>

    <div class="mt-4">
        <p>Yang bertanda tangan di bawah ini, Ketua RT {{ $surat->warga->rt->nomor_rt }} / RW {{ $surat->warga->rw->nomor_rw }}, Desa Sukamaju, menerangkan dengan sesungguhnya bahwa:</p>
        
        <table class="mt-2">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value font-bold">{{ $surat->warga->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->warga->nik }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tgl Lahir</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->warga->tempat_lahir }}, {{ $surat->warga->tanggal_lahir ? \Carbon\Carbon::parse($surat->warga->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kelamin</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->warga->jenis_kelamin }}</td>
            </tr>
            <tr>
                <td class="label">Agama</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->warga->agama }}</td>
            </tr>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->warga->pekerjaan }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $surat->warga->alamat ?? '-' }}</td>
            </tr>
        </table>
        
        <p class="mt-4">Orang tersebut di atas benar-benar warga dan penduduk kami yang berdomisili di alamat tersebut. Surat keterangan ini dibuat untuk keperluan:</p>
        
        <div class="mt-2 font-bold text-center border" style="padding: 10px; border: 1px solid #ccc;">
            "{{ $surat->data_tambahan['keperluan'] ?? 'Keperluan Umum' }}"
        </div>
        
        <p class="mt-4">Demikian surat keterangan ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature-box">
        <div>Sukamaju, {{ \Carbon\Carbon::parse($surat->approved_at ?? now())->translatedFormat('d F Y') }}</div>
        <div>Ketua RW {{ $surat->warga->rw->nomor_rw }}</div>
        <br><br><br><br>
        <div class="font-bold" style="text-decoration: underline;">{{ $surat->approver?->name ?? '....................................' }}</div>
    </div>
    <div style="clear: both;"></div>
</body>
</html>
