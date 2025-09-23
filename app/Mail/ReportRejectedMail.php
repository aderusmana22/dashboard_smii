<?php

namespace App\Mail;

use App\Models\LaporanKecelakaan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public LaporanKecelakaan $laporan;
    public string $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(LaporanKecelakaan $laporan, string $rejectionReason)
    {
        $this->laporan = $laporan->load('pembuatLaporan', 'approvalHistories.user'); // Load pembuatLaporan dan history untuk nama penolak
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Kecelakaan Kerja Ditolak - ' . $this->laporan->nomor_form,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'safetyboard.emails.rejected',
            with: [
                'laporanUrl' => route('accidents-report.show', $this->laporan->nomor_form),
                'reviseUrl' => route('accidents-report.revise', $this->laporan->nomor_form),
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