<?php

namespace App\Mail;

use App\Models\PengajuanSurat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuratSelesaiMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public PengajuanSurat $surat,
        public string $pdfPath
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $namaSurat = $this->surat->template?->nama_surat ?? 'Surat Keterangan';
        $nomorSurat = $this->surat->nomor_surat ?? ('#'.$this->surat->id);

        return new Envelope(
            subject: "Dokumen Resmi Selesai: {$namaSurat} ({$nomorSurat})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.surat_selesai',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (empty($this->pdfPath) || ! file_exists($this->pdfPath)) {
            return [];
        }

        $namaSurat = $this->surat->template?->nama_surat ?? 'Surat';
        $wargaNama = $this->surat->warga?->nama_lengkap ?? 'Warga';

        $cleanSurat = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaSurat);
        $cleanWarga = preg_replace('/[^A-Za-z0-9_\-]/', '_', $wargaNama);
        $attachmentName = "{$cleanSurat}_{$cleanWarga}.pdf";

        return [
            Attachment::fromPath($this->pdfPath)
                ->as($attachmentName)
                ->withMime('application/pdf'),
        ];
    }
}
