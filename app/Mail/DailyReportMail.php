<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $reportDate;
    protected $filePath;

    /**
     * Create a new message instance.
     */
    public function __construct(string $userName, string $reportDate, string $filePath)
    {
        $this->userName = $userName;
        $this->reportDate = $reportDate;
        $this->filePath = $filePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                'Otomasi System - PT Sinar Meadow'
            ),
            subject: 'Daily Finished Goods Stock Report',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as('Report_Stock_PPIC_' . now()->format('Ymd') . '.xlsx')
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}