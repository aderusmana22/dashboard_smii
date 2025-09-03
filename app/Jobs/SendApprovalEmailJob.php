<?php

namespace App\Jobs;

use App\Mail\ApprovalRequestMail;
use App\Models\ApprovalToken;
use App\Models\LaporanKecelakaan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendApprovalEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public LaporanKecelakaan $laporan,
        public User $approver
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Invalidate token lama yang belum terpakai untuk laporan dan approver ini
        ApprovalToken::where('laporan_kecelakaan_id', $this->laporan->id)
            ->where('user_id', $this->approver->id)
            ->whereNull('used_at')
            ->update(['expires_at' => now()]);

        // Buat token approve baru
        $approveToken = ApprovalToken::create([
            'laporan_kecelakaan_id' => $this->laporan->id,
            'user_id' => $this->approver->id,
            'token' => Str::random(60),
            'action' => 'approve',
            'expires_at' => now()->addDays(7),
        ]);

        // Buat token reject baru
        $rejectToken = ApprovalToken::create([
            'laporan_kecelakaan_id' => $this->laporan->id,
            'user_id' => $this->approver->id,
            'token' => Str::random(60),
            'action' => 'reject',
            'expires_at' => now()->addDays(7),
        ]);

        // Kirim email menggunakan Mailable
        $mailable = new ApprovalRequestMail($this->laporan, $this->approver, $approveToken->token, $rejectToken->token);
        Mail::to($this->approver->email)->send($mailable);
    }
}