<?php

namespace App\Http\Controllers;

use App\Models\LaporanKecelakaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\LaporanApprovalStatus;
use App\Jobs\SendApprovalEmailJob;
use App\Jobs\SendReportStatusEmailJob; // <-- Pastikan use statement ini ada

class LaporanKecelakaanController extends Controller
{
     private $approvalOrder = [
        'manager_hse_id',
        'manager_terkait_id',
        'dept_head_id',
        'gm_id'
    ];

    public function index()
    {
        return view('safetyboard.index');
    }

    public function getData(Request $request)
    {
        $laporanTable = (new LaporanKecelakaan)->getTable();
        $approvalStatusTable = (new LaporanApprovalStatus)->getTable();

        $latestRevisionsSubquery = LaporanKecelakaan::select(
                DB::raw('SUBSTRING_INDEX(nomor_form, "-REV", 1) as base_form'),
                DB::raw('MAX(revision_number) as max_revision')
            )
            ->groupBy('base_form');

        $query = LaporanKecelakaan::query()
            ->joinSub($latestRevisionsSubquery, 'latest_revs', function ($join) use ($laporanTable) {
                $join->on(DB::raw('SUBSTRING_INDEX(nomor_form, "-REV", 1)'), '=', 'latest_revs.base_form')
                     ->on("{$laporanTable}.revision_number", '=', 'latest_revs.max_revision');
            })
            ->leftJoin($approvalStatusTable, "{$laporanTable}.id", '=', "{$approvalStatusTable}.laporan_kecelakaan_id")
            ->select("{$laporanTable}.*");

        if ($request->filled('nomor_form')) {
            $query->where("{$laporanTable}.nomor_form", 'like', '%' . $request->nomor_form . '%');
        }
        if ($request->filled('nama_korban')) {
            $query->where("{$laporanTable}.nama_korban", 'like', '%' . $request->nama_korban . '%');
        }
        if ($request->filled('status')) {
            $query->where("{$approvalStatusTable}.status", $request->status);
        }
        if ($request->filled('date_start')) {
            $query->whereDate("{$laporanTable}.date", '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate("{$laporanTable}.date", '<=', $request->date_end);
        }

        $totalDataQuery = LaporanKecelakaan::query()
            ->joinSub($latestRevisionsSubquery, 'latest_revs', function ($join) {
                $join->on(DB::raw('SUBSTRING_INDEX(nomor_form, "-REV", 1)'), '=', 'latest_revs.base_form')
                     ->on('revision_number', '=', 'latest_revs.max_revision');
            });
        
        $totalData = (clone $totalDataQuery)->count();
        $totalFiltered = (clone $query)->count();

        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderColumnName = $request->input('columns.' . $orderColumnIndex . '.name');
            $orderDirection = $request->input('order.0.dir');
            if ($orderColumnName) {
                $columnMapping = [
                    'nomor_form' => "{$laporanTable}.nomor_form",
                    'date' => "{$laporanTable}.date",
                    'nama_korban' => "{$laporanTable}.nama_korban",
                    'approval_statuses.status' => "{$approvalStatusTable}.status",
                    'lokasi_kecelakaan' => "{$laporanTable}.lokasi_kecelakaan",
                ];
                if (isset($columnMapping[$orderColumnName])) {
                    $query->orderBy($columnMapping[$orderColumnName], $orderDirection);
                }
            }
        } else {
            $query->latest("{$laporanTable}.created_at");
        }

        if ($request->filled('length') && $request->length != -1) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $laporan = $query->with('approvalStatus')->get();
        
        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $laporan
        ]);
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
            'sebab_kecelakaan' => 'nullable|string|max:255',
            'sebab_utama_a' => 'nullable|string',
            'sebab_a_lain_input' => 'nullable|string',
            'sebab_utama_b' => 'nullable|string',
            'sebab_b_lain_input' => 'nullable|string',
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
            if (!empty($validatedData['uraian_kejadian'])) {
                $validatedData['uraian_kejadian'] = $this->prosesGambarEditor($validatedData['uraian_kejadian']);
            }
            if (!empty($validatedData['analisa_masalah'])) {
                $validatedData['analisa_masalah'] = $this->prosesGambarEditor($validatedData['analisa_masalah']);
            }
            if (!empty($validatedData['tindakan_pencegahan'])) {
                $validatedData['tindakan_pencegahan'] = $this->prosesGambarEditor($validatedData['tindakan_pencegahan']);
            }
            if (!empty($validatedData['rekomendasi'])) {
                $validatedData['rekomendasi'] = $this->prosesGambarEditor($validatedData['rekomendasi']);
            }

            $laporan = new LaporanKecelakaan($validatedData);

            if ($request->has('revised_from_id')) {
                $originalReport = LaporanKecelakaan::findOrFail($request->input('revised_from_id'));
                
                $baseNomorForm = explode('-REV', $originalReport->nomor_form)[0];

                $allOldVersions = LaporanKecelakaan::where(DB::raw('SUBSTRING_INDEX(nomor_form, "-REV", 1)'), $baseNomorForm)->get();
                foreach ($allOldVersions as $oldVersion) {
                    $oldVersion->is_active = false;
                    $oldVersion->save();
                    if ($oldVersion->approvalStatus) {
                        $oldVersion->approvalStatus->update(['status' => 'revised']);
                    }
                }
                
                $latestRevision = LaporanKecelakaan::where(DB::raw('SUBSTRING_INDEX(nomor_form, "-REV", 1)'), $baseNomorForm)->max('revision_number');
                $newRevisionNumber = ($latestRevision ?? 0) + 1;

                $laporan->nomor_form = $baseNomorForm . '-REV' . $newRevisionNumber;
                $laporan->revision_number = $newRevisionNumber;
                $laporan->revised_from_id = $originalReport->id;
                $laporan->is_active = true;

            } else {
                $year = date('Y');
                $latestReportCount = LaporanKecelakaan::whereYear('created_at', $year)
                                        ->whereNull('revised_from_id')
                                        ->lockForUpdate()
                                        ->count();
                $nextNumber = $latestReportCount + 1;
                $laporan->nomor_form = sprintf("HSE-%s-%d", $year, $nextNumber);
            }

            $laporan->apd_data = $this->prosesApdData($request);
            $laporan->sebab_utama = $this->prosesSebabUtama($request);
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

            try {
                $firstApprover = User::find($firstApproverId);
                if ($firstApprover) {
                    $laporan->load('pembuatLaporan'); 
                    SendApprovalEmailJob::dispatch($laporan, $firstApprover);
                }
            } catch(\Exception $e) {
                Log::error('Gagal mengirim email persetujuan awal: ' . $e->getMessage());
            }

            return redirect()->route('accidents-report.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan laporan kecelakaan: ' . $e->getMessage() . ' di file ' . $e->getFile() . ' baris ' . $e->getLine());
            
            return back()->withInput();
        }
    }

    public function approve(Request $request, LaporanKecelakaan $laporan)
    {
        $status = $laporan->approvalStatus;
        if (!$status || $status->current_approver_id !== Auth::id()) {
            return view('safetyboard.partials.feedback', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk melakukan tindakan ini.'
            ]);
        }
        DB::beginTransaction();
        try {
            $currentApproverField = $this->getCurrentApproverField($laporan);
            if ($currentApproverField === null) {
                  return view('safetyboard.partials.feedback', [
                    'type' => 'error',
                    'message' => 'Status laporan tidak valid untuk persetujuan.'
                ]);
            }
            $currentIndex = array_search($currentApproverField, $this->approvalOrder);
            $laporan->approvalHistories()->create([
                'user_id' => Auth::id(),
                'action' => 'approved',
                'notes' => 'Menyetujui laporan sebagai ' . $this->getRoleName($currentApproverField),
            ]);
            if ($currentIndex === count($this->approvalOrder) - 1) {
                // Ini adalah persetujuan terakhir (oleh GM)
                $status->update(['status' => 'approved', 'current_approver_id' => null]);

                // --- KIRIM NOTIFIKASI LAPORAN DISETUJUI ---
                DB::afterCommit(function () use ($laporan) {
                    try {
                        SendReportStatusEmailJob::dispatch($laporan, 'approved');
                    } catch (\Exception $e) {
                        Log::error('Gagal mengirim email notifikasi laporan disetujui: ' . $e->getMessage());
                    }
                });
                // --- AKHIR NOTIFIKASI ---

            } else {
                $nextApproverField = $this->approvalOrder[$currentIndex + 1];
                $nextApproverId = $laporan->{$nextApproverField};
                $nextStatus = 'pending_' . str_replace('_id', '', $nextApproverField);
                $status->update(['status' => $nextStatus, 'current_approver_id' => $nextApproverId]);

                DB::afterCommit(function () use ($laporan, $nextApproverId) {
                    try {
                        $nextApprover = User::find($nextApproverId);
                        if ($nextApprover) {
                            $laporan->load('pembuatLaporan');
                            SendApprovalEmailJob::dispatch($laporan, $nextApprover);
                        }
                    } catch(\Exception $e) {
                         Log::error('Gagal mengirim email persetujuan lanjutan: ' . $e->getMessage());
                    }
                });
            }
            DB::commit();
            
             return view('safetyboard.partials.feedback', [
                'type' => 'success',
                'message' => 'Laporan berhasil disetujui. Proses persetujuan akan dilanjutkan ke tahap berikutnya.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyetujui laporan: ' . $e->getMessage());
             return view('safetyboard.partials.feedback', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan internal saat mencoba menyetujui laporan. Silakan coba lagi.'
            ]);
        }
    }

    public function reject(Request $request, LaporanKecelakaan $laporan)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:10'
        ]);
        if ($validator->fails()) {
            return view('safetyboard.partials.feedback', [
                'type' => 'error',
                'message' => 'Validasi gagal: Alasan penolakan wajib diisi dan minimal 10 karakter.'
            ]);
        }
        $status = $laporan->approvalStatus;
        if (!$status || $status->current_approver_id !== Auth::id()) {
              return view('safetyboard.partials.feedback', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk melakukan tindakan ini.'
            ]);
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

            // --- KIRIM NOTIFIKASI LAPORAN DITOLAK ---
            DB::afterCommit(function () use ($laporan, $request) {
                try {
                    SendReportStatusEmailJob::dispatch($laporan, 'rejected', $request->rejection_reason);
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim email notifikasi laporan ditolak: ' . $e->getMessage());
                }
            });
            // --- AKHIR NOTIFIKASI ---
            
            return view('safetyboard.partials.feedback', [
                'type' => 'success',
                'message' => 'Laporan telah berhasil ditolak. Pembuat laporan akan diberi notifikasi untuk revisi.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menolak laporan: ' . $e->getMessage());
             return view('safetyboard.partials.feedback', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan internal saat mencoba menolak laporan. Silakan coba lagi.'
            ]);
        }
    }

    public function create()
    {
        $gms = User::role('gm')->orderBy('name')->get();
        $hseManagers = User::role('asmenHse')->orderBy('name')->get();
        $deptHeads = User::role('DepHse')->orderBy('name')->get();
        $allUsers = User::orderBy('name')->get();
        $laporan = null;
        return view('safetyboard.form', [
            'gms' => $gms,
            'hseManagers' => $hseManagers,
            'deptHeads' => $deptHeads,
            'allUsers' => $allUsers,
            'laporan' => $laporan,
        ]);
    }

    public function show(LaporanKecelakaan $laporan)
    {
        $baseNomorForm = explode('-REV', $laporan->nomor_form)[0];
        $latestVersion = LaporanKecelakaan::where(DB::raw('SUBSTRING_INDEX(nomor_form, "-REV", 1)'), $baseNomorForm)
                                            ->orderBy('revision_number', 'desc')
                                            ->first();

        if ($latestVersion && $laporan->id !== $latestVersion->id) {
            return redirect()->route('accidents-report.show', $latestVersion->nomor_form);
        }

        $laporan->load('approvalStatus', 'approvalHistories.user', 'pembuatLaporan', 'revisedFrom');
        $currentApproverField = $this->getCurrentApproverField($laporan);
        return view('safetyboard.show', compact('laporan', 'currentApproverField'));
    }

    public function revise(LaporanKecelakaan $laporan)
    {
        $baseNomorForm = explode('-REV', $laporan->nomor_form)[0];
        $latestVersion = LaporanKecelakaan::where(DB::raw('SUBSTRING_INDEX(nomor_form, "-REV", 1)'), $baseNomorForm)
                                            ->orderBy('revision_number', 'desc')
                                            ->first();

        if ($latestVersion && $laporan->id !== $latestVersion->id) {
            return redirect()->route('accidents-report.revise', $latestVersion->nomor_form);
        }

        if ($laporan->pembuat_laporan_id !== Auth::id()) {
            return redirect()->route('accidents-report.index');
        }

        if ($laporan->approvalStatus?->status !== 'rejected') {
            return redirect()->route('accidents-report.show', $laporan->nomor_form);
        }

        $laporan->load('biayaPerawatan', 'saranPerbaikan');
        $gms = User::role('gm')->orderBy('name')->get();
        $hseManagers = User::role('asmenHse')->orderBy('name')->get();
        $deptHeads = User::role('DepHse')->orderBy('name')->get();
        $allUsers = User::orderBy('name')->get();

        return view('safetyboard.form', [
            'laporan' => $laporan,
            'gms' => $gms,
            'hseManagers' => $hseManagers,
            'deptHeads' => $deptHeads,
            'allUsers' => $allUsers,
            'isRevision' => true
        ]);
    }

    private function prosesGambarEditor(string $content): string
    {
        if (empty($content)) return '';
        $dom = new \DOMDocument();
        @$dom->loadHtml(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (strpos($src, 'data:image/') === 0) {
                try {
                    list($type, $data) = explode(';', $src);
                    list(, $data)      = explode(',', $data);
                    $data = base64_decode($data);
                    $image_type = explode('/', $type)[1];
                    $extension = $image_type;
                    $path = 'editor-uploads/' . uniqid() . date('YmdHis') . '.' . $extension;
                    Storage::disk('public')->put($path, $data);
                    $img->setAttribute('src', Storage::url($path));
                    $img->removeAttribute('data-mce-src');
                } catch (\Exception $e) {
                    Log::error('Gagal memproses gambar base64: ' . $e->getMessage());
                    continue;
                }
            }
        }
        return $dom->saveHTML();
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

    private function prosesBiaya(LaporanKecelakaan $laporan, array $data): void
    {
        if (!empty($data['biaya_harga'])) {
            foreach ($data['biaya_harga'] as $index => $harga) {
                if (!empty($harga) && isset($data['biaya_kategori'][$index])) {
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

    private function prosesSebabUtama(Request $request): ?array
    {
        $results = [];
        $sebabA = $request->input('sebab_utama_a');
        $sebabB = $request->input('sebab_utama_b');

        if ($sebabA) {
            $deskripsi = ($sebabA === 'A-lain')
                ? $request->input('sebab_a_lain_input')
                : (str_starts_with($sebabA, 'A - ') ? substr($sebabA, 4) : '');
            if (!empty($deskripsi)) {
                $results[] = ['kategori' => 'A', 'deskripsi' => $deskripsi];
            }
        }

        if ($sebabB) {
            $deskripsi = ($sebabB === 'B-lain')
                ? $request->input('sebab_b_lain_input')
                : (str_starts_with($sebabB, 'B - ') ? substr($sebabB, 4) : '');
            if (!empty($deskripsi)) {
                $results[] = ['kategori' => 'B', 'deskripsi' => $deskripsi];
            }
        }

        return !empty($results) ? $results : null;
    }
}