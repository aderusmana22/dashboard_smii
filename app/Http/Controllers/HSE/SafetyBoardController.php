<?php

namespace App\Http\Controllers\HSE;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SafetyBoardController extends Controller
{
    /**
     * Handles the initial page load for the dashboard.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Call the unified data fetching method, indicating it's for initial HTML load
        $data = $this->getSafetyData(true);
        return view('dashboard.dashboardSafetyBoard', $data);
    }

    /**
     * Fetches and prepares all dashboard data, used for both initial load and AJAX.
     * @param bool $forHtml True if data is for initial HTML render, false for JSON (AJAX).
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getSafetyData($forHtml = false)
    {
        // --- User's provided calculations and mock data (adapted) ---
        // These values would typically come from your database or an external API
        $last_accident_date = Carbon::parse('2023-10-24');
        $record_days_without_accident = 3103; // Example: Best record previously achieved
        $accidents_this_month = 0; // Example: Accidents recorded this month
        // --------------------------------------------------------------------------------

        $today = Carbon::now();

        // 1. Calculate TOTAL HARI KERJA SAMPAI BULAN INI
        $start_of_year = Carbon::now()->startOfYear();
        $total_working_days_until_this_month = $start_of_year->diffInDays($today) + 1; // +1 to include today

        // 2. Calculate TOTAL HARI TANPA KECELAKAAN
        $total_days_without_accident = $last_accident_date->diffInDays($today);

        // 3. Calculate TARGET HARI KERJA TAHUN INI (total days in the current year, considering leap year)
        $current_year = Carbon::now()->year;
        $target_working_days_this_year = Carbon::createFromDate($current_year, 12, 31)->dayOfYear; // 366 if leap year, 365 otherwise
        // --- End of user's adapted calculations ---

        // Fetch weather data for Pulo Gadung, East Jakarta
        $weatherData = $this->_fetchWeatherData();

        // Prepare the final data array
        $data = [
            'total_days_without_accident_until_this_month' => $total_days_without_accident,
            'total_working_days_until_this_month' => $total_working_days_until_this_month,
            'target_working_days_this_year' => $target_working_days_this_year,
            'accidents_this_month' => $accidents_this_month,
            'last_accident_date' => $last_accident_date->format('d M Y'), // Format as "DD Mon. YYYY"
            'record_days_without_accident' => $record_days_without_accident,
            'current_time' => $today->format('d F Y H:i:s'), // Add current time for header refresh
            'current_temperature' => $weatherData['temperature'],
            'weather_condition' => $weatherData['condition'],
            'weather_icon_url' => $weatherData['icon_url'],
        ];

        // Return data as array for Blade view or as JSON for AJAX
        if ($forHtml) {
            return $data;
        } else {
            return response()->json($data);
        }
    }

    /**
     * Fetches weather data from Open-Meteo for Pulo Gadung, East Jakarta.
     * @return array Contains temperature, condition, and icon_url.
     */
    private function _fetchWeatherData()
    {
        // Coordinates for Pulo Gadung, East Jakarta
        $latitude = -6.18;
        $longitude = 106.90;
        $timezone = 'Asia/Jakarta'; // Jakarta timezone

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
                $temperature = round($data['current_weather']['temperature']); // Round to nearest integer
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
            // Log the error for debugging purposes
            Log::error('Error fetching weather data from Open-Meteo: ' . $e->getMessage());
        }

        // Return default/fallback data if API call fails or data is not as expected
        return [
            'temperature' => 'N/A',
            'condition' => 'Tidak Tersedia',
            'icon_url' => 'https://placehold.co/50x50/FFF/000?text=❓', // Generic default icon
        ];
    }

    /**
     * Translates WMO Weather interpretation codes from Open-Meteo to Bahasa Indonesia conditions.
     * @param int $code The WMO weather code.
     * @return string Human-readable weather condition.
     */
    private function _getWeatherCondition($code)
    {
        switch ($code) {
            case 0:
                return 'Cerah';
            case 1:
                return 'Sebagian Cerah';
            case 2:
                return 'Sebagian Berawan';
            case 3:
                return 'Berawan Penuh';
            case 45:
                return 'Kabut';
            case 48:
                return 'Kabut Beku';
            case 51:
                return 'Gerimis Ringan';
            case 53:
                return 'Gerimis Sedang';
            case 55:
                return 'Gerimis Lebat';
            case 56:
                return 'Gerimis Membeku Ringan';
            case 57:
                return 'Gerimis Membeku Lebat';
            case 61:
                return 'Hujan Ringan';
            case 63:
                return 'Hujan Sedang';
            case 65:
                return 'Hujan Lebat';
            case 66:
                return 'Hujan Membeku Ringan';
            case 67:
                return 'Hujan Membeku Lebat';
            case 71:
                return 'Salju Ringan';
            case 73:
                return 'Salju Sedang';
            case 75:
                return 'Salju Lebat';
            case 77:
                return 'Butiran Salju';
            case 80:
                return 'Hujan Deras Ringan';
            case 81:
                return 'Hujan Deras Sedang';
            case 82:
                return 'Hujan Deras Lebat';
            case 85:
                return 'Hujan Salju Ringan';
            case 86:
                return 'Hujan Salju Lebat';
            case 95:
                return 'Badai Petir';
            case 96:
                return 'Badai Petir dengan Hujan Es Ringan';
            case 99:
                return 'Badai Petir dengan Hujan Es Lebat';
            default:
                return 'Tidak Diketahui';
        }
    }

    /**
     * Returns a placeholder icon URL based on weather code.
     * In a real application, these would be paths to actual icon assets.
     * @param int $code The WMO weather code.
     * @return string URL for the corresponding weather icon.
     */
    private function _getWeatherIconUrl($code)
    {
        switch ($code) {
            case 0:
                return '<i class="fa-solid fa-sun fa-3x"></i>'; // Clear sky
            case 1:
                return '<i class="fa-solid fa-cloud-sun fa-3x"></i>'; // Mostly clear
            case 2:
                return '<i class="fa-solid fa-cloud fa-3x"></i>'; // Partly cloudy
            case 3:
                return '<i class="fa-solid fa-cloud fa-3x"></i>'; // Overcast (can use same as partly cloudy or a more dense cloud)
            case 45:
            case 48:
                return '<i class="fa-solid fa-smog fa-3x"></i>'; // Fog
            case 51:
            case 53:
            case 55:
            case 56:
            case 57:
                return '<i class="fa-solid fa-cloud-showers-heavy fa-3x"></i>'; // Drizzle
            case 61:
            case 63:
            case 65:
            case 80:
            case 81:
            case 82:
                return '<i class="fa-solid fa-cloud-showers-heavy fa-3x"></i>'; // Rain
            case 66:
            case 67:
                return '<i class="fa-solid fa-cloud-meatball fa-3x"></i>'; // Freezing Rain (using something indicative)
            case 71:
            case 73:
            case 75:
            case 77:
            case 85:
            case 86:
                return '<i class="fa-solid fa-snowflake fa-3x"></i>'; // Snow
            case 95:
            case 96:
            case 99:
                return '<i class="fa-solid fa-cloud-bolt fa-3x"></i>'; // Thunderstorm
            default:
                return '<i class="fa-solid fa-question fa-3x"></i>'; // Unknown
        }
    }

    /**
     * Show the form for creating a new safety board entry.
     */
    public function create()
    {
        return view('dashboard.dashboardSafetyBoardCreate');
    }

    /**
     * Store a newly created safety board entry in storage.
     */
    public function store(Request $request)
    {
        // Logic to validate and store the safety board data
        return redirect()->route('hse.safety_board.index');
    }
}
