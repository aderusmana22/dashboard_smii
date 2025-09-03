<?php

namespace App\Http\Controllers;

use App\Jobs\SendApprovalEmailJob;
use App\Models\ApprovalToken;
use App\Models\LaporanKecelakaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class EmailApprovalController extends Controller
{
    // Pastikan urutan ini sama persis dengan di LaporanKecelakaanController
    private $approvalOrder = [
        'manager_hse_id',
        'manager_terkait_id',
        'dept_head_id',
        'gm_id'
    ];

    public function approve(string $token)
    {
        $approvalToken = $this->validateToken($token, 'approve');
        if (!$approvalToken instanceof ApprovalToken) {
            return $approvalToken; // Mengembalikan view error
        }

        $laporan = $approvalToken->laporanKecelakaan;
        $approver = $approvalToken->user;

        if ($laporan->approvalStatus?->current_approver_id !== $approver->id) {
            // --- PERUBAHAN DI SINI ---
            return view('safetyboard.emails.invalid', ['message' => 'Laporan ini tidak lagi menunggu persetujuan Anda. Mungkin sudah ada tindakan yang diambil.']);
        }

        DB::beginTransaction();
        try {
            $currentApproverField = $this->getCurrentApproverField($laporan);
            $currentIndex = array_search($currentApproverField, $this->approvalOrder);

            $laporan->approvalHistories()->create([
                'user_id' => $approver->id,
                'action' => 'approved',
                'notes' => 'Menyetujui laporan via email sebagai ' . $this->getRoleName($currentApproverField),
            ]);

            if ($currentIndex === count($this->approvalOrder) - 1) {
                $laporan->approvalStatus->update(['status' => 'approved', 'current_approver_id' => null]);
            } else {
                $nextApproverField = $this->approvalOrder[$currentIndex + 1];
                $nextApproverId = $laporan->{$nextApproverField};
                $nextStatus = 'pending_' . str_replace('_id', '', $nextApproverField);
                $laporan->approvalStatus->update(['status' => $nextStatus, 'current_approver_id' => $nextApproverId]);
                
                $nextApprover = User::find($nextApproverId);
                if($nextApprover) {
                    SendApprovalEmailJob::dispatch($laporan, $nextApprover);
                }
            }

            $this->invalidateTokensFor($laporan, $approver);
            DB::commit();
            // --- PERUBAHAN DI SINI ---
            return view('safetyboard.emails.success', ['message' => 'Laporan berhasil disetujui. Terima kasih atas tindakan Anda.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyetujui laporan via email: ' . $e->getMessage());
            // --- PERUBAHAN DI SINI ---
            return view('safetyboard.emails.invalid', ['message' => 'Terjadi kesalahan sistem saat memproses permintaan Anda.']);
        }
    }

    public function showRejectForm(string $token): View
    {
        $approvalToken = $this->validateToken($token, 'reject');
        if (!$approvalToken instanceof ApprovalToken) {
            return $approvalToken; // Mengembalikan view error
        }
        // --- PERUBAHAN DI SINI ---
        return view('safetyboard.emails.reject_form', ['token' => $approvalToken->token]);
    }

    public function reject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:10',
            'token' => 'required|string|exists:approval_tokens,token',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $approvalToken = $this->validateToken($request->token, 'reject');
        if (!$approvalToken instanceof ApprovalToken) {
            return $approvalToken; // Mengembalikan view error
        }
        
        $laporan = $approvalToken->laporanKecelakaan;
        $approver = $approvalToken->user;
        
        if ($laporan->approvalStatus?->current_approver_id !== $approver->id) {
            // --- PERUBAHAN DI SINI ---
            return view('safetyboard.emails.invalid', ['message' => 'Laporan ini tidak lagi menunggu persetujuan Anda. Mungkin sudah ada tindakan yang diambil.']);
        }

        DB::beginTransaction();
        try {
            $currentApproverField = $this->getCurrentApproverField($laporan);
            $laporan->approvalStatus->update([
                'status' => 'rejected',
                'current_approver_id' => null,
                'rejection_reason' => $request->rejection_reason,
            ]);

            $laporan->approvalHistories()->create([
                'user_id' => $approver->id,
                'action' => 'rejected',
                'notes' => 'Menolak laporan via email sebagai ' . $this->getRoleName($currentApproverField) . '. Alasan: ' . $request->rejection_reason,
            ]);

            $this->invalidateTokensFor($laporan, $approver);
            DB::commit();
            // --- PERUBAHAN DI SINI ---
            return view('safetyboard.emails.success', ['message' => 'Laporan telah ditolak. Pembuat laporan akan diinformasikan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menolak laporan via email: ' . $e->getMessage());
            // --- PERUBAHAN DI SINI ---
            return view('safetyboard.emails.invalid', ['message' => 'Terjadi kesalahan sistem saat memproses permintaan Anda.']);
        }
    }

    private function validateToken(string $token, string $action)
    {
        $approvalToken = ApprovalToken::where('token', $token)
            ->where('action', $action)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$approvalToken) {
            // --- PERUBAHAN DI SINI ---
            return view('safetyboard.emails.invalid', ['message' => 'Tautan yang Anda gunakan tidak valid, sudah pernah digunakan, atau telah kedaluwarsa.']);
        }
        return $approvalToken;
    }

    private function invalidateTokensFor(LaporanKecelakaan $laporan, User $approver): void
    {
        ApprovalToken::where('laporan_kecelakaan_id', $laporan->id)
            ->where('user_id', $approver->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }
    
    private function getCurrentApproverField(LaporanKecelakaan $laporan): ?string
    {
        if (!$laporan->approvalStatus || !str_starts_with($laporan->approvalStatus->status, 'pending_')) {
            return null;
        }
        $roleKey = str_replace('pending_', '', $laporan->approvalStatus->status);
        $fieldName = $roleKey . '_id';
        return in_array($fieldName, $this->approvalOrder) ? $fieldName : null;
    }

    private function getRoleName(string $field): string
    {
        $names = [
            'manager_hse_id' => 'Manager HSE',
            'manager_terkait_id' => 'Manager Terkait',
            'dept_head_id' => 'Dept Head',
            'gm_id' => 'General Manager',
        ];
        return $names[$field] ?? 'Approver';
    }
}