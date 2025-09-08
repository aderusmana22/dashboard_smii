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

class ApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properti publik akan secara otomatis tersedia di dalam file Blade
    public LaporanKecelakaan $laporan;
    public User $approver;

    // Properti publik baru untuk menampung HTML yang sudah di-style
    public string $uraian_kejadian_styled;
    public string $analisa_masalah_styled;
    public string $tindakan_pencegahan_styled;
    public string $rekomendasi_styled;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\LaporanKecelakaan $laporan
     * @param \App\Models\User $approver
     * @param string $approveToken
     * @param string $rejectToken
     */
    public function __construct(
        LaporanKecelakaan $laporan,
        User $approver,
        public string $approveToken,
        public string $rejectToken
    ) {
        $this->laporan = $laporan;
        $this->approver = $approver;

        // Panggil method private untuk memproses dan menata semua konten HTML
        $this->prepareStyledHtml();
    }

    /**
     * Menjalankan fungsi styling untuk semua field yang relevan.
     */
    private function prepareStyledHtml(): void
    {
        $this->uraian_kejadian_styled = $this->styleEmailImages($this->laporan->uraian_kejadian);
        $this->analisa_masalah_styled = $this->styleEmailImages($this->laporan->analisa_masalah);
        $this->tindakan_pencegahan_styled = $this->styleEmailImages($this->laporan->tindakan_pencegahan);
        $this->rekomendasi_styled = $this->styleEmailImages($this->laporan->rekomendasi);
    }

    /**
     * Mem-parsing string HTML dan menerapkan gaya inline pada tag <img> untuk email.
     * Ini adalah fungsi yang sebelumnya menyebabkan error di file Blade.
     * 
     * @param string|null $htmlContent Konten HTML mentah.
     * @return string Konten HTML yang sudah diberi gaya.
     */
    private function styleEmailImages(?string $htmlContent): string
    {
        if (empty(trim($htmlContent))) {
            return '';
        }

        $dom = new \DOMDocument();
        // Menggunakan @ untuk menekan error dari HTML yang mungkin tidak valid & memastikan encoding UTF-8
        @$dom->loadHtml(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            // Terapkan gaya inline langsung ke elemen gambar
            $img->setAttribute('style', 'max-height: 200px; width: auto; height: auto; display: block; margin: 10px auto;');
        }
        
        return $dom->saveHTML();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Permintaan Persetujuan Laporan Kecelakaan #' . $this->laporan->nomor_form,
        );
    }

    /**
     * Get the message content definition.
     */
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

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}