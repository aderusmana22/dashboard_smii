<?php

namespace App\Http\Controllers;

use App\Models\LaporanKecelakaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanKecelakaanController extends Controller
{
    /**
     * Mendefinisikan urutan persetujuan.
     */
    private $approvalOrder = [
        'manager_hse_id',
        'manager_terkait_id',
        'dept_head_id',
        'gm_id'
    ];

    public function index()
    {
        $laporan = LaporanKecelakaan::with(['pembuatLaporan', 'approvalStatus.currentApprover'])
            ->latest()
            ->paginate(15);
        return view('safetyboard.index', compact('laporan'));
    }

public function create()
{
    $users = User::orderBy('name')->get(['id', 'name']);
    // Define $laporan as null for the create form
    $laporan = null;
    return view('safetyboard.form', compact('users', 'laporan'));
}

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nomor_form' => 'nullable|string|max:255',
            'date' => 'required|string',
            'kategori_kecelakaan' => 'required|string|max:255',
            'kategori_dampak' => 'required|string|max:255',
            'waktu_kecelakaan' => 'required|date',
            'lokasi_kecelakaan' => 'required|string|max:255',
            'tipe_kecelakaan' => 'nullable|string|max:255',
            'bagian_terluka' => 'nullable|string|max:255',
            'uraian_kejadian' => 'required|string',
            'nama_korban' => 'required|string|max:255',
            'nik' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'usia' => 'nullable|string|max:255',
            'tanggal_masuk' => 'nullable|date',
            'masa_kerja' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
            'pertolongan' => 'required|string|max:255',
            'p3k_oleh' => 'nullable|string|max:255',
            'jam_p3k' => 'nullable|date_format:H:i',
            'akibat_kecelakaan' => 'required|string|max:255',
            'waktu_hilang' => 'nullable|integer|min:0',
            'analisa_masalah' => 'nullable|string',
            'tindakan_pencegahan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'biaya_harga.*' => 'nullable|numeric|min:0',
            'biaya_kategori.*' => 'nullable|string|max:255',
            'perbaikan_tindakan.*' => 'nullable|string',
            'perbaikan_pic.*' => 'nullable|string|max:255',
            'perbaikan_due_date.*' => 'nullable|date',
            'pembuat_laporan_id' => 'required|integer|exists:users,id',
            'manager_hse_id' => 'required|integer|exists:users,id',
            'manager_terkait_id' => 'required|integer|exists:users,id',
            'dept_head_id' => 'required|integer|exists:users,id',
            'gm_id' => 'required|integer|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $year = date('Y');
            // Mengunci tabel untuk mencegah race condition saat menghitung nomor urut
            $latestReportCount = LaporanKecelakaan::whereYear('created_at', $year)->lockForUpdate()->count();
            $nextNumber = $latestReportCount + 1;

            // --- LOGIKA PEMBUATAN NOMOR FORM OTOMATIS (DIPERBAIKI) ---
            if (!$request->has('revised_from_id')) {
                $validatedData['nomor_form'] = sprintf("HSE-%s-%d", $year, $nextNumber);
            } else {
                $validatedData['nomor_form'] = sprintf("HSE-%s-%d-REV", $year, $nextNumber);
            }

            // --- LOGIKA PENANGANAN REVISI ---
            if ($request->has('revised_from_id')) {
                $originalReport = LaporanKecelakaan::with('approvalStatus')->findOrFail($request->input('revised_from_id'));
                if ($originalReport->approvalStatus) {
                    $originalReport->approvalStatus->update(['status' => 'revised']);
                }
                $validatedData['revision_number'] = $originalReport->revision_number + 1;
                $validatedData['revised_from_id'] = $originalReport->id;
            }

            $laporan = new LaporanKecelakaan($validatedData);
            $laporan->apd_data = $this->prosesApdData($request);
            list($kategori, $deskripsi) = $this->prosesSebabUtama($request);
            $laporan->sebab_utama_kategori = $kategori;
            $laporan->sebab_utama_deskripsi = $deskripsi;
            $laporan->save();

            $this->prosesBiaya($laporan, $validatedData);
            $this->prosesSaranPerbaikan($laporan, $validatedData);

            $firstApproverId = $validatedData[$this->approvalOrder[0]];
            $laporan->approvalStatus()->create([
                'status' => 'pending_manager_hse',
                'current_approver_id' => $firstApproverId,
            ]);
            $laporan->approvalHistories()->create([
                'user_id' => $validatedData['pembuat_laporan_id'],
                'action' => 'created',
                'notes' => 'Laporan dibuat dan diajukan untuk persetujuan.',
            ]);

            DB::commit();
            return redirect()->route('accidents-report.index')->with('success', 'Laporan kecelakaan berhasil disimpan dan diajukan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan laporan kecelakaan: ' . $e->getMessage() . ' di file ' . $e->getFile() . ' baris ' . $e->getLine());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data. Error: ' . $e->getMessage());
        }
    }

    public function show(LaporanKecelakaan $laporan)
    {
        $laporan->load('approvalStatus', 'approvalHistories.user', 'pembuatLaporan', 'revisedFrom');
        $currentApproverField = $this->getCurrentApproverField($laporan);
        return view('safetyboard.show', compact('laporan', 'currentApproverField'));
    }

    public function revise(LaporanKecelakaan $laporan)
    {
        if ($laporan->pembuat_laporan_id !== Auth::id()) {
            return redirect()->route('accidents-report.index')->with('error', 'Anda tidak berwenang merevisi laporan ini.');
        }
        if ($laporan->approvalStatus?->status !== 'rejected') {
            return redirect()->route('accidents-report.index')->with('error', 'Hanya laporan yang ditolak yang dapat direvisi.');
        }

        $laporan->load('biayaPerawatan', 'saranPerbaikan');
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('safetyboard.form', [
            'laporan' => $laporan,
            'users' => $users,
            'isRevision' => true
        ]);
    }

    public function approve(LaporanKecelakaan $laporan)
    {
        $status = $laporan->approvalStatus;
        if (!$status || $status->current_approver_id !== Auth::id()) {
            return redirect()->route('accidents-report.index')->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
        }

        DB::beginTransaction();
        try {
            $currentApproverField = $this->getCurrentApproverField($laporan);
            if ($currentApproverField === null) {
                return redirect()->route('accidents-report.index')->with('error', 'Status laporan tidak valid untuk persetujuan.');
            }
            
            $currentIndex = array_search($currentApproverField, $this->approvalOrder);

            $laporan->approvalHistories()->create([
                'user_id' => Auth::id(),
                'action' => 'approved',
                'notes' => 'Menyetujui laporan sebagai ' . $this->getRoleName($currentApproverField),
            ]);

            if ($currentIndex === count($this->approvalOrder) - 1) {
                $status->update(['status' => 'approved', 'current_approver_id' => null]);
            } else {
                $nextApproverField = $this->approvalOrder[$currentIndex + 1];
                $nextApproverId = $laporan->{$nextApproverField};
                $nextStatus = 'pending_' . str_replace('_id', '', $nextApproverField);
                $status->update(['status' => $nextStatus, 'current_approver_id' => $nextApproverId]);
            }

            DB::commit();
            return redirect()->route('accidents-report.index')->with('success', 'Laporan berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyetujui laporan: ' . $e->getMessage());
            return redirect()->route('accidents-report.index')->with('error', 'Terjadi kesalahan saat menyetujui laporan.');
        }
    }

    public function reject(Request $request, LaporanKecelakaan $laporan)
    {
        $request->validate(['rejection_reason' => 'required|string|min:10']);
        $status = $laporan->approvalStatus;
        if (!$status || $status->current_approver_id !== Auth::id()) {
            return redirect()->route('accidents-report.index')->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
        }

        DB::beginTransaction();
        try {
            $currentApproverField = $this->getCurrentApproverField($laporan);
            $status->update([
                'status' => 'rejected',
                'current_approver_id' => null,
                'rejection_reason' => $request->rejection_reason,
            ]);
            $laporan->approvalHistories()->create([
                'user_id' => Auth::id(),
                'action' => 'rejected',
                'notes' => 'Menolak laporan sebagai ' . $this->getRoleName($currentApproverField) . '. Alasan: ' . $request->rejection_reason,
            ]);
            DB::commit();
            return redirect()->route('accidents-report.index')->with('success', 'Laporan telah ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menolak laporan: ' . $e->getMessage());
            return redirect()->route('accidents-report.index')->with('error', 'Terjadi kesalahan saat menolak laporan.');
        }
    }

    // --- Helper Functions ---

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

    private function prosesBiaya(LaporanKecelakaan $laporan, array $data): void
    {
        if (!empty($data['biaya_harga'])) {
            foreach ($data['biaya_harga'] as $index => $harga) {
                if (!empty($harga) && !empty($data['biaya_kategori'][$index])) {
                    $laporan->biayaPerawatan()->create(['harga' => $harga, 'kategori' => $data['biaya_kategori'][$index]]);
                }
            }
        }
    }

    private function prosesSaranPerbaikan(LaporanKecelakaan $laporan, array $data): void
    {
        if (!empty($data['perbaikan_tindakan'])) {
            foreach ($data['perbaikan_tindakan'] as $index => $tindakan) {
                if (!empty($tindakan)) {
                    $laporan->saranPerbaikan()->create([
                        'tindakan' => $tindakan,
                        'pic' => $data['perbaikan_pic'][$index] ?? null,
                        'due_date' => $data['perbaikan_due_date'][$index] ?? null,
                    ]);
                }
            }
        }
    }

    private function prosesApdData(Request $request): array
    {
        $apds = ['sarung_tangan', 'sepatu', 'helm', 'masker', 'kacamata', 'celemek', 'kedok', 'hairnet'];
        $apdData = [];
        foreach ($apds as $key) {
            if ($request->has("apd_wajib_{$key}")) {
                $apdData[$key] = [
                    'wajib' => true,
                    'keterangan' => $request->input("apd_keterangan_{$key}"),
                    'dipakai' => $request->input("apd_dipakai_{$key}"),
                ];
            }
        }
        return $apdData;
    }

    private function prosesSebabUtama(Request $request): array
    {
        $sebab = $request->input('sebab_utama');
        if (!$sebab) return [null, null];
        if ($request->input('sebab_utama') === 'on') {
            if (!empty($request->input('sebab_a_lain_input'))) return ['A', $request->input('sebab_a_lain_input')];
            if (!empty($request->input('sebab_b_lain_input'))) return ['B', $request->input('sebab_b_lain_input')];
        }
        if (str_contains($sebab, ' - ')) return explode(' - ', $sebab, 2);
        return [null, null];
    }
}