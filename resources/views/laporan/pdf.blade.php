<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekapitulasi Pelayanan Surat - {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #111;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h3 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h2 {
            margin: 2px 0;
            font-size: 16pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 10pt;
            color: #333;
        }
        .title {
            text-align: center;
            margin-bottom: 16px;
        }
        .title h4 {
            margin: 0;
            font-size: 12pt;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .title p {
            margin: 2px 0 0 0;
            font-size: 10pt;
        }
        .summary-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 10px;
            border: 1px solid #999;
            font-size: 10pt;
            text-align: center;
            background-color: #f8f9fa;
        }
        .summary-table td strong {
            display: block;
            font-size: 12pt;
            color: #1a56db;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #666;
            padding: 5px 6px;
        }
        .data-table th {
            background-color: #e5e7eb;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer table {
            width: 100%;
            border: none;
        }
        .footer td {
            border: none;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>PEMERINTAH KABUPATEN {{ strtoupper($desa?->kabupaten_kota ?? 'SEJAHTERA') }}</h3>
        <h3>KECAMATAN {{ strtoupper($desa?->kecamatan ?? 'MAKMUR') }}</h3>
        <h2>KANTOR KEPALA {{ strtoupper($desa?->nama ?? 'DESA SUKAMAJU') }}</h2>
        <p>{{ $desa?->alamat ?? 'Jl. Merdeka No. 1' }} | Kode Pos: {{ $desa?->kode_pos ?? '12345' }}</p>
    </div>

    <div class="title">
        <h4>LAPORAN REKAPITULASI PELAYANAN SURAT WARGA</h4>
        <p>Periode: <strong>{{ strtoupper($namaBulan) }} {{ $tahun }}</strong></p>
    </div>

    <!-- Ringkasan Angka -->
    <table class="summary-table">
        <tr>
            <td>Total Permohonan: <strong>{{ $summary['total'] }}</strong></td>
            <td>Surat Selesai: <strong>{{ $summary['selesai'] }}</strong></td>
            <td>Verifikasi Desa: <strong>{{ $summary['menunggu_desa'] }}</strong></td>
            <td>Menunggu RW: <strong>{{ $summary['menunggu_rw'] }}</strong></td>
            <td>Menunggu RT: <strong>{{ $summary['menunggu_rt'] }}</strong></td>
            <td>Ditolak: <strong style="color: #dc2626;">{{ $summary['ditolak'] }}</strong></td>
        </tr>
    </table>

    <!-- Rincian Data Surat -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 110px;">Nomor Surat</th>
                <th>Nama Pemohon</th>
                <th style="width: 90px;">NIK</th>
                <th style="width: 45px;">RT/RW</th>
                <th>Jenis Surat</th>
                <th>Keperluan</th>
                <th style="width: 80px;">Tgl Diajukan</th>
                <th style="width: 75px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuan as $idx => $s)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td style="font-family: monospace; font-size: 8.5pt;">{{ $s->nomor_surat ?? '-' }}</td>
                <td>{{ $s->warga?->nama_lengkap ?? '-' }}</td>
                <td class="text-center" style="font-family: monospace; font-size: 8.5pt;">{{ $s->warga?->nik ?? '-' }}</td>
                <td class="text-center">{{ $s->warga?->rt?->nomor_rt ?? '-' }}/{{ $s->warga?->rw?->nomor_rw ?? '-' }}</td>
                <td>{{ $s->template?->nama_surat ?? '-' }}</td>
                <td>{{ $s->data_tambahan['keperluan'] ?? '-' }}</td>
                <td class="text-center">{{ $s->created_at->format('d/m/Y') }}</td>
                <td class="text-center" style="font-weight: bold; font-size: 8pt;">{{ str_replace('_', ' ', $s->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 15px;">Tidak ada riwayat pengajuan surat pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Pejabat Desa -->
    <div class="footer">
        <table>
            <tr>
                <td style="width: 65%;"></td>
                <td style="width: 35%; text-align: center;">
                    <p style="margin: 0;">{{ $desa?->nama ?? 'Sukamaju' }}, {{ now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 2px 0 60px 0; font-weight: bold;">KEPALA DESA {{ strtoupper($desa?->nama ?? 'SUKAMAJU') }}</p>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $desa?->kepala_desa ?? 'Bapak Budi Santoso' }}</p>
                    <p style="margin: 0; font-size: 9pt;">NIP: 19780512 200501 1 004</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

