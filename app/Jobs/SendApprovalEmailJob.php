<?php

namespace App\Jobs;

use App\Models\LaporanKecelakaan;
use App\Models\User;
use App\Mail\ApprovalRequestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendApprovalEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected LaporanKecelakaan $laporan,
        protected User $approver
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Untuk keamanan, token harus unik per permintaan dan disimpan di database.
            // Anda perlu membuat model dan migration untuk tabel `approval_tokens`.

            // Hapus token lama jika ada untuk approver dan laporan ini
            DB::table('approval_tokens')->where([
                'laporan_kecelakaan_id' => $this->laporan->id,
                'user_id' => $this->approver->id,
            ])->delete();

            // Buat token baru untuk approve
            $approveToken = DB::table('approval_tokens')->insertGetId([
                'laporan_kecelakaan_id' => $this->laporan->id,
                'user_id' => $this->approver->id,
                'action' => 'approve',
                'token' => Str::random(60),
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $approveTokenString = DB::table('approval_tokens')->find($approveToken)->token;

            // Buat token baru untuk reject
            $rejectToken = DB::table('approval_tokens')->insertGetId([
                'laporan_kecelakaan_id' => $this->laporan->id,
                'user_id' => $this->approver->id,
                'action' => 'reject',
                'token' => Str::random(60),
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $rejectTokenString = DB::table('approval_tokens')->find($rejectToken)->token;

            // Buat instance Mailable dengan data dan token yang baru dibuat
            $email = new ApprovalRequestMail(
                $this->laporan,
                $this->approver,
                $approveTokenString,
                $rejectTokenString
            );

            // Kirim email
            Mail::to($this->approver->email)->send($email);

        } catch (\Exception $e) {
            Log::error("Gagal mengirim email persetujuan untuk laporan #{$this->laporan->nomor_form} ke {$this->approver->email}: " . $e->getMessage());
        }
    }
}