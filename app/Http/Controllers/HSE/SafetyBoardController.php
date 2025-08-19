<?php

namespace App\Http\Controllers\HSE;

use App\Http\Controllers\Controller;
use App\Models\SafetyBoard; // <-- Import model yang sudah dibuat
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SafetyBoardController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Memanggil method data terpusat untuk render HTML awal
        $data = $this->getSafetyData(true);
        return view('dashboard.dashboardSafetyBoard', $data);
    }

    /**
     * Mengambil dan menyiapkan semua data dashboard, digunakan untuk render awal dan API AJAX.
     * @param bool $forHtml True jika data untuk render HTML, false untuk JSON (AJAX).
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getSafetyData($forHtml = false)
    {
        // --- Mengambil data dinamis dari database ---
        // Mengambil record paling baru dari tabel safety_boards.
        $safetyData = SafetyBoard::latest()->first();

        // --- Menyiapkan nilai default jika data di database kosong ---
        // Jika $safetyData ada DAN kolom last_accident_date tidak null, gunakan tanggal tsb.
        // Jika tidak, gunakan tanggal hari ini agar kalkulasi hari tanpa kecelakaan menjadi 0.
        $last_accident_date = $safetyData && $safetyData->last_accident_date ? $safetyData->last_accident_date : Carbon::now();

        // Jika $safetyData ada, gunakan rekornya. Jika tidak, default ke 0.
        $record_days_without_accident = $safetyData ? $safetyData->record_days_without_accident : 0;

        // Jika $safetyData ada DAN marquee_text tidak kosong, pecah string menjadi array.
        // Jika tidak, sediakan array dengan satu pesan default.
        $marquee_texts = $safetyData && $safetyData->marquee_text
            ? explode('***', $safetyData->marquee_text)
            : ['Selamat Bekerja dengan Aman! Utamakan Keselamatan.'];

        // Data ini bisa dikembangkan lebih lanjut untuk diambil dari database jika diperlukan.
        $accidents_this_month = 0;
        // --------------------------------------------------------------------------------

        $today = Carbon::now();

        // 1. Kalkulasi: TOTAL HARI TANPA KECELAKAAN
        // Dihitung dari selisih hari antara tanggal kecelakaan terakhir dari DB dan hari ini.
        $total_days_without_accident = $last_accident_date->diffInDays($today);

        // 2. Kalkulasi: TOTAL HARI KERJA SAMPAI BULAN INI
        // Dihitung dari 1 Januari tahun ini sampai hari ini.
        $start_of_year = Carbon::now()->startOfYear();
        $total_working_days_until_this_month = $start_of_year->diffInDays($today) + 1; // +1 untuk ikutkan hari ini

        // 3. Kalkulasi: TARGET HARI KERJA TAHUN INI
        // Dihitung dari jumlah total hari dalam tahun berjalan (memperhitungkan tahun kabisat).
        $target_working_days_this_year = Carbon::now()->endOfYear()->dayOfYear;

        // Mengambil data cuaca dari API eksternal
        $weatherData = $this->_fetchWeatherData();

        // Menyiapkan array data final untuk dikirim ke view atau sebagai JSON
        $data = [
            'total_days_without_accident_until_this_month' => $total_days_without_accident,
            'total_working_days_until_this_month' => $total_working_days_until_this_month,
            'target_working_days_this_year' => $target_working_days_this_year,
            'accidents_this_month' => $accidents_this_month,
            'last_accident_date' => $last_accident_date->format('d M Y'),
            'record_days_without_accident' => $record_days_without_accident,
            'current_time' => $today->format('d F Y H:i:s'),
            'current_temperature' => $weatherData['temperature'],
            'weather_condition' => $weatherData['condition'],
            'weather_icon_url' => $weatherData['icon_url'],
            'marquee_texts' => $marquee_texts, // <-- Data marquee dinamis
        ];

        // Mengembalikan data sebagai array untuk Blade atau sebagai JSON untuk API
        if ($forHtml) {
            return $data;
        } else {
            return response()->json($data);
        }
    }

    /**
     * Mengambil data cuaca dari Open-Meteo untuk Pulo Gadung, Jakarta Timur.
     * @return array Berisi temperature, condition, dan icon_url.
     */
    private function _fetchWeatherData()
    {
        // Koordinat Pulo Gadung, Jakarta Timur
        $latitude = -6.205053840196611;
        $longitude = 106.91191809343205;
        $timezone = 'Asia/Jakarta';

        try {
            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current_weather' => true,
                'temperature_unit' => 'celsius',
                'timezone' => $timezone,
            ]);

            $data = $response->json();

            if (isset($data['current_weather'])) {
                $temperature = round($data['current_weather']['temperature']);
                $weatherCode = $data['current_weather']['weathercode'];
                $condition = $this->_getWeatherCondition($weatherCode);
                $iconUrl = $this->_getWeatherIconUrl($weatherCode);
                return [
                    'temperature' => $temperature,
                    'condition' => $condition,
                    'icon_url' => $iconUrl,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching weather data from Open-Meteo: ' . $e->getMessage());
        }

        // Data default jika API gagal
        return [
            'temperature' => 'N/A',
            'condition' => 'Tidak Tersedia',
            'icon_url' => '<i class="fa-solid fa-question fa-3x"></i>',
        ];
    }

    /**
     * Menerjemahkan kode cuaca WMO dari Open-Meteo ke Bahasa Indonesia.
     * @param int $code Kode cuaca WMO.
     * @return string Kondisi cuaca yang dapat dibaca manusia.
     */
    private function _getWeatherCondition($code)
    {
        $conditions = [
            0 => 'Cerah',
            1 => 'Sebagian Cerah',
            2 => 'Sebagian Berawan',
            3 => 'Berawan Penuh',
            45 => 'Kabut',
            48 => 'Kabut Beku',
            51 => 'Gerimis Ringan',
            53 => 'Gerimis Sedang',
            55 => 'Gerimis Lebat',
            56 => 'Gerimis Membeku Ringan',
            57 => 'Gerimis Membeku Lebat',
            61 => 'Hujan Ringan',
            63 => 'Hujan Sedang',
            65 => 'Hujan Lebat',
            66 => 'Hujan Membeku Ringan',
            67 => 'Hujan Membeku Lebat',
            71 => 'Salju Ringan',
            73 => 'Salju Sedang',
            75 => 'Salju Lebat',
            77 => 'Butiran Salju',
            80 => 'Hujan Deras Ringan',
            81 => 'Hujan Deras Sedang',
            82 => 'Hujan Deras Lebat',
            85 => 'Hujan Salju Ringan',
            86 => 'Hujan Salju Lebat',
            95 => 'Badai Petir',
            96 => 'Badai Petir & Hujan Es Ringan',
            99 => 'Badai Petir & Hujan Es Lebat',
        ];
        return $conditions[$code] ?? 'Tidak Diketahui';
    }

    /**
     * Mengembalikan markup HTML ikon Font Awesome berdasarkan kode cuaca.
     * @param int $code Kode cuaca WMO.
     * @return string String HTML untuk ikon.
     */
    private function _getWeatherIconUrl($code)
    {
        switch ($code) {
            case 0: return '<i class="fa-solid fa-sun fa-3x"></i>';
            case 1: return '<i class="fa-solid fa-cloud-sun fa-3x"></i>';
            case 2:
            case 3: return '<i class="fa-solid fa-cloud fa-3x"></i>';
            case 45:
            case 48: return '<i class="fa-solid fa-smog fa-3x"></i>';
            case 51:
            case 53:
            case 55:
            case 56:
            case 57:
            case 61:
            case 63:
            case 65:
            case 80:
            case 81:
            case 82: return '<i class="fa-solid fa-cloud-showers-heavy fa-3x"></i>';
            case 66:
            case 67: return '<i class="fa-solid fa-cloud-meatball fa-3x"></i>';
            case 71:
            case 73:
            case 75:
            case 77:
            case 85:
            case 86: return '<i class="fa-solid fa-snowflake fa-3x"></i>';
            case 95:
            case 96:
            case 99: return '<i class="fa-solid fa-cloud-bolt fa-3x"></i>';
            default: return '<i class="fa-solid fa-question fa-3x"></i>';
        }
    }

    /**
     * Menampilkan form untuk membuat entri safety board baru.
     * (Placeholder untuk pengembangan di masa depan)
     */
    public function create()
    {
        // Anda bisa membuat view untuk form input data di sini
        // return view('dashboard.dashboardSafetyBoardCreate');
        return "Halaman untuk membuat/mengedit data Safety Board.";
    }

    /**
     * Menyimpan entri safety board baru ke dalam storage.
     * (Placeholder untuk pengembangan di masa depan)
     */
    public function store(Request $request)
    {
        // Logika untuk validasi dan menyimpan data dari form
        // return redirect()->route('hse.safety_board.index');
    }
}