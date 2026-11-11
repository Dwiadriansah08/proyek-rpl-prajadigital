<?php

namespace App\Http\Controllers;

use App\Mail\SuratSelesaiMail;
use App\Models\PengajuanSurat;
use App\Models\SuratTemplate;
use App\Services\WhatsappNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Controller untuk mengelola Pengajuan Surat oleh Warga.
 *
 * Alur persetujuan: Warga -> (Opsional: RT) -> RW -> Selesai.
 * Surat yang disetujui akan di-generate PDF-nya.
 */
class PengajuanSuratController extends Controller
{
    /**
     * Tampilkan daftar pengajuan surat sesuai role dan wilayah.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = PengajuanSurat::with(['warga', 'template']);

        if ($user->hasRole('rw')) {
            $query->whereHas('warga', function ($q) use ($user) {
                $q->where('rw_id', $user->rw_id);
            });
        } elseif ($user->hasRole('rt')) {
            $query->whereHas('warga', function ($q) use ($user) {
                $q->where('rt_id', $user->rt_id);
            });
        } elseif ($user->hasRole('warga')) {
            $query->where('warga_id', $user->warga?->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->latest()->paginate(15)->withQueryString();

        return view('surat.index', compact('pengajuan'));
    }

    /**
     * Tampilkan form pengajuan surat (khusus Warga).
     */
    public function create(): View|RedirectResponse
    {
        if (! auth()->user()->hasRole('warga')) {
            return redirect()->route('surat.index')->with('error', 'Hanya warga yang dapat mengajukan surat.');
        }

        $templates = SuratTemplate::where('is_active', true)->get();

        return view('surat.create', compact('templates'));
    }

    /**
     * Simpan pengajuan surat baru.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! auth()->user()->hasRole('warga')) {
            abort(403);
        }

        $validated = $request->validate([
            'template_id' => 'required|exists:surat_templates,id',
            'keperluan' => 'required|string',
        ]);

        $data_tambahan = $request->except(['_token', 'template_id']);

        $surat = PengajuanSurat::create([
            'warga_id' => auth()->user()->warga->id,
            'template_id' => $validated['template_id'],
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => $data_tambahan,
        ]);

        try {
            app(WhatsappNotificationService::class)->notifyStatusUpdate($surat, 'MENUNGGU_RT');
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi WA pengajuan surat baru: '.$e->getMessage());
        }

        return redirect()->route('surat.index')->with('success', 'Pengajuan surat berhasil dikirim ke RT.');
    }

    /**
     * Tampilkan detail pengajuan surat.
     */
    public function show(PengajuanSurat $surat): View
    {
        $this->authorize('view', $surat);

        return view('surat.show', compact('surat'));
    }

