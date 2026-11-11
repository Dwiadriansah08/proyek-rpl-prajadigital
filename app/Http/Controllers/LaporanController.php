<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\PengajuanSurat;
use App\Models\SuratTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller untuk Laporan & Rekapitulasi Pelayanan Surat Desa.
 *
 * Menyediakan ringkasan bulanan, export PDF rekap pelayanan,
 * dan export CSV/Excel untuk arsip administratif desa.
 */
class LaporanController extends Controller
{
    /**
     * Tampilkan halaman utama Laporan & Rekapitulasi Pelayanan.
     */
    public function index(Request $request): View
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $query = PengajuanSurat::with(['warga.rw', 'warga.rt', 'template'])
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        $summary = [
            'total' => (clone $query)->count(),
            'selesai' => (clone $query)->where('status', 'SELESAI')->count(),
            'menunggu_desa' => (clone $query)->where('status', 'MENUNGGU_ADMIN_DESA')->count(),
            'menunggu_rw' => (clone $query)->where('status', 'MENUNGGU_RW')->count(),
            'menunggu_rt' => (clone $query)->where('status', 'MENUNGGU_RT')->count(),
            'ditolak' => (clone $query)->where('status', 'DITOLAK')->count(),
        ];

        $pengajuan = $query->latest()->paginate(20)->withQueryString();
        $templates = SuratTemplate::where('is_active', true)->get();
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F');

        return view('laporan.index', compact('pengajuan', 'summary', 'bulan', 'tahun', 'namaBulan', 'templates'));
    }

    /**
     * Export rekapitulasi pelayanan surat ke format PDF resmi.
     */
    public function exportPdf(Request $request): Response
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $query = PengajuanSurat::with(['warga.rw', 'warga.rt', 'template'])
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->latest()->get();
        $desa = Desa::first();

        $summary = [
            'total' => $pengajuan->count(),
            'selesai' => $pengajuan->where('status', 'SELESAI')->count(),
            'menunggu_desa' => $pengajuan->where('status', 'MENUNGGU_ADMIN_DESA')->count(),
            'menunggu_rw' => $pengajuan->where('status', 'MENUNGGU_RW')->count(),
            'menunggu_rt' => $pengajuan->where('status', 'MENUNGGU_RT')->count(),
            'ditolak' => $pengajuan->where('status', 'DITOLAK')->count(),
        ];

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F');

        $pdf = Pdf::loadView('laporan.pdf', compact('pengajuan', 'summary', 'bulan', 'tahun', 'namaBulan', 'desa'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("Rekap_Pelayanan_Surat_{$namaBulan}_{$tahun}.pdf");
    }

    /**
     * Export rekapitulasi pelayanan surat ke format file Excel/CSV.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $query = PengajuanSurat::with(['warga.rw', 'warga.rt', 'template'])
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuan = $query->latest()->get();
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
        $fileName = "Rekap_Pelayanan_Surat_{$namaBulan}_{$tahun}.csv";

        return response()->streamDownload(function () use ($pengajuan) {
            $handle = fopen('php://output', 'w');
            // Menulis BOM UTF-8 agar karakter Excel tidak berantakan
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header kolom
            fputcsv($handle, [
                'No',
                'Nomor Surat',
                'Pemohon',
                'NIK',
                'RT',
                'RW',
                'Jenis Surat',
                'Keperluan',
                'Status',
                'Tanggal Pengajuan',
                'Tanggal Selesai',
                'Catatan',
            ]);

            $no = 1;
            foreach ($pengajuan as $s) {
                fputcsv($handle, [
                    $no++,
                    $s->nomor_surat ?? '-',
                    $s->warga?->nama_lengkap ?? '-',
                    "'".($s->warga?->nik ?? '-'),
                    $s->warga?->rt?->nomor_rt ?? '-',
                    $s->warga?->rw?->nomor_rw ?? '-',
                    $s->template?->nama_surat ?? '-',
                    $s->data_tambahan['keperluan'] ?? '-',
                    str_replace('_', ' ', $s->status),
                    $s->created_at->format('d/m/Y H:i'),
                    $s->approved_at ? $s->approved_at->format('d/m/Y H:i') : '-',
                    $s->catatan ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
