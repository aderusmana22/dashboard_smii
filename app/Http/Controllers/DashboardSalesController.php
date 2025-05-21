<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardSalesController extends Controller
{
    protected $countryMapping = [
        "TAIWAN" => "Taiwan",
        "PHILLIPPINES" => "Philippines",
        "MALAYSIA" => "Malaysia",
        "MYANMAR" => "Burma",
        "EXPORT AUSTRALIA" => "New Zealand", // Note: Could include NZ, so Wellington mapping needs care
        "SRILANKA" => "Sri Lanka",
        "UNI ARAB EMIRATES" => "United Arab Emirates",
        "HONGKONG" => "Hong Kong",
        "PEOPLE'S REPUBLIC OF CHINA" => "China",
        "BRAZIL" => "Brazil",
        "UNITED STATES OF AMERICA" => "United States",
        "GERMANY" => "Germany",
        "INDIA" => "India",
        "CANADA" => "Canada",
        "SOUTH AFRICA" => "South Africa",
        "NEPAL" => "Nepal",
        "FIJI" => "Fiji",
        "RUSSIAN FEDERATION" => "Russia",
        "NORTH KOREA" => "Korea, North",
        "SOUTH KOREA" => "Korea, South"
    ];

    protected $indonesiaSuperRegionKeys = [
        "REGION1A", "REGION1B", "REGION1C", "REGION1D",
        "REGION2A", "REGION2B", "REGION2C", "REGION2D",
        "REGION3A", "REGION3B", "REGION3C",
        "REGION4A", "REGION4B",
        "KEYACCOUNT",
        "COMMERCIAL"
    ];

    protected $rawCodeCmmtToSuperRegionKeyMap;

    protected $indonesiaCityData = [
        // ... (your extensive list of Indonesian cities remains here) ...
        "Tangerang Selatan" => ['lat' => -6.2887, 'lng' => 106.7194, 'db_key' => "TANGERANG SELATAN"],
        "Bali" => ['lat' => -8.3405, 'lng' => 115.0919, 'db_key' => "BALI"],
        "Denpasar" => ['lat' => -8.6705, 'lng' => 115.2126, 'db_key' => "DENPASAR"],
        "Bogor" => ['lat' => -6.5950, 'lng' => 106.8060, 'db_key' => "BOGOR"],
        "Semarang" => ['lat' => -6.9667, 'lng' => 110.4381, 'db_key' => "SEMARANG"],
        "Jakarta" => ['lat' => -6.2088, 'lng' => 106.8456, 'db_key' => "JAKARTA"],
        "Jakarta Pusat" => ['lat' => -6.1751, 'lng' => 106.8272, 'db_key' => "JAKARTA PUSAT"],
        "Jakarta Utara" => ['lat' => -6.1384, 'lng' => 106.8639, 'db_key' => "JAKARTA UTARA"],
        "Jakarta Barat" => ['lat' => -6.1676, 'lng' => 106.7663, 'db_key' => "JAKARTA BARAT"],
        "Jakarta Selatan" => ['lat' => -6.2615, 'lng' => 106.8106, 'db_key' => "JAKARTA SELATAN"],
        "Adm. Jakarta Selatan" => ['lat' => -6.2615, 'lng' => 106.8106, 'db_key' => "ADM. JAKARTA SELATAN"],
        "Jakarta Timur" => ['lat' => -6.2259, 'lng' => 106.9004, 'db_key' => "JAKARTA TIMUR"],
        "Bekasi" => ['lat' => -6.2383, 'lng' => 106.9756, 'db_key' => "BEKASI"],
        "Bekas" => ['lat' => -6.2383, 'lng' => 106.9756, 'db_key' => "BEKAS"],
        "Serang" => ['lat' => -6.1181, 'lng' => 106.1558, 'db_key' => "SERANG"],
        "Surabaya" => ['lat' => -7.2575, 'lng' => 112.7521, 'db_key' => "SURABAYA"],
        "Bandung" => ['lat' => -6.9175, 'lng' => 107.6191, 'db_key' => "BANDUNG"],
        "Medan" => ['lat' => 3.5952, 'lng' => 98.6722, 'db_key' => "MEDAN"],
        "Palembang" => ['lat' => -2.9761, 'lng' => 104.7754, 'db_key' => "PALEMBANG"],
        "Makassar" => ['lat' => -5.1477, 'lng' => 119.4327, 'db_key' => "MAKASSAR"],
        "Yogyakarta" => ['lat' => -7.7956, 'lng' => 110.3695, 'db_key' => "YOGYAKARTA"],
        "D.I.Yogyakarta" => ['lat' => -7.7956, 'lng' => 110.3695, 'db_key' => "D.I.YOGYAKARTA"],
        "DI Yogyakarta" => ['lat' => -7.7956, 'lng' => 110.3695, 'db_key' => "DI YOGYAKARTA"],
        "Sleman" => ['lat' => -7.7186, 'lng' => 110.3803, 'db_key' => "SLEMAN"],
        "Tangerang" => ['lat' => -6.1783, 'lng' => 106.6319, 'db_key' => "TANGERANG"],
        "Depok" => ['lat' => -6.4025, 'lng' => 106.7942, 'db_key' => "DEPOK"],
        "Cirebon" => ['lat' => -6.7066, 'lng' => 108.5570, 'db_key' => "CIREBON"],
        "Malang" => ['lat' => -7.9666, 'lng' => 112.6326, 'db_key' => "MALANG"],
        "Jember" => ['lat' => -8.1690, 'lng' => 113.7000, 'db_key' => "JEMBER"],
        "Kudus" => ['lat' => -6.8063, 'lng' => 110.8421, 'db_key' => "KUDUS"],
        "Pekanbaru" => ['lat' => 0.5071, 'lng' => 101.4478, 'db_key' => "PEKAN BARU"],
        "Balikpapan" => ['lat' => -1.2379, 'lng' => 116.8529, 'db_key' => "BALIKPAPAN"],
        "Banjarmasin" => ['lat' => -3.3167, 'lng' => 114.5900, 'db_key' => "BANJARMASIN"],
        "Pontianak" => ['lat' => -0.0277, 'lng' => 109.3425, 'db_key' => "KALIMANTAN BARAT"],
        "Padang" => ['lat' => -0.9471, 'lng' => 100.3616, 'db_key' => "PADANG"],
        "DKI Jakarta" => ['lat' => -6.2088, 'lng' => 106.8456, 'db_key' => "DKI JAKARTA"],
        "DKI Jakarta Raya" => ['lat' => -6.2088, 'lng' => 106.8456, 'db_key' => "DKI JAKARTA RAYA"],
        "Pasuruan" => ['lat' => -7.6455, 'lng' => 112.9075, 'db_key' => "PASURUAN"],
        "Pangkalpinang" => ['lat' => -2.1303, 'lng' => 106.1098, 'db_key' => "PANGKALPINANG"],
        "Bojonegoro" => ['lat' => -7.1566, 'lng' => 111.8886, 'db_key' => "BOJONEGORO"],
        "Sidoarjo" => ['lat' => -7.4478, 'lng' => 112.7183, 'db_key' => "SIDOARJO"],
        "Tulung Agung" => ['lat' => -8.0652, 'lng' => 111.9010, 'db_key' => "TULUNG AGUNG"],
        "Gorontalo" => ['lat' => 0.5400, 'lng' => 123.0640, 'db_key' => "GORONTALO"],
        "Palu" => ['lat' => -0.8977, 'lng' => 119.8657, 'db_key' => "PALU"],
        "Batam" => ['lat' => 1.0456, 'lng' => 104.0305, 'db_key' => "BATAM"],
        "Jambi" => ['lat' => -1.6102, 'lng' => 103.6131, 'db_key' => "JAMBI"],
        "Cikarang" => ['lat' => -6.3160, 'lng' => 107.1463, 'db_key' => "CIKARANG"],
        "Banten" => ['lat' => -6.4240, 'lng' => 106.1231, 'db_key' => "BANTEN"],
        "Samarinda" => ['lat' => -0.5021, 'lng' => 117.1537, 'db_key' => "SAMARINDA"],
        "Aceh" => ['lat' => 4.6951, 'lng' => 96.7494, 'db_key' => "ACEH"],
        "Banda Aceh" => ['lat' => 5.5483, 'lng' => 95.3238, 'db_key' => "BANDA ACEH"],
        "Kendari" => ['lat' => -3.9955, 'lng' => 122.5148, 'db_key' => "KENDARI"],
        "Pamekasan" => ['lat' => -7.1600, 'lng' => 113.4783, 'db_key' => "PAMEKASAN"],
        "Karawang" => ['lat' => -6.3025, 'lng' => 107.3060, 'db_key' => "KARAWANG"],
        "Penjaringan" => ['lat' => -6.1180, 'lng' => 106.7924, 'db_key' => "PENJARINGAN"],
        "Ternate" => ['lat' => 0.7896, 'lng' => 127.3748, 'db_key' => "TERNATE"],
        "Kupang" => ['lat' => -10.1778, 'lng' => 123.5976, 'db_key' => "KUPANG"],
        "Gresik" => ['lat' => -7.1646, 'lng' => 112.6508, 'db_key' => "GRESIK"],
        "Manado" => ['lat' => 1.4748, 'lng' => 124.8421, 'db_key' => "MANADO"],
        "Muara Bungo" => ['lat' => -1.4869, 'lng' => 102.1130, 'db_key' => "MUARA BUNGO"],
        "Sukajaya" => ['lat' => -6.5740, 'lng' => 106.5080, 'db_key' => "SUKAJAYA"],
        "Alok" => ['lat' => -8.6655, 'lng' => 122.2163, 'db_key' => "ALOK"],
        "Mataram" => ['lat' => -8.5833, 'lng' => 116.1167, 'db_key' => "MATARAM"],
        "Sukabumi" => ['lat' => -6.9222, 'lng' => 106.9256, 'db_key' => "SUKABUMI"],
        "Tarakan" => ['lat' => 3.3208, 'lng' => 117.5888, 'db_key' => "TARAKAN"],
        "Duri" => ['lat' => 1.2318, 'lng' => 101.2052, 'db_key' => "DURI"],
        "Kediri" => ['lat' => -7.8200, 'lng' => 112.0170, 'db_key' => "KEDIRI"],
        "Probolinggo" => ['lat' => -7.7466, 'lng' => 113.2168, 'db_key' => "PROBOLINGGO"],
        "Jayapura" => ['lat' => -2.5333, 'lng' => 140.7167, 'db_key' => "JAYAPURA"],
        "Baubau" => ['lat' => -5.4685, 'lng' => 122.6031, 'db_key' => "BAUBAU"],
        "Ambon" => ['lat' => -3.6554, 'lng' => 128.1908, 'db_key' => "AMBON"],
        "Sorong" => ['lat' => -0.8827, 'lng' => 131.2596, 'db_key' => "SORONG"],
        "Papua Barat" => ['lat' => -1.3361, 'lng' => 133.1740, 'db_key' => "PAPUA BARAT"],
        "Manokwari Papua" => ['lat' => -0.8626, 'lng' => 134.0550, 'db_key' => "MANOKWARI PAPUA"],
        "Subang" => ['lat' => -6.5700, 'lng' => 107.7630, 'db_key' => "SUBANG"],
        "Deli Serdang" => ['lat' => 3.4191, 'lng' => 98.8131, 'db_key' => "DELI SERDANG"],
        "Tebing Tinggi" => ['lat' => 3.3316, 'lng' => 99.1621, 'db_key' => "TEBING TINGGI"],
        "Kaban Jahe Karo" => ['lat' => 3.1000, 'lng' => 98.4833, 'db_key' => "KABAN JAHE KARO"],
        "Garut" => ['lat' => -7.2000, 'lng' => 107.9000, 'db_key' => "GARUT"],
        "Kuningan" => ['lat' => -6.9760, 'lng' => 108.4850, 'db_key' => "KUNINGAN"],
        "Bintan" => ['lat' => 1.0795, 'lng' => 104.4360, 'db_key' => "BINTAN"],
        "Kotawaringin" => ['lat' => -2.5300, 'lng' => 111.6300, 'db_key' => "KOTAWARINGIN"],
    ];

    // NEW: Data for international city markers
    protected $internationalCityData = [
        // China - using PEOPLE'S REPUBLIC OF CHINA as the key from $countryMapping
        "SHANDONG" => ['lat' => 36.3000, 'lng' => 118.5000, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA"],
        "GUANGZHOU" => ['lat' => 23.1291, 'lng' => 113.2644, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA"],
        "XIAMEN" => ['lat' => 24.4798, 'lng' => 118.0894, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA"],
        "DONGLI DIST, TIANJIN" => ['lat' => 39.0892, 'lng' => 117.3340, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA"],
        // Philippines
        "MAKATI" => ['lat' => 14.5547, 'lng' => 121.0244, 'country_code_cmmt_key' => "PHILLIPPINES"],
        // Malaysia
        "JOHOR BAHRU" => ['lat' => 1.4927, 'lng' => 103.7414, 'country_code_cmmt_key' => "MALAYSIA"],
        // Myanmar
        "YANGON" => ['lat' => 16.8409, 'lng' => 96.1735, 'country_code_cmmt_key' => "MYANMAR"],
        // UAE
        "DUBAI" => ['lat' => 25.2048, 'lng' => 55.2708, 'country_code_cmmt_key' => "UNI ARAB EMIRATES"],
        // Sri Lanka
        "COLOMBO" => ['lat' => 6.9271, 'lng' => 79.8612, 'country_code_cmmt_key' => "SRILANKA"],
        // North Korea
        "PYONG YANG" => ['lat' => 39.0392, 'lng' => 125.7625, 'country_code_cmmt_key' => "NORTH KOREA"],
        // Taiwan
        "TAIPEI" => ['lat' => 25.0330, 'lng' => 121.5654, 'country_code_cmmt_key' => "TAIWAN"],
        // Hong Kong
        "HONGKONG" => ['lat' => 22.3193, 'lng' => 114.1694, 'country_code_cmmt_key' => "HONGKONG"],
        // South Korea
        "SEOUL" => ['lat' => 37.5665, 'lng' => 126.9780, 'country_code_cmmt_key' => "SOUTH KOREA"],
        // New Zealand (Example, if sales for Wellington are under "EXPORT AUSTRALIA" code_cmmt)
        "WELLINGTON" => ['lat' => -41.2865, 'lng' => 174.7762, 'country_code_cmmt_key' => "EXPORT AUSTRALIA"],
    ];


    public function __construct()
    {
        $this->rawCodeCmmtToSuperRegionKeyMap = collect([
            "REGION 1A", "REGION 1B", "REGION 1C", "REGION 1D",
            "REGION 2A", "REGION 2B", "REGION 2C", "REGION 2D",
            "REGION 3A", "REGION 3B", "REGION 3C",
            "REGION 4A", "REGION 4B",
            "KEY ACCOUNT", "COMMERCIAL"
        ])->mapWithKeys(function ($item) {
            return [strtoupper($item) => strtoupper(str_replace(' ', '', $item))];
        })->all();
    }

    protected function getAvailableDateRanges()
    {
        $minDateDb = SalesTransaction::selectRaw('MIN(STR_TO_DATE(tr_effdate, "%Y-%m-%d")) as min_date')->value('min_date');
        $today = Carbon::now();
        $minDateIso = $minDateDb ? Carbon::parse($minDateDb)->format('Y-m-d') : $today->format('Y-m-d');
        $maxDateIsoForPicker = $today->format('Y-m-d');

        return [
            'min_date_iso' => $minDateIso,
            'max_date_iso' => $maxDateIsoForPicker,
        ];
    }

    public function showMapDashboard()
    {
        $dateRanges = $this->getAvailableDateRanges();
        $filterValues = [
            'brands' => SalesTransaction::distinct()->orderBy('pl_desc')->pluck('pl_desc')->filter()->toArray(),
            'cities' => SalesTransaction::distinct()->orderBy('ad_city')->pluck('ad_city')->filter()->map(function($city){ return strtoupper(trim($city)); })->unique()->sort()->values()->all(),
            'code_cmmts' => SalesTransaction::distinct()->orderBy('code_cmmt')->pluck('code_cmmt')->filter()->map(function($code){ return strtoupper(trim($code)); })->unique()->sort()->values()->all(),
        ];
        // Ensure "ALL" is not part of these lists if they are directly from DB
        // The sorting above is good.

        return view('dashboard.dashboardSales', compact('dateRanges', 'filterValues'));
    }

    public function getSalesData(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'brand' => 'nullable|string',
            'code_cmmt' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->input('startDate'));
        $endDate = Carbon::parse($request->input('endDate'));

        $filterBrand = $request->input('brand');
        $filterCodeCmmt = $request->input('code_cmmt') ? strtoupper(trim($request->input('code_cmmt'))) : null;
        $filterCity = $request->input('city') ? strtoupper(trim($request->input('city'))) : null;


        $period = CarbonPeriod::create($startDate, $endDate);
        $yearsInRange = [];
        foreach ($period as $date) {
            $yearsInRange[] = $date->year;
        }
        $uniqueYears = array_unique($yearsInRange);

        $baseSalesQuery = SalesTransaction::query()
            ->whereBetween('tr_effdate', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($filterBrand && $filterBrand !== 'ALL') {
            $baseSalesQuery->where('pl_desc', $filterBrand);
        }
        if ($filterCodeCmmt && $filterCodeCmmt !== 'ALL') {
            $baseSalesQuery->where(DB::raw('UPPER(TRIM(code_cmmt))'), $filterCodeCmmt);
        }
        if ($filterCity && $filterCity !== 'ALL') {
            $baseSalesQuery->where(DB::raw('UPPER(TRIM(ad_city))'), $filterCity);
        }

        $currentSalesData = (clone $baseSalesQuery)
            ->select('code_cmmt', DB::raw('SUM(tr_ton) as total_ton'))
            ->groupBy('code_cmmt')
            ->get()
            ->pluck('total_ton', 'code_cmmt');

        $lastYearStartDate = $startDate->copy()->subYear()->format('Y-m-d');
        $lastYearEndDate = $endDate->copy()->subYear()->format('Y-m-d');

        $baseLYSalesQuery = SalesTransaction::query()
            ->whereBetween('tr_effdate', [$lastYearStartDate, $lastYearEndDate]);

        if ($filterBrand && $filterBrand !== 'ALL') {
            $baseLYSalesQuery->where('pl_desc', $filterBrand);
        }
        if ($filterCodeCmmt && $filterCodeCmmt !== 'ALL') {
            $baseLYSalesQuery->where(DB::raw('UPPER(TRIM(code_cmmt))'), $filterCodeCmmt);
        }
        if ($filterCity && $filterCity !== 'ALL') {
            $baseLYSalesQuery->where(DB::raw('UPPER(TRIM(ad_city))'), $filterCity);
        }

        $lastYearSalesData = (clone $baseLYSalesQuery)
            ->select('code_cmmt', DB::raw('SUM(tr_ton) as total_ton_ly'))
            ->groupBy('code_cmmt')
            ->get()
            ->pluck('total_ton_ly', 'code_cmmt');


        $rawBudgetsQuery = DB::table('standard_budgets')
            ->whereIn('year', $uniqueYears)
            ->select('name_region', 'amount')
            ->get();
        $budgets = [];
        foreach ($rawBudgetsQuery as $budgetEntry) {
            $regionKey = strtoupper(str_replace(' ', '', trim($budgetEntry->name_region)));
            $amount = (float) $budgetEntry->amount;
            $budgets[$regionKey] = ($budgets[$regionKey] ?? 0) + $amount;
        }

        $worldSales = [];
        $indonesiaSuperRegionSales = [];

        foreach ($this->indonesiaSuperRegionKeys as $srKey) {
            $indonesiaSuperRegionSales[$srKey] = [
                'sales' => 0, 'budget' => $budgets[$srKey] ?? 0, 'lastYearSales' => 0
            ];
        }

        foreach ($this->countryMapping as $rawCountryNameFromDb => $targetCountryName) {
            $budgetKey = strtoupper(str_replace(' ', '', $targetCountryName));
            if ($budgetKey === "UNITEDSTATESOFAMERICA") $budgetKey = "UNITEDSTATES";
            $alternativeBudgetKey = strtoupper(str_replace(' ', '', $rawCountryNameFromDb));
             if ($alternativeBudgetKey === "UNITEDSTATESOFAMERICA") $alternativeBudgetKey = "UNITEDSTATES";

            $worldSales[$targetCountryName] = [
                'sales' => 0,
                'budget' => $budgets[$budgetKey] ?? ($budgets[$alternativeBudgetKey] ?? 0),
                'lastYearSales' => 0
            ];
        }
        $indonesiaOverallBudgetKey = strtoupper("INDONESIA");
        $worldSales["Indonesia"] = [
            'sales' => 0, 'budget' => $budgets[$indonesiaOverallBudgetKey] ?? 0, 'lastYearSales' => 0
        ];

        foreach ($currentSalesData as $codeCmmtDb => $totalTon) {
            $codeCmmtUpper = strtoupper(trim($codeCmmtDb));
            $totalTon = (float) $totalTon;
            $superRegionKey = $this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUpper] ?? null;

            if ($superRegionKey && in_array($superRegionKey, $this->indonesiaSuperRegionKeys)) {
                if (isset($indonesiaSuperRegionSales[$superRegionKey])) {
                    $indonesiaSuperRegionSales[$superRegionKey]['sales'] += $totalTon;
                }
            } elseif (isset($this->countryMapping[$codeCmmtUpper])) {
                $shapeName = $this->countryMapping[$codeCmmtUpper];
                if (isset($worldSales[$shapeName])) {
                    $worldSales[$shapeName]['sales'] += $totalTon;
                } else {
                     $budgetKey = strtoupper(str_replace(' ', '', $shapeName));
                     if ($budgetKey === "UNITEDSTATESOFAMERICA") $budgetKey = "UNITEDSTATES";
                     $worldSales[$shapeName] = ['sales' => $totalTon, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => 0];
                }
            } elseif ($codeCmmtUpper !== 'INDONESIA' && !in_array($codeCmmtUpper, array_keys($this->rawCodeCmmtToSuperRegionKeyMap))) {
                $displayName = $codeCmmtDb; // Use original case from DB for display name consistency
                if (isset($worldSales[$displayName])) {
                     $worldSales[$displayName]['sales'] += $totalTon;
                 } else {
                     $budgetKey = strtoupper(str_replace(' ', '', $codeCmmtUpper));
                     $worldSales[$displayName] = ['sales' => $totalTon, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => 0];
                 }
            }
        }

        foreach ($lastYearSalesData as $codeCmmtDb => $totalTonLy) {
            $codeCmmtUpper = strtoupper(trim($codeCmmtDb));
            $totalTonLy = (float) $totalTonLy;
            $superRegionKey = $this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUpper] ?? null;

            if ($superRegionKey && in_array($superRegionKey, $this->indonesiaSuperRegionKeys)) {
                if (isset($indonesiaSuperRegionSales[$superRegionKey])) {
                    $indonesiaSuperRegionSales[$superRegionKey]['lastYearSales'] += $totalTonLy;
                }
            } elseif (isset($this->countryMapping[$codeCmmtUpper])) {
                $shapeName = $this->countryMapping[$codeCmmtUpper];
                if (isset($worldSales[$shapeName])) {
                    $worldSales[$shapeName]['lastYearSales'] += $totalTonLy;
                } else {
                     $budgetKey = strtoupper(str_replace(' ', '', $shapeName));
                     if ($budgetKey === "UNITEDSTATESOFAMERICA") $budgetKey = "UNITEDSTATES";
                     $worldSales[$shapeName] = ['sales' => 0, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => $totalTonLy];
                }
            } elseif ($codeCmmtUpper !== 'INDONESIA' && !in_array($codeCmmtUpper, array_keys($this->rawCodeCmmtToSuperRegionKeyMap))) {
                $displayName = $codeCmmtDb;
                if (isset($worldSales[$displayName])) {
                    $worldSales[$displayName]['lastYearSales'] += $totalTonLy;
                 } else {
                    $budgetKey = strtoupper(str_replace(' ', '', $codeCmmtUpper));
                    $worldSales[$displayName] = ['sales' => 0, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => $totalTonLy];
                 }
            }
        }

        $totalIndonesiaSales = 0;
        $totalIndonesiaLastYearSales = 0;
        foreach ($this->indonesiaSuperRegionKeys as $srKey) {
            if (isset($indonesiaSuperRegionSales[$srKey])) {
                $totalIndonesiaSales += $indonesiaSuperRegionSales[$srKey]['sales'];
                $totalIndonesiaLastYearSales += $indonesiaSuperRegionSales[$srKey]['lastYearSales'];
            }
        }
        if (isset($worldSales["Indonesia"])) {
            $worldSales["Indonesia"]['sales'] = $totalIndonesiaSales;
            $worldSales["Indonesia"]['lastYearSales'] = $totalIndonesiaLastYearSales;
        }


        // Indonesian City Markers
        $cityMarkers = [];
        $indonesiaCityDbKeys = array_map('strtoupper', array_column($this->indonesiaCityData, 'db_key'));
        $indonesiaCitySalesQuery = SalesTransaction::select(DB::raw("UPPER(TRIM(ad_city)) as city_name_from_db"), DB::raw('SUM(tr_ton) as total_ton'))
            ->whereBetween('tr_effdate', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotNull('ad_city')
            ->whereIn(DB::raw("UPPER(TRIM(ad_city))"), $indonesiaCityDbKeys);

        if ($filterBrand && $filterBrand !== 'ALL') $indonesiaCitySalesQuery->where('pl_desc', $filterBrand);
        if ($filterCodeCmmt && $filterCodeCmmt !== 'ALL') {
             // Only apply code_cmmt if it's an Indonesian region or "INDONESIA"
            $isIndonesianCode = $filterCodeCmmt === 'INDONESIA' || isset($this->rawCodeCmmtToSuperRegionKeyMap[$filterCodeCmmt]);
            if ($isIndonesianCode) {
                $indonesiaCitySalesQuery->where(DB::raw('UPPER(TRIM(code_cmmt))'), $filterCodeCmmt);
            } else { // If foreign country code_cmmt, no Indonesian cities should match
                $indonesiaCitySalesQuery->whereRaw('1=0');
            }
        }
        if ($filterCity && $filterCity !== 'ALL') {
             // If city filter is active, check if it's an Indonesian city
            if (in_array($filterCity, $indonesiaCityDbKeys)) {
                 $indonesiaCitySalesQuery->where(DB::raw("UPPER(TRIM(ad_city))"), $filterCity);
            } else { // If foreign city, no Indonesian cities should match
                $indonesiaCitySalesQuery->whereRaw('1=0');
            }
        }

        $indonesiaCitySalesData = $indonesiaCitySalesQuery->groupBy(DB::raw("UPPER(TRIM(ad_city))"))->get()->pluck('total_ton', 'city_name_from_db');

        foreach ($this->indonesiaCityData as $displayName => $cityInfo) {
            $dbKeyNormalized = strtoupper(trim($cityInfo['db_key']));
            $sales = (float) ($indonesiaCitySalesData[$dbKeyNormalized] ?? 0);
            if ($sales >= 0) { // Show even with 0 sales if data is expected
                $cityMarkers[] = ['name' => $displayName, 'lat' => $cityInfo['lat'], 'lng' => $cityInfo['lng'], 'sales' => $sales];
            }
        }

        // International City Markers
        $internationalCityMarkers = [];
        if (!empty($this->internationalCityData)) {
            $internationalCityDbKeys = array_map('strtoupper', array_keys($this->internationalCityData));

            $internationalCitySalesQuery = SalesTransaction::select(
                    DB::raw("UPPER(TRIM(ad_city)) as city_name_from_db"),
                    DB::raw("UPPER(TRIM(code_cmmt)) as country_code_cmmt"), // Get the country identifier
                    DB::raw('SUM(tr_ton) as total_ton')
                )
                ->whereBetween('tr_effdate', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('ad_city')
                ->whereIn(DB::raw("UPPER(TRIM(ad_city))"), $internationalCityDbKeys);

            if ($filterBrand && $filterBrand !== 'ALL') {
                $internationalCitySalesQuery->where('pl_desc', $filterBrand);
            }
            if ($filterCodeCmmt && $filterCodeCmmt !== 'ALL') {
                // Apply code_cmmt filter only if it's NOT an Indonesian region or "INDONESIA"
                $isIndonesianCode = $filterCodeCmmt === 'INDONESIA' || isset($this->rawCodeCmmtToSuperRegionKeyMap[$filterCodeCmmt]);
                if (!$isIndonesianCode) {
                    $internationalCitySalesQuery->where(DB::raw('UPPER(TRIM(code_cmmt))'), $filterCodeCmmt);
                } else { // If Indonesian code_cmmt, no international cities should match
                    $internationalCitySalesQuery->whereRaw('1=0');
                }
            }
             if ($filterCity && $filterCity !== 'ALL') {
                // If city filter is active, check if it's an international city we know
                if (in_array($filterCity, $internationalCityDbKeys)) {
                    $internationalCitySalesQuery->where(DB::raw("UPPER(TRIM(ad_city))"), $filterCity);
                } else { // If Indonesian city, no international cities should match
                    $internationalCitySalesQuery->whereRaw('1=0');
                }
            }

            $internationalCitySalesData = $internationalCitySalesQuery
                ->groupBy(DB::raw("UPPER(TRIM(ad_city))"), DB::raw("UPPER(TRIM(code_cmmt))"))
                ->get();

            foreach ($internationalCitySalesData as $sale) {
                $cityDbKey = $sale->city_name_from_db; // Already uppercase from query
                $countryCodeCmmt = $sale->country_code_cmmt; // Already uppercase

                if (isset($this->internationalCityData[$cityDbKey])) {
                    $cityInfo = $this->internationalCityData[$cityDbKey];
                    // Critical: Ensure the city's predefined country_code_cmmt_key matches the one from the transaction
                    if (strtoupper(trim($cityInfo['country_code_cmmt_key'])) === $countryCodeCmmt) {
                        $sales = (float) $sale->total_ton;
                        if ($sales > 0) { // Only show markers with sales for international cities
                            $internationalCityMarkers[] = [
                                'name' => ucwords(strtolower(str_replace(['_', '-'], ' ', $cityDbKey))),
                                'lat' => $cityInfo['lat'],
                                'lng' => $cityInfo['lng'],
                                'sales' => $sales,
                                'country' => $this->countryMapping[$countryCodeCmmt] ?? $countryCodeCmmt
                            ];
                        }
                    }
                }
            }
        }

        return response()->json([
            'worldSales' => $worldSales,
            'indonesiaSuperRegionSales' => $indonesiaSuperRegionSales,
            'cityMarkers' => $cityMarkers,
            'internationalCityMarkers' => $internationalCityMarkers,
        ]);
    }
}