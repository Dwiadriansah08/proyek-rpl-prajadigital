<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Keterangan Kematian</title>
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
            margin: 5px 0;
            text-align: justify;
            text-indent: 28px;
        }
        .content p.no-indent {
            text-indent: 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 8px 15px;
        }
        .data-table td {
            padding: 2.5px 0;
            vertical-align: top;
            font-size: 11.5pt;
        }
        .data-table .label {
            width: 32%;
        }
        .data-table .colon {
            width: 4%;
            text-align: center;
        }
        .data-table .value {
            width: 64%;
        }
        .highlight-box {
            border: 1px dashed #444;
            background-color: #fcfcfc;
            padding: 8px 12px;
            margin: 6px 0;
            font-size: 11pt;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 22px;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
            font-size: 11pt;
        }
        .signature-space {
            height: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .footer-note {
            margin-top: 20px;
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

        $namaAlmarhum = $data['nama_almarhum'] ?? ($data['nama_jenazah'] ?? $warga->nama_lengkap);
        $nikAlmarhum = $data['nik_almarhum'] ?? $warga->nik;
        $tempatMeninggal = $data['tempat_meninggal'] ?? 'Kediaman / Rumah Sakit';
        $tglMeninggal = !empty($data['tanggal_meninggal']) ? \Carbon\Carbon::parse($data['tanggal_meninggal'])->translatedFormat('l, d F Y') : '-';
        $sebabMeninggal = $data['sebab_kematian'] ?? ($data['penyebab'] ?? 'Sakit Biasa / Usia Lanjut');
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
        <div class="title">SURAT KETERANGAN KEMATIAN</div>
        <div class="nomor">Nomor: {{ $surat->nomor_surat ?? ('470/'.sprintf('%03d', $surat->id).'/KM/'.($rw?->nomor_rw ?? '001').'/'.date('Y')) }}</div>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="content">
        <p class="no-indent">
            Yang bertanda tangan di bawah ini Pengurus RT {{ $rt?->nomor_rt ?? '-' }} / RW {{ $rw?->nomor_rw ?? '-' }}, 
            {{ $desa?->nama ?? 'Desa Sukamaju' }}, Kecamatan {{ $desa?->kecamatan ?? 'Makmur' }}, menerangkan bahwa:
        </p>

        <table class="data-table">
            <tr>
                <td class="label">Nama Almarhum/Almarhumah</td>
                <td class="colon">:</td>
                <td class="value"><strong>{{ strtoupper($namaAlmarhum) }}</strong></td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">{{ $nikAlmarhum }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kelamin</td>
                <td class="colon">:</td>
                <td class="value">{{ $data['jenis_kelamin_almarhum'] ?? $warga->jenis_kelamin }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Terakhir</td>
                <td class="colon">:</td>
                <td class="value">{{ $data['alamat_almarhum'] ?? ($warga->alamat ?? 'Lingkungan RT '.$rt?->nomor_rt.' RW '.$rw?->nomor_rw) }}</td>
            </tr>
        </table>

        <p class="no-indent" style="margin-top: 8px;">
            Telah <strong>MENINGGAL DUNIA</strong> pada:
        </p>

        <table class="data-table">
            <tr>
                <td class="label">Hari, Tanggal Meninggal</td>
                <td class="colon">:</td>
                <td class="value"><strong>{{ $tglMeninggal }}</strong></td>
            </tr>
            @if(!empty($data['pukul_meninggal']))
            <tr>
                <td class="label">Pukul / Waktu</td>
                <td class="colon">:</td>
                <td class="value">{{ $data['pukul_meninggal'] }} WIB</td>
            </tr>
            @endif
            <tr>
                <td class="label">Tempat Kematian</td>
                <td class="colon">:</td>
                <td class="value">{{ $tempatMeninggal }}</td>
            </tr>
            <tr>
                <td class="label">Penyebab Kematian</td>
                <td class="colon">:</td>
                <td class="value">{{ $sebabMeninggal }}</td>
            </tr>
            @if(!empty($data['tempat_pemakaman']))
            <tr>
                <td class="label">Tempat Pemakaman</td>
                <td class="colon">:</td>
                <td class="value">{{ $data['tempat_pemakaman'] }}</td>
            </tr>
            @endif
        </table>

        <p class="no-indent" style="margin-top: 6px;">
            Surat keterangan kematian ini diterbitkan atas laporan dan permohonan dari pihak keluarga/pelapor:
        </p>
        <table class="data-table">
            <tr>
                <td class="label">Nama Pelapor</td>
                <td class="colon">:</td>
                <td class="value">{{ $warga->nama_lengkap }} (NIK: {{ $warga->nik }})</td>
            </tr>
            <tr>
                <td class="label">Hubungan dengan Jenazah</td>
                <td class="colon">:</td>
                <td class="value">{{ $data['hubungan_pelapor'] ?? 'Keluarga / Ahli Waris' }}</td>
            </tr>
        </table>

        <div class="highlight-box">
            <strong>Keperluan Dokumen:</strong> {{ $data['keperluan'] ?? 'Pengurusan Akta Kematian Disdukcapil / Administrasi Perbankan / Ahli Waris' }}
        </div>

        <p>
            Demikian surat keterangan kematian ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagai kelengkapan administrasi kematian bagi instansi yang berwenang.
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
        * Dokumen resmi diterbitkan melalui Sistem Administrasi Layanan Desa PrajaDigital | ID Pengajuan: #{{ $surat->id }} | Dokumen ini merupakan pengantar resmi untuk pengurusan Akta Kematian di Disdukcapil.
    </div>
</body>
</html>