    /**
     * Proses persetujuan atau penolakan surat oleh RT/RW.
     */
    public function process(Request $request, PengajuanSurat $surat): RedirectResponse
    {
        $this->authorize('process', $surat);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'catatan' => 'nullable|string',
        ]);

        if ($validated['action'] === 'reject') {
            return $this->handleRejection($surat, $validated['catatan']);
        }

        return $this->handleApproval($surat);
    }

    /**
     * Tangani penolakan surat.
     */
    private function handleRejection(PengajuanSurat $surat, ?string $catatan): RedirectResponse
    {
        $user = auth()->user();
        $updateData = [
            'status' => 'DITOLAK',
            'catatan' => $catatan,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ];

        if ($user->hasRole(['admin_desa', 'super_admin'])) {
            $updateData['approved_by_admin_desa_at'] = now();
        }

        $surat->update($updateData);

        app(WhatsappNotificationService::class)->notifyStatusUpdate($surat, 'DITOLAK');

        return redirect()->route('surat.show', $surat->id)->with('success', 'Pengajuan surat ditolak.');
    }

    /**
     * Tangani persetujuan surat berdasarkan role (RT, RW, atau Admin Desa).
     */
    private function handleApproval(PengajuanSurat $surat): RedirectResponse
    {
        $user = auth()->user();

        if ($surat->status === 'MENUNGGU_RT' && $user->hasRole('rt')) {
            $surat->update(['status' => 'MENUNGGU_RW']);
            app(WhatsappNotificationService::class)->notifyStatusUpdate($surat, 'MENUNGGU_RW');
        } elseif ($surat->status === 'MENUNGGU_RW' && $user->hasRole('rw')) {
            // RW menyetujui, surat dialihkan ke Admin Desa untuk verifikasi & penerbitan akhir
            $surat->update(['status' => 'MENUNGGU_ADMIN_DESA']);
            app(WhatsappNotificationService::class)->notifyStatusUpdate($surat, 'MENUNGGU_ADMIN_DESA');
        } elseif ($surat->status === 'MENUNGGU_ADMIN_DESA' && ($user->hasRole('admin_desa') || $user->hasRole('super_admin'))) {
            // Admin Desa / Lurah menyetujui tingkat akhir, surat selesai dan generate PDF
            $rwNomor = $surat->warga?->rw?->nomor_rw ?? '001';
            $nomorSurat = '470/'.sprintf('%03d', $surat->id).'/RW.'.$rwNomor.'/'.date('Y');

            $surat->update([
                'status' => 'SELESAI',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approved_by_admin_desa_at' => now(),
                'nomor_surat' => $nomorSurat,
            ]);

            $this->generatePdf($surat);
            $surat->refresh();

            // Kirim Email PDF ke Warga
            $this->sendEmailSuratSelesai($surat);

            // Kirim Notifikasi Teks WhatsApp ke Warga
            try {
                app(WhatsappNotificationService::class)->notifyStatusUpdate($surat, 'SELESAI');
            } catch (\Throwable $e) {
                Log::error("Gagal mengirim notifikasi WA surat selesai ID {$surat->id}: ".$e->getMessage());
            }
        } else {
            return redirect()->route('surat.show', $surat->id)->with('error', 'Status surat tidak sesuai untuk diproses oleh peran Anda.');
        }

        return redirect()->route('surat.show', $surat->id)->with('success', 'Pengajuan surat berhasil diproses.');
    }

    /**
     * Kirim email dengan lampiran PDF ke email warga pemohon.
     */
    private function sendEmailSuratSelesai(PengajuanSurat $surat): void
    {
        $warga = $surat->warga;
        $recipientEmail = $warga?->email ?: ($warga?->user?->email ?: null);

        if (! $recipientEmail || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Pengiriman email surat ID {$surat->id} dilewati: email warga tidak ditemukan atau tidak valid.");

            return;
        }

        if (empty($surat->file_pdf)) {
            Log::warning("Pengiriman email surat ID {$surat->id} dilewati: berkas file_pdf belum tersedia.");

            return;
        }

        $pdfPath = storage_path('app/public/'.$surat->file_pdf);
        if (! file_exists($pdfPath)) {
            Log::warning("Pengiriman email surat ID {$surat->id} dilewati: file PDF tidak ada di {$pdfPath}.");

            return;
        }

        try {
            Mail::to($recipientEmail)->send(new SuratSelesaiMail($surat, $pdfPath));

            $currentMailer = config('mail.default');
            if ($currentMailer === 'log') {
                Log::warning("Email surat selesai ID {$surat->id} ditulis ke laravel.log karena MAIL_MAILER=log. Untuk mengirim ke inbox email warga nyata, ubah MAIL_MAILER=smtp di file .env.", [
                    'recipient' => $recipientEmail,
                ]);
            } else {
                Log::info("Email surat selesai ID {$surat->id} berhasil dikirim ke {$recipientEmail} via mailer [{$currentMailer}]");
            }
        } catch (\Exception $e) {
            Log::error('Gagal kirim email: '.$e->getMessage(), [
                'surat_id' => $surat->id,
                'recipient' => $recipientEmail,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Tentukan view template Blade PDF berdasarkan jenis / kode surat.
     */
    private function resolvePdfView(PengajuanSurat $surat): string
    {
        $kode = strtoupper((string) ($surat->template?->kode_surat ?? ''));
        $nama = strtolower((string) ($surat->template?->nama_surat ?? ''));

        // 1. Surat Pengantar RT/RW (SK-01)
        if ($kode === 'SK-01' || str_contains($nama, 'pengantar')) {
            return 'surat.pdf.pengantar_rtrw';
        }

        // 2. Surat Keterangan Domisili (SK-02)
        if ($kode === 'SK-02' || str_contains($nama, 'domisili')) {
            return 'surat.pdf.domisili';
        }

        // 3. Surat Keterangan Tidak Mampu / SKTM (SK-03)
        if ($kode === 'SK-03' || str_contains($nama, 'tidak mampu') || str_contains($nama, 'sktm')) {
            return 'surat.pdf.sktm';
        }

        // 4. Surat Keterangan Kematian (SK-04)
        if ($kode === 'SK-04' || str_contains($nama, 'kematian') || str_contains($nama, 'meninggal')) {
            return 'surat.pdf.kematian';
        }

        // 5. Surat Keterangan Usaha / SKU (SK-05)
        if ($kode === 'SK-05' || str_contains($nama, 'usaha') || str_contains($nama, 'sku')) {
            return 'surat.pdf.sku';
        }

        // 6. Surat Keterangan Berkelakuan Baik / SKCK (SK-06)
        if ($kode === 'SK-06' || str_contains($nama, 'kelakuan') || str_contains($nama, 'berkelakuan') || str_contains($nama, 'skck')) {
            return 'surat.pdf.berkelakuan_baik';
        }

        // 7. Surat Keterangan Penghasilan (SK-07)
        if ($kode === 'SK-07' || str_contains($nama, 'penghasilan') || str_contains($nama, 'gaji')) {
            return 'surat.pdf.penghasilan';
        }

        // Fallback default
        if (view()->exists('surat.pdf_template')) {
            return 'surat.pdf_template';
        }

        return 'surat.pdf.pengantar_rtrw';
    }

    /**
     * Dapatkan full public URL dari file PDF surat yang tersimpan di storage publik.
     */
    public function getPdfUrl(PengajuanSurat $surat): ?string
    {
        if (empty($surat->file_pdf)) {
            return null;
        }

        $filePath = storage_path('app/public/'.$surat->file_pdf);
        if (! file_exists($filePath)) {
            Log::warning("File PDF fisik surat ID {$surat->id} tidak ditemukan di storage: {$filePath}");

            return null;
        }

        return asset('storage/'.$surat->file_pdf);
    }

    /**
     * Generate file PDF untuk surat yang telah selesai.
     */
    private function generatePdf(PengajuanSurat $surat): void
    {
        try {
            $view = $this->resolvePdfView($surat);
            $pdf = Pdf::loadView($view, ['surat' => $surat]);
            $fileName = 'surat_'.$surat->id.'_'.time().'.pdf';

            $publicDir = storage_path('app/public');
            if (! is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $pdf->save($publicDir.'/'.$fileName);
            $surat->update(['file_pdf' => $fileName]);

            Log::info("PDF Surat ID {$surat->id} berhasil di-generate menggunakan template [{$view}]: {$fileName}");
        } catch (\Exception $e) {
            // Gagal membuat PDF, namun status surat sudah selesai
            Log::error("Gagal generate PDF Surat ID {$surat->id}: ".$e->getMessage());
        }
    }

    /**
     * Unduh file PDF surat.
     */
    public function download(PengajuanSurat $surat)
    {
        $this->authorize('view', $surat);

        if (! $surat->file_pdf) {
            abort(404, 'File PDF tidak ditemukan');
        }

        return response()->download(storage_path('app/public/'.$surat->file_pdf));
    }
}
