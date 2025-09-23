<?php

namespace App\Mail;

use App\Models\LaporanKecelakaan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public LaporanKecelakaan $laporan;

    /**
     * Create a new message instance.
     */
    public function __construct(LaporanKecelakaan $laporan)
    {
        $this->laporan = $laporan->load('pembuatLaporan'); // Pastikan pembuatLaporan terload
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Kecelakaan Kerja Disetujui Penuh - ' . $this->laporan->nomor_form,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'safetyboard.emails.approved',
            with: [
                'laporanUrl' => route('accidents-report.show', $this->laporan->nomor_form),
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