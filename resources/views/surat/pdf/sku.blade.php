<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Keterangan Usaha</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm 2.5cm 2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.45;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-col {
            width: 80px;
            text-align: left;
        }
        .logo-box {
            width: 70px;
            height: 70px;
            border: 2px solid #333;
            border-radius: 50%;
            text-align: center;
            line-height: 70px;
            font-weight: bold;
            font-size: 11pt;
            margin-right: 15px;
            letter-spacing: 1px;
        }
        .instansi-col {
            text-align: center;
        }
        .instansi-col h4 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .instansi-col h3 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .instansi-col h2 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .instansi-col p {
            margin: 2px 0 0 0;
            font-size: 10pt;
            font-style: italic;
        }
        .double-line {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin-top: 5px;
            margin-bottom: 18px;
        }
        .title-box {
            text-align: center;
            margin-bottom: 18px;
        }
        .title-box .title {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-box .nomor {
            font-size: 11pt;
            margin-top: 3px;
        }
        .content p {
            margin: 6px 0;
            text-align: justify;
            text-indent: 28px;
        }
        .content p.no-indent {
            text-indent: 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px 15px;
        }
        .data-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11.5pt;
        }
        .data-table .label {
            width: 30%;
        }
        .data-table .colon {
            width: 4%;
            text-align: center;
        }
        .data-table .value {
            width: 66%;
        }
        .highlight-box {
            border: 1px dashed #444;
            background-color: #fcfcfc;
            padding: 8px 12px;
            margin: 8px 0;
            font-size: 11pt;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
            font-size: 11pt;
        }
        .signature-space {
            height: 65px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .footer-note {
            margin-top: 25px;
            font-size: 8.5pt;
            color: #666;
            border-top: 0.5px dotted #999;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $warga = $surat->warga;
        $rw = $warga->rw;
        $rt = $warga->rt;
        $desa = $rw?->desa;
        $data = is_array($surat->data_tambahan) ? $surat->data_tambahan : [];
        $tglApproved = $surat->approved_at ? \Carbon\Carbon::parse($surat->approved_at) : now();

        $namaUsaha = $data['nama_usaha'] ?? 'Usaha Perdagangan / Jasa';
        $bidangUsaha = $data['bidang_usaha'] ?? ($data['jenis_usaha'] ?? 'Perdagangan Mikro & Kecil');
        $alamatUsaha = $data['alamat_usaha'] ?? ($warga->alamat ?? 'Lingkungan RT '.$rt?->nomor_rt.' RW '.$rw?->nomor_rw);
        $sejakTahun = $data['sejak_tahun'] ?? ($data['lama_usaha'] ?? date('Y'));
    @endphp

    {{-- KOP SURAT RESMI --}}
    <table class="header-table">
        <tr>
            <td class="logo-col">
                <div class="logo-box">DESA</div>
            </td>
            <td class="instansi-col">
                <h4>PEMERINTAH KABUPATEN {{ strtoupper($desa?->kabupaten_kota ?? 'BANDUNG') }}</h4>
                <h3>KECAMATAN {{ strtoupper($desa?->kecamatan ?? 'MAKMUR') }}</h3>
                <h2>{{ strtoupper($desa?->nama ?? 'DESA SUKAMAJU') }}</h2>
                <h3>RUKUN WARGA {{ $rw?->nomor_rw ?? '001' }} / RUKUN TETANGGA {{ $rt?->nomor_rt ?? '001' }}</h3>
                <p>{{ $desa?->alamat ?? 'Jl. Merdeka No. 1' }} | Kode Pos: {{ $desa?->kode_pos ?? '40111' }}</p>
            </td>
        </tr>
    </table>
    <div class="double-line"></div>

    {{-- JUDUL SURAT --}}
    <div class="title-box">
        <div class="title">SURAT KETERANGAN USAHA (SKU)</div>
        <div class="nomor">Nomor: {{ $surat->nomor_surat ?? ('470/'.sprintf('%03d', $surat->id).'/SKU/'.($rw?->nomor_rw ?? '001').'/'.date('Y')) }}</div>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="content">
        <p class="no-indent">
            Yang bertanda tangan di bawah ini, Pengurus RT {{ $rt?->nomor_rt ?? '-' }} / RW {{ $rw?->nomor_rw ?? '-' }} 
            {{ $desa?->nama ?? 'Desa Sukamaju' }}, Kecamatan {{ $desa?->kecamatan ?? 'Makmur' }}, menerangkan dengan sebenarnya bahwa:
        </p>

        <table class="data-table">
            <tr>
                <td class="label">Nama Lengkap Pemilik</td>
                <td class="colon">:</td>
                <td class="value"><strong>{{ strtoupper($warga->nama_lengkap) }}</strong></td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">{{ $warga->nik }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tanggal Lahir</td>
                <td class="colon">:</td>
                <td class="value">{{ $warga->tempat_lahir }}, {{ $warga->tanggal_lahir ? \Carbon\Carbon::parse($warga->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kelamin / Agama</td>
                <td class="colon">:</td>
                <td class="value">{{ $warga->jenis_kelamin }} / {{ $warga->agama }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Tinggal Pemilik</td>
                <td class="colon">:</td>
                <td class="value">{{ $warga->alamat ?? '-' }}, RT {{ $rt?->nomor_rt ?? '-' }} / RW {{ $rw?->nomor_rw ?? '-' }}</td>
            </tr>
        </table>

        <p class="no-indent" style="margin-top: 10px;">
            Bahwa orang yang namanya tersebut di atas adalah benar-benar penduduk kami yang sampai saat ini memiliki dan menjalankan kegiatan usaha sebagai berikut:
        </p>

        <table class="data-table">
            <tr>
                <td class="label">Nama Usaha / Toko</td>
                <td class="colon">:</td>
                <td class="value font-bold"><strong>{{ strtoupper($namaUsaha) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Bidang / Jenis Usaha</td>
                <td class="colon">:</td>
                <td class="value">{{ $bidangUsaha }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Lokasi Usaha</td>
                <td class="colon">:</td>
                <td class="value">{{ $alamatUsaha }}</td>
            </tr>
            <tr>
                <td class="label">Berjalan Sejak</td>
                <td class="colon">:</td>
                <td class="value">Tahun {{ $sejakTahun }}</td>
            </tr>
        </table>

        <p>
            Surat Keterangan Usaha ini diberikan kepada yang bersangkutan untuk keperluan:
        </p>

        <div class="highlight-box">
            <strong>Keperluan:</strong> {{ $data['keperluan'] ?? 'Kelengkapan Permohonan Pengajuan Modal Usaha / KUR Perbankan / Izin Usaha Resmi' }}
        </div>

        <p>
            Demikian surat keterangan usaha ini dibuat dengan sebenarnya dan dapat dipergunakan sebagaimana mestinya oleh instansi atau pihak yang berkepentingan.
        </p>
    </div>

    {{-- AREA TANDA TANGAN --}}
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                Ketua RT {{ $rt?->nomor_rt ?? '001' }}<br>
                <div class="signature-space"></div>
                <div class="signature-name">( {{ $rt?->ketua_rt ?? '....................................' }} )</div>
            </td>
            <td>
                {{ $desa?->nama ?? 'Sukamaju' }}, {{ $tglApproved->translatedFormat('d F Y') }}<br>
                Ketua RW {{ $rw?->nomor_rw ?? '001' }}<br>
                <div class="signature-space"></div>
                <div class="signature-name">( {{ $surat->approver?->name ?? ($rw?->ketua_rw ?? '....................................') }} )</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        * Dokumen resmi diterbitkan melalui Sistem Administrasi Layanan Desa PrajaDigital | ID Pengajuan: #{{ $surat->id }} | SKU ini berlaku selama 1 (satu) tahun sejak tanggal diterbitkan.
    </div>
</body>
</html>
