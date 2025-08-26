<?php

namespace App\Http\Controllers;

use App\Models\LaporanKecelakaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\LaporanApprovalStatus;

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
        $approvalStatusTable = (new LaporanApprovalStatus)->getTable();

        // PERUBAHAN UTAMA 1: Query dasar sekarang HANYA mengambil laporan yang aktif.
        $query = LaporanKecelakaan::where('laporan_kecelakaans.is_active', true)
            ->with(['approvalStatus', 'pembuatLaporan'])
            ->leftJoin($approvalStatusTable, 'laporan_kecelakaans.id', '=', $approvalStatusTable . '.laporan_kecelakaan_id')
            ->select('laporan_kecelakaans.*');

        // --- FILTERING ---
        if ($request->filled('nomor_form')) {
            $query->where('laporan_kecelakaans.nomor_form', 'like', '%' . $request->nomor_form . '%');
        }
        if ($request->filled('nama_korban')) {
            $query->where('laporan_kecelakaans.nama_korban', 'like', '%' . $request->nama_korban . '%');
        }
        if ($request->filled('status')) {
            $query->where($approvalStatusTable . '.status', $request->status);
        }
        if ($request->filled('date_start')) {
            $query->whereDate('laporan_kecelakaans.date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('laporan_kecelakaans.date', '<=', $request->date_end);
        }

        // PERUBAHAN UTAMA 2: Hitung total record setelah filtering, sebelum pagination.
        $totalFiltered = $query->count();
        $totalData = LaporanKecelakaan::where('is_active', true)->count();

        // --- SORTING ---
        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderColumnName = $request->input('columns.' . $orderColumnIndex . '.name');
            $orderDirection = $request->input('order.0.dir');

            if ($orderColumnName) {
                if ($orderColumnName === 'approval_statuses.status') {
                    $query->orderBy($approvalStatusTable . '.status', $orderDirection);
                } else {
                    $query->orderBy($orderColumnName, $orderDirection);
                }
            }
        } else {
            $query->latest('laporan_kecelakaans.created_at');
        }

        // --- PAGINATION ---
        if ($request->filled('length') && $request->length != -1) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $laporan = $query->get();

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

            // PERUBAHAN UTAMA 3: Logika baru untuk penomoran revisi
            if ($request->has('revised_from_id')) {
                // Mengambil laporan original yang akan direvisi
                $originalReport = LaporanKecelakaan::with('approvalStatus')->findOrFail($request->input('revised_from_id'));

                // 1. Menonaktifkan laporan lama agar tidak muncul di daftar utama
                $originalReport->is_active = false;
                $originalReport->save();

                // 2. Mengupdate status approval laporan lama menjadi "revised"
                if ($originalReport->approvalStatus) {
                    $originalReport->approvalStatus->update(['status' => 'revised']);
                }
                
                // 3. Membentuk nomor form revisi yang baru
                //    Ini akan mengambil basis nomor (misal: HSE-2025-5) bahkan jika sudah ada -REV sebelumnya
                $baseNomorForm = explode('-REV', $originalReport->nomor_form)[0];
                $newRevisionNumber = $originalReport->revision_number + 1;

                // Format baru: HSE-2025-5-REV1, HSE-2025-5-REV2, dst.
                $laporan->nomor_form = $baseNomorForm . '-REV' . $newRevisionNumber;
                $laporan->revision_number = $newRevisionNumber;
                $laporan->revised_from_id = $originalReport->id;

            } else {
                // Logika untuk Laporan Baru (tanpa revisi)
                $year = date('Y');
                // Menghitung hanya laporan utama (bukan revisi) untuk menentukan nomor berikutnya
                $latestReportCount = LaporanKecelakaan::whereYear('created_at', $year)
                                        ->whereNull('revised_from_id') 
                                        ->lockForUpdate()
                                        ->count();
                $nextNumber = $latestReportCount + 1;
                $laporan->nomor_form = sprintf("HSE-%s-%d", $year, $nextNumber);
            }
            
            // `is_active` sudah default true dari migrasi, jadi tidak perlu diset manual
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

    public function approve(Request $request, LaporanKecelakaan $laporan)
    {
        $status = $laporan->approvalStatus;
        if (!$status || $status->current_approver_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk aksi ini.'], 403);
        }

        DB::beginTransaction();
        try {
            $currentApproverField = $this->getCurrentApproverField($laporan);
            if ($currentApproverField === null) {
                return response()->json(['success' => false, 'message' => 'Status laporan tidak valid untuk persetujuan.'], 400);
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
            return response()->json(['success' => true, 'message' => 'Laporan berhasil disetujui.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyetujui laporan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyetujui laporan.'], 500);
        }
    }

    public function reject(Request $request, LaporanKecelakaan $laporan)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
        }

        $status = $laporan->approvalStatus;
        if (!$status || $status->current_approver_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk aksi ini.'], 403);
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
            return response()->json(['success' => true, 'message' => 'Laporan telah ditolak.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menolak laporan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menolak laporan.'], 500);
        }
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name']);
        $laporan = null;
        return view('safetyboard.form', compact('users', 'laporan'));
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

    private function prosesGambarEditor(string $content): string
    {
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