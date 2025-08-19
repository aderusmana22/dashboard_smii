<?php

namespace App\Http\Controllers;

use App\Models\LaporanKecelakaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LaporanKecelakaanController extends Controller
{
    /**
     * Menampilkan daftar semua laporan kecelakaan.
     */
    public function index()
    {
        $laporan = LaporanKecelakaan::latest()->paginate(15);
        // Pastikan view ini ada: resources/views/safetyboard/index.blade.php
        return view('safetyboard.index', compact('laporan'));
    }

    /**
     * Menampilkan form untuk membuat laporan baru.
     */
    public function create()
    {
        // Path view ini sudah benar jika file Anda ada di:
        // resources/views/safetyboard/form.blade.php
        return view('safetyboard.form');
    }

    /**
     * Menyimpan laporan baru ke database.
     */
    public function store(Request $request)
    {
        // --- PERBAIKAN 1: Validasi yang lebih lengkap & mencakup semua field form ---
        $validatedData = $request->validate([
            'nomor_form' => 'nullable|string|max:255|unique:laporan_kecelakaans,nomor_form',
            'date' => 'required|string',
            'kategori_kecelakaan' => 'required|string|max:255',
            'kategori_dampak' => 'required|string|max:255',
            'waktu_kecelakaan' => 'required|date',
            'lokasi_kecelakaan' => 'required|string|max:255',
            'tipe_kecelakaan' => 'nullable|string|max:255', // Field baru
            'bagian_terluka' => 'nullable|string|max:255', // Field baru
            'uraian_kejadian' => 'required|string',
            'nama_korban' => 'required|string|max:255',
            'nik' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'usia' => 'nullable|string|max:255', // Field baru (dari JS)
            'tanggal_masuk' => 'nullable|date',
            'masa_kerja' => 'nullable|string|max:255', // Field baru (dari JS)
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
            'pertolongan' => 'required|string|max:255',
            'p3k_oleh' => 'nullable|string|max:255',
            'jam_p3k' => 'nullable|date_format:H:i',
            'akibat_kecelakaan' => 'required|string|max:255',
            'waktu_hilang' => 'nullable|integer|min:0',
            'analisa_masalah' => 'nullable|string', // Field baru
            'tindakan_pencegahan' => 'nullable|string', // Field baru
            'rekomendasi' => 'nullable|string', // Field baru
            'biaya_harga.*' => 'nullable|numeric|min:0',
            'biaya_kategori.*' => 'nullable|string|max:255',
            'perbaikan_tindakan.*' => 'nullable|string',
            'perbaikan_pic.*' => 'nullable|string|max:255',
            'perbaikan_due_date.*' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // --- PERBAIKAN 2: Gunakan data yang sudah tervalidasi ---
            $laporan = new LaporanKecelakaan();
            $laporan->fill($validatedData);

            // Konversi format tanggal dari 'd F Y' ke 'Y-m-d'
            $laporan->date = Carbon::createFromFormat('d F Y', $validatedData['date'])->format('Y-m-d');

            // Proses data APD dan Sebab Utama
            $laporan->apd_data = $this->prosesApdData($request);
            list($kategori, $deskripsi) = $this->prosesSebabUtama($request);
            $laporan->sebab_utama_kategori = $kategori;
            $laporan->sebab_utama_deskripsi = $deskripsi;

            $laporan->save();

            // Proses dan simpan Biaya Perawatan
            if (!empty($validatedData['biaya_harga'])) {
                foreach ($validatedData['biaya_harga'] as $index => $harga) {
                    if (!empty($harga)) {
                        $laporan->biayaPerawatan()->create([
                            'harga' => $harga,
                            'kategori' => $validatedData['biaya_kategori'][$index] ?? 'Lainnya',
                        ]);
                    }
                }
            }

            // Proses dan simpan Saran Perbaikan
            if (!empty($validatedData['perbaikan_tindakan'])) {
                foreach ($validatedData['perbaikan_tindakan'] as $index => $tindakan) {
                    if (!empty($tindakan)) {
                        $laporan->saranPerbaikan()->create([
                            'tindakan' => $tindakan,
                            'pic' => $validatedData['perbaikan_pic'][$index],
                            'due_date' => $validatedData['perbaikan_due_date'][$index],
                        ]);
                    }
                }
            }

            DB::commit();

            // --- PERBAIKAN 3: Sesuaikan nama route dengan file web.php ---
            return redirect()->route('accidents-report.index')->with('success', 'Laporan kecelakaan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan laporan kecelakaan: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.');
        }
    }

    /**
     * Helper function untuk memproses data APD dari request.
     */
    private function prosesApdData(Request $request): array
    {
        $apds = [
            'sarung_tangan', 'sepatu', 'helm', 'masker',
            'kacamata', 'celemek', 'kedok', 'hairnet'
        ];
        $apdData = [];

        foreach ($apds as $key) {
            // Mengecek dari checkbox yang tidak memiliki value
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

    /**
     * Helper function untuk memproses data Sebab Utama.
     */
    private function prosesSebabUtama(Request $request): array
    {
        $sebab = $request->input('sebab_utama');
        if (!$sebab) {
            return [null, null];
        }

        // Cek jika radio "lain-lain" yang dipilih
        if ($request->has('sebab_a_lain_input') && $request->input('sebab_utama') == 'on') {
            return ['A', $request->input('sebab_a_lain_input')];
        }
        if ($request->has('sebab_b_lain_input') && $request->input('sebab_utama') == 'on') {
            return ['B', $request->input('sebab_b_lain_input')];
        }
        
        // Jika radio lain yang dipilih
        if (str_contains($sebab, ' - ')) {
            $parts = explode(' - ', $sebab, 2);
            return [$parts[0], $parts[1]];
        }

        return [null, null];
    }
}