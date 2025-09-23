<?php

namespace App\Jobs;

use App\Mail\ReportApprovedMail;
use App\Mail\ReportRejectedMail;
use App\Models\LaporanKecelakaan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReportStatusEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected LaporanKecelakaan $laporan;
    protected string $statusType; // 'approved' or 'rejected'
    protected ?string $rejectionReason;

    /**
     * Create a new job instance.
     */
    public function __construct(LaporanKecelakaan $laporan, string $statusType, ?string $rejectionReason = null)
    {
        $this->laporan = $laporan;
        $this->statusType = $statusType;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Pastikan relasi pembuatLaporan sudah terload
            $this->laporan->load('pembuatLaporan');

            if (!$this->laporan->pembuatLaporan) {
                Log::warning("Pembuat laporan tidak ditemukan untuk laporan #{$this->laporan->nomor_form}. Email notifikasi tidak dikirim.");
                return;
            }

            $recipientEmail = $this->laporan->pembuatLaporan->email;

            if ($this->statusType === 'approved') {
                Mail::to($recipientEmail)->send(new ReportApprovedMail($this->laporan));
                Log::info("Email notifikasi laporan disetujui dikirim ke {$recipientEmail} untuk laporan #{$this->laporan->nomor_form}.");
            } elseif ($this->statusType === 'rejected') {
                Mail::to($recipientEmail)->send(new ReportRejectedMail($this->laporan, $this->rejectionReason ?? 'Tidak ada alasan spesifik diberikan.'));
                Log::info("Email notifikasi laporan ditolak dikirim ke {$recipientEmail} untuk laporan #{$this->laporan->nomor_form}.");
            } else {
                Log::warning("Tipe status email tidak dikenal: {$this->statusType} untuk laporan #{$this->laporan->nomor_form}.");
            }

        } catch (\Exception $e) {
            Log::error("Gagal mengirim email notifikasi status laporan untuk laporan #{$this->laporan->nomor_form} ({$this->statusType}) ke {$this->laporan->pembuatLaporan->email }: " . $e->getMessage());
        }
    }
}