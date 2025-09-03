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

    /**
     * Create a new message instance.
     */
    public function __construct(
        public LaporanKecelakaan $laporan,
        public User $approver,
        public string $approveToken,
        public string $rejectToken
    ) {}

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
            // --- PERUBAHAN DI SINI ---
            markdown: 'safetyboard.emails.request', 
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