<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Selesai - Pelayanan Desa PrajaDigital</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: #ffffff;
            padding: 30px 25px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .badge-success {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 20px;
            margin-top: 10px;
            text-transform: uppercase;
        }
        .content {
            padding: 30px 25px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #111827;
        }
        .detail-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px;
            margin: 20px 0;
        }
        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            display: table-cell;
            width: 38%;
            color: #64748b;
            font-weight: 500;
        }
        .detail-value {
            display: table-cell;
            width: 62%;
            color: #1e293b;
            font-weight: 600;
        }
        .attachment-alert {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px 16px;
            border-radius: 4px;
            margin: 22px 0;
            font-size: 14px;
            color: #1e40af;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 25px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    @php
        $warga = $surat->warga;
        $rw = $warga?->rw;
        $rt = $warga?->rt;
        $desa = $rw?->desa;
        $namaSurat = $surat->template?->nama_surat ?? 'Surat Keterangan';
        $nomorSurat = $surat->nomor_surat ?? ('#'.$surat->id);
        $waktuSelesai = $surat->approved_at ? \Carbon\Carbon::parse($surat->approved_at)->translatedFormat('d F Y, H:i') : now()->translatedFormat('d F Y, H:i');
    @endphp

    <div class="container">
        <div class="header">
            <h1>PELAYANAN DESA PRAJADIGITAL</h1>
            <p>{{ $desa?->nama ?? 'Pemerintah Desa Sukamaju' }} - Rukun Warga {{ $rw?->nomor_rw ?? '001' }}</p>
            <div class="badge-success">Surat Telah Selesai Diproses</div>
        </div>

        <div class="content">
            <div class="greeting">
                Yth. Bapak/Ibu {{ $warga?->nama_lengkap ?? 'Warga' }},
            </div>
            <p>
                Kabar baik! Pengajuan dokumen administrasi kependudukan Anda melalui sistem layanan desa <strong>PrajaDigital</strong> telah selesai diverifikasi dan disetujui oleh Pengurus RT dan RW terkait.
            </p>

            <div class="detail-card">
                <div class="detail-row">
                    <div class="detail-label">Jenis Dokumen</div>
                    <div class="detail-value">{{ $namaSurat }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Nomor Surat</div>
                    <div class="detail-value" style="font-family: monospace;">{{ $nomorSurat }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Nama Pemohon</div>
                    <div class="detail-value">{{ $warga?->nama_lengkap }} (NIK: {{ $warga?->nik }})</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Wilayah Domisili</div>
                    <div class="detail-value">RT {{ $rt?->nomor_rt ?? '-' }} / RW {{ $rw?->nomor_rw ?? '-' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Waktu Disetujui</div>
                    <div class="detail-value">{{ $waktuSelesai }} WIB</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Disetujui Oleh</div>
                    <div class="detail-value">Ketua RW {{ $rw?->nomor_rw ?? '001' }} ({{ $surat->approver?->name ?? 'Pengurus RW' }})</div>
                </div>
            </div>

            <div class="attachment-alert">
                <strong>Lampiran File Dokumen:</strong><br>
                File PDF resmi telah kami lampirkan secara langsung pada email ini. Anda dapat mengunduh dan mencetak file tersebut kapan saja untuk keperluan administrasi Anda.
            </div>

            <p style="font-size: 14px; color: #4b5563;">
                Jika Anda memiliki pertanyaan lebih lanjut mengenai permohonan surat ini, Anda dapat menghubungi kantor sekretariat RT/RW setempat.
            </p>

            <p style="font-size: 14px; margin-top: 20px;">
                Salam hangat,<br>
                <strong>Pengurus RT {{ $rt?->nomor_rt ?? '001' }} / RW {{ $rw?->nomor_rw ?? '001' }}</strong><br>
                {{ $desa?->nama ?? 'Pemerintah Desa Sukamaju' }}
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} PrajaDigital - Sistem Pelayanan Administrasi Desa Terpadu.</p>
            <p>Email ini dikirim secara otomatis oleh sistem, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
