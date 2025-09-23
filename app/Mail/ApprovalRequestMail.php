<?php

namespace App\Mail;

use App\Models\LaporanKecelakaan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public LaporanKecelakaan $laporan;
    public User $approver;
    public string $uraian_kejadian_styled;
    public string $analisa_masalah_styled;
    public string $tindakan_pencegahan_styled;
    public string $rekomendasi_styled;

    public function __construct(
        LaporanKecelakaan $laporan,
        User $approver,
        public string $approveToken,
        public string $rejectToken
    ) {
        $this->laporan = $laporan;
        $this->approver = $approver;
        $this->prepareStyledHtml();
    }

    private function prepareStyledHtml(): void
    {
        $this->uraian_kejadian_styled = $this->processHtmlForEmail($this->laporan->uraian_kejadian);
        $this->analisa_masalah_styled = $this->processHtmlForEmail($this->laporan->analisa_masalah);
        $this->tindakan_pencegahan_styled = $this->processHtmlForEmail($this->laporan->tindakan_pencegahan);
        $this->rekomendasi_styled = $this->processHtmlForEmail($this->laporan->rekomendasi);
    }

    /**
     * Mem-parsing string HTML, secara paksa memperbaiki URL gambar yang rusak, dan menerapkan gaya.
     * Didesain khusus untuk memperbaiki data lama yang mungkin memiliki path aneh.
     * 
     * @param string|null $htmlContent Konten HTML mentah dari database.
     * @return string Konten HTML yang siap untuk ditampilkan di email.
     */
    private function processHtmlForEmail(?string $htmlContent): string
    {
        if (empty(trim($htmlContent))) {
            return '';
        }

        $dom = new \DOMDocument();
        @$dom->loadHtml(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');
        $appUrl = rtrim(config('app.url'), '/'); // Ambil URL dasar dan hapus slash di akhir

        // Iterasi mundur diperlukan saat memodifikasi node untuk menghindari masalah indeks
        for ($i = $images->length - 1; $i >= 0; $i--) {
            $img = $images->item($i);
            $currentSrc = $img->getAttribute('src');

            // --- INI LOGIKA PERBAIKAN BARU YANG LEBIH AGRESIF ---
            
            // Cari posisi string '/storage/editor-uploads/'
            $storagePathPosition = strpos($currentSrc, '/storage/editor-uploads/');

            // Jika string tersebut ditemukan di dalam src
            if ($appUrl && $storagePathPosition !== false) {
                // Ambil bagian path yang benar, mulai dari '/storage/...'
                $correctRelativePath = substr($currentSrc, $storagePathPosition);
                
                // Gabungkan URL dasar dengan path yang benar untuk membuat URL absolut
                $absoluteUrl = $appUrl . $correctRelativePath;
                
                // Set atribut src ke URL absolut yang sudah diperbaiki
                $img->setAttribute('src', $absoluteUrl);
            }
            // --- AKHIR LOGIKA PERBAIKAN BARU ---

            // Terapkan gaya inline agar responsif di klien email
            $img->setAttribute('style', 'max-width: 100%; height: auto; display: block; margin: 10px 0;');
        }
        
        return $dom->saveHTML();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Approval Laporan Kecelakaan Kerja - ' . $this->laporan->nomor_form,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'safetyboard.emails.request',
            with: [
                'approveUrl' => route('email-approval.approve', ['token' => $this->approveToken]),
                'rejectUrl' => route('email-approval.show-reject-form', ['token' => $this->rejectToken]),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}