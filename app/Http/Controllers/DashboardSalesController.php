<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardSalesController extends Controller
{
    // ... (definisi property $countryMapping, $indonesiaSuperRegionKeys, dll. tetap sama) ...
    protected $countryMapping = [
        "TAIWAN" => "Taiwan", "PHILLIPPINES" => "Philippines", "MALAYSIA" => "Malaysia",
        "MYANMAR" => "Myanmar", "EXPORT AUSTRALIA" => "Australia", "SRILANKA" => "Sri Lanka",
        "UNI ARAB EMIRATES" => "United Arab Emirates", "HONGKONG" => "Hong Kong",
        "PEOPLE'S REPUBLIC OF CHINA" => "China", "BRAZIL" => "Brazil",
        "UNITED STATES OF AMERICA" => "United States", "GERMANY" => "Germany",
        "INDIA" => "India", "CANADA" => "Canada", "SOUTH AFRICA" => "South Africa",
        "NEPAL" => "Nepal", "FIJI" => "Fiji", "RUSSIAN FEDERATION" => "Russia",
        "NORTH KOREA" => "North Korea", "SOUTH KOREA" => "South Korea"
    ];

    protected $indonesiaSuperRegionKeys = [
        "REGION1A", "REGION1B", "REGION1C", "REGION1D", "REGION2A", "REGION2B", "REGION2C", "REGION2D",
        "REGION3A", "REGION3B", "REGION3C", "REGION4A", "REGION4B", "KEYACCOUNT", "COMMERCIAL"
    ];

    protected $rawCodeCmmtToSuperRegionKeyMap;

    protected $indonesiaCityData = [
        "Tangerang Selatan" => ['lat' => -6.2887, 'lng' => 106.7194, 'db_key' => "TANGERANG SELATAN"], "Bali" => ['lat' => -8.3405, 'lng' => 115.0919, 'db_key' => "BALI"],
        "Denpasar" => ['lat' => -8.6705, 'lng' => 115.2126, 'db_key' => "DENPASAR"], "Bogor" => ['lat' => -6.5950, 'lng' => 106.8060, 'db_key' => "BOGOR"],
        "Semarang" => ['lat' => -6.9667, 'lng' => 110.4381, 'db_key' => "SEMARANG"], "Jakarta" => ['lat' => -6.2088, 'lng' => 106.8456, 'db_key' => "JAKARTA"],
        "Jakarta Pusat" => ['lat' => -6.1751, 'lng' => 106.8272, 'db_key' => "JAKARTA PUSAT"], "Jakarta Utara" => ['lat' => -6.1384, 'lng' => 106.8639, 'db_key' => "JAKARTA UTARA"],
        "Jakarta Barat" => ['lat' => -6.1676, 'lng' => 106.7663, 'db_key' => "JAKARTA BARAT"], "Jakarta Selatan" => ['lat' => -6.2615, 'lng' => 106.8106, 'db_key' => "JAKARTA SELATAN"],
        "Adm. Jakarta Selatan" => ['lat' => -6.2615, 'lng' => 106.8106, 'db_key' => "ADM. JAKARTA SELATAN"], "Jakarta Timur" => ['lat' => -6.2259, 'lng' => 106.9004, 'db_key' => "JAKARTA TIMUR"],
        "Bekasi" => ['lat' => -6.2383, 'lng' => 106.9756, 'db_key' => "BEKASI"], "Bekas" => ['lat' => -6.2383, 'lng' => 106.9756, 'db_key' => "BEKAS"],
        "Serang" => ['lat' => -6.1181, 'lng' => 106.1558, 'db_key' => "SERANG"], "Surabaya" => ['lat' => -7.2575, 'lng' => 112.7521, 'db_key' => "SURABAYA"],
        "Bandung" => ['lat' => -6.9175, 'lng' => 107.6191, 'db_key' => "BANDUNG"], "Medan" => ['lat' => 3.5952, 'lng' => 98.6722, 'db_key' => "MEDAN"],
        "Palembang" => ['lat' => -2.9761, 'lng' => 104.7754, 'db_key' => "PALEMBANG"], "Makassar" => ['lat' => -5.1477, 'lng' => 119.4327, 'db_key' => "MAKASSAR"],
        "Yogyakarta" => ['lat' => -7.7956, 'lng' => 110.3695, 'db_key' => "YOGYAKARTA"], "D.I.Yogyakarta" => ['lat' => -7.7956, 'lng' => 110.3695, 'db_key' => "D.I.YOGYAKARTA"],
        "DI Yogyakarta" => ['lat' => -7.7956, 'lng' => 110.3695, 'db_key' => "DI YOGYAKARTA"], "Sleman" => ['lat' => -7.7186, 'lng' => 110.3803, 'db_key' => "SLEMAN"],
        "Tangerang" => ['lat' => -6.1783, 'lng' => 106.6319, 'db_key' => "TANGERANG"], "Depok" => ['lat' => -6.4025, 'lng' => 106.7942, 'db_key' => "DEPOK"],
        "Cirebon" => ['lat' => -6.7066, 'lng' => 108.5570, 'db_key' => "CIREBON"], "Malang" => ['lat' => -7.9666, 'lng' => 112.6326, 'db_key' => "MALANG"],
        "Jember" => ['lat' => -8.1690, 'lng' => 113.7000, 'db_key' => "JEMBER"], "Kudus" => ['lat' => -6.8063, 'lng' => 110.8421, 'db_key' => "KUDUS"],
        "Pekanbaru" => ['lat' => 0.5071, 'lng' => 101.4478, 'db_key' => "PEKAN BARU"], "Balikpapan" => ['lat' => -1.2379, 'lng' => 116.8529, 'db_key' => "BALIKPAPAN"],
        "Banjarmasin" => ['lat' => -3.3167, 'lng' => 114.5900, 'db_key' => "BANJARMASIN"], "Pontianak (Kota)" => ['lat' => -0.0277, 'lng' => 109.3425, 'db_key' => "PONTIANAK"],
        "Kalimantan Barat (Prov)" => ['lat' => -0.0277, 'lng' => 109.3425, 'db_key' => "KALIMANTAN BARAT"], "Padang" => ['lat' => -0.9471, 'lng' => 100.3616, 'db_key' => "PADANG"],
        "DKI Jakarta" => ['lat' => -6.2088, 'lng' => 106.8456, 'db_key' => "DKI JAKARTA"], "DKI Jakarta Raya" => ['lat' => -6.2088, 'lng' => 106.8456, 'db_key' => "DKI JAKARTA RAYA"],
        "Pasuruan" => ['lat' => -7.6455, 'lng' => 112.9075, 'db_key' => "PASURUAN"], "Pangkalpinang" => ['lat' => -2.1303, 'lng' => 106.1098, 'db_key' => "PANGKALPINANG"],
        "Bojonegoro" => ['lat' => -7.1566, 'lng' => 111.8886, 'db_key' => "BOJONEGORO"], "Sidoarjo" => ['lat' => -7.4478, 'lng' => 112.7183, 'db_key' => "SIDOARJO"],
        "Tulung Agung" => ['lat' => -8.0652, 'lng' => 111.9010, 'db_key' => "TULUNG AGUNG"], "Gorontalo" => ['lat' => 0.5400, 'lng' => 123.0640, 'db_key' => "GORONTALO"],
        "Palu" => ['lat' => -0.8977, 'lng' => 119.8657, 'db_key' => "PALU"], "Batam" => ['lat' => 1.0456, 'lng' => 104.0305, 'db_key' => "BATAM"],
        "Jambi" => ['lat' => -1.6102, 'lng' => 103.6131, 'db_key' => "JAMBI"], "Cikarang" => ['lat' => -6.3160, 'lng' => 107.1463, 'db_key' => "CIKARANG"],
        "Banten (Prov)" => ['lat' => -6.4240, 'lng' => 106.1231, 'db_key' => "BANTEN"], "Samarinda" => ['lat' => -0.5021, 'lng' => 117.1537, 'db_key' => "SAMARINDA"],
        "Aceh (Prov)" => ['lat' => 4.6951, 'lng' => 96.7494, 'db_key' => "ACEH"], "Banda Aceh" => ['lat' => 5.5483, 'lng' => 95.3238, 'db_key' => "BANDA ACEH"],
        "Kendari" => ['lat' => -3.9955, 'lng' => 122.5148, 'db_key' => "KENDARI"], "Pamekasan" => ['lat' => -7.1600, 'lng' => 113.4783, 'db_key' => "PAMEKASAN"],
        "Karawang" => ['lat' => -6.3025, 'lng' => 107.3060, 'db_key' => "KARAWANG"], "Penjaringan" => ['lat' => -6.1180, 'lng' => 106.7924, 'db_key' => "PENJARINGAN"],
        "Ternate" => ['lat' => 0.7896, 'lng' => 127.3748, 'db_key' => "TERNATE"], "Kupang" => ['lat' => -10.1778, 'lng' => 123.5976, 'db_key' => "KUPANG"],
        "Gresik" => ['lat' => -7.1646, 'lng' => 112.6508, 'db_key' => "GRESIK"], "Manado" => ['lat' => 1.4748, 'lng' => 124.8421, 'db_key' => "MANADO"],
        "Muara Bungo" => ['lat' => -1.4869, 'lng' => 102.1130, 'db_key' => "MUARA BUNGO"], "Sukajaya" => ['lat' => -6.5740, 'lng' => 106.5080, 'db_key' => "SUKAJAYA"],
        "Alok" => ['lat' => -8.6655, 'lng' => 122.2163, 'db_key' => "ALOK"], "Mataram" => ['lat' => -8.5833, 'lng' => 116.1167, 'db_key' => "MATARAM"],
        "Sukabumi" => ['lat' => -6.9222, 'lng' => 106.9256, 'db_key' => "SUKABUMI"], "Tarakan" => ['lat' => 3.3208, 'lng' => 117.5888, 'db_key' => "TARAKAN"],
        "Duri" => ['lat' => 1.2318, 'lng' => 101.2052, 'db_key' => "DURI"], "Kediri" => ['lat' => -7.8200, 'lng' => 112.0170, 'db_key' => "KEDIRI"],
        "Probolinggo" => ['lat' => -7.7466, 'lng' => 113.2168, 'db_key' => "PROBOLINGGO"], "Jayapura" => ['lat' => -2.5333, 'lng' => 140.7167, 'db_key' => "JAYAPURA"],
        "Baubau" => ['lat' => -5.4685, 'lng' => 122.6031, 'db_key' => "BAUBAU"], "Ambon" => ['lat' => -3.6554, 'lng' => 128.1908, 'db_key' => "AMBON"],
        "Sorong" => ['lat' => -0.8827, 'lng' => 131.2596, 'db_key' => "SORONG"], "Papua Barat (Prov)" => ['lat' => -1.3361, 'lng' => 133.1740, 'db_key' => "PAPUA BARAT"],
        "Manokwari Papua" => ['lat' => -0.8626, 'lng' => 134.0550, 'db_key' => "MANOKWARI PAPUA"], "Subang" => ['lat' => -6.5700, 'lng' => 107.7630, 'db_key' => "SUBANG"],
        "Deli Serdang" => ['lat' => 3.4191, 'lng' => 98.8131, 'db_key' => "DELI SERDANG"], "Tebing Tinggi" => ['lat' => 3.3316, 'lng' => 99.1621, 'db_key' => "TEBING TINGGI"],
        "Kaban Jahe Karo" => ['lat' => 3.1000, 'lng' => 98.4833, 'db_key' => "KABAN JAHE KARO"], "Garut" => ['lat' => -7.2000, 'lng' => 107.9000, 'db_key' => "GARUT"],
        "Kuningan" => ['lat' => -6.9760, 'lng' => 108.4850, 'db_key' => "KUNINGAN"], "Bintan" => ['lat' => 1.0795, 'lng' => 104.4360, 'db_key' => "BINTAN"],
        "Kotawaringin" => ['lat' => -2.5300, 'lng' => 111.6300, 'db_key' => "KOTAWARINGIN"],
    ];

    protected $internationalCityData = [
        "SHANDONG" => ['lat' => 36.3000, 'lng' => 118.5000, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA", 'display_name' => "Shandong"],
        "GUANGZHOU" => ['lat' => 23.1291, 'lng' => 113.2644, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA", 'display_name' => "Guangzhou"],
        "XIAMEN" => ['lat' => 24.4798, 'lng' => 118.0894, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA", 'display_name' => "Xiamen"],
        "DONGLI DIST, TIANJIN" => ['lat' => 39.0892, 'lng' => 117.3340, 'country_code_cmmt_key' => "PEOPLE'S REPUBLIC OF CHINA", 'display_name' => "Dongli Dist, Tianjin"],
        "MAKATI" => ['lat' => 14.5547, 'lng' => 121.0244, 'country_code_cmmt_key' => "PHILLIPPINES", 'display_name' => "Makati"],
        "JOHOR BAHRU" => ['lat' => 1.4927, 'lng' => 103.7414, 'country_code_cmmt_key' => "MALAYSIA", 'display_name' => "Johor Bahru"],
        "YANGON" => ['lat' => 16.8409, 'lng' => 96.1735, 'country_code_cmmt_key' => "MYANMAR", 'display_name' => "Yangon"],
        "DUBAI" => ['lat' => 25.2048, 'lng' => 55.2708, 'country_code_cmmt_key' => "UNI ARAB EMIRATES", 'display_name' => "Dubai"],
        "COLOMBO" => ['lat' => 6.9271, 'lng' => 79.8612, 'country_code_cmmt_key' => "SRILANKA", 'display_name' => "Colombo"],
        "PYONG YANG" => ['lat' => 39.0392, 'lng' => 125.7625, 'country_code_cmmt_key' => "NORTH KOREA", 'display_name' => "Pyongyang"],
        "TAIPEI" => ['lat' => 25.0330, 'lng' => 121.5654, 'country_code_cmmt_key' => "TAIWAN", 'display_name' => "Taipei"],
        "HONGKONG CITY" => ['lat' => 22.3193, 'lng' => 114.1694, 'country_code_cmmt_key' => "HONGKONG", 'display_name' => "Hong Kong"],
        "SEOUL" => ['lat' => 37.5665, 'lng' => 126.9780, 'country_code_cmmt_key' => "SOUTH KOREA", 'display_name' => "Seoul"],
        "WELLINGTON" => ['lat' => -41.2865, 'lng' => 174.7762, 'country_code_cmmt_key' => "EXPORT AUSTRALIA", 'display_name' => "Wellington"],
    ];

    protected function getCitiesForSuperRegion(string $superRegionKey): array
    {
        $superRegionDefinitions = [
            "REGION1A" => ["PONTIANAK", "KALIMANTAN BARAT", "SERANG", "TANGERANG", "LAMPUNG"],
            "REGION1B" => ["BANDUNG", "TASIKMALAYA", "CIREBON"],
            "REGION1C" => ["JAKARTA TIMUR", "JAKARTA PUSAT", "JAKARTA UTARA", "JAKARTA BARAT", "JAKARTA SELATAN", "JAKARTA", "DKI JAKARTA", "DKI JAKARTA RAYA", "ADM. JAKARTA SELATAN", "DEPOK"],
            "REGION1D" => ["KARAWANG", "BEKASI", "BOGOR", "BEKAS"],
            "REGION2A" => ["SEMARANG", "KUDUS", "TEGAL", "PEKALONGAN", "BOJONEGORO"],
            "REGION2B" => ["MALANG", "SURABAYA", "JEMBER", "MADURA", "BANYUWANGI", "PASURUAN", "SIDOARJO", "GRESIK", "PROBOLINGGO", "KEDIRI"],
            "REGION2C" => ["BALI", "FLORES", "KUPANG", "LOMBOK", "MATARAM", "ALOK", "DENPASAR"],
            "REGION2D" => ["SOLO", "PURWOKERTO", "MAGELANG", "YOGYAKARTA", "D.I.YOGYAKARTA", "DI YOGYAKARTA", "SLEMAN", "TULUNGAGUNG", "TULUNG AGUNG", "MADIUN"],
            "REGION3A" => ["PALEMBANG", "BANGKA BELITUNG", "PANGKALPINANG", "JAMBI", "MUARA BUNGO"],
            "REGION3B" => ["PADANG", "PEKANBARU", "PEKAN BARU", "BATAM", "DURI", "BINTAN"],
            "REGION3C" => ["MEDAN", "ACEH", "BANDA ACEH", "NIAS", "DELI SERDANG", "TEBING TINGGI", "KABAN JAHE KARO"],
            "REGION4A" => ["SORONG", "GORONTALO", "TERNATE", "JAYAPURA", "MANADO", "MANOKWARI", "MANOKWARI PAPUA", "TIMIKA", "MERAUKE", "AMBON", "PAPUA BARAT"],
            "REGION4B" => ["TARAKAN", "PALU", "SAMARINDA", "BAUBAU", "BANJARMASIN", "PALANGKARAYA", "SAMPIT", "PANGKALANBUN", "MAKASSAR", "KENDARI", "BALIKPAPAN", "KOTAWARINGIN"],
        ];
        return $superRegionDefinitions[$superRegionKey] ?? [];
    }

    public function __construct()
    {
        $this->rawCodeCmmtToSuperRegionKeyMap = collect([
            "REGION 1A", "REGION 1B", "REGION 1C", "REGION 1D", "REGION 2A", "REGION 2B", "REGION 2C", "REGION 2D",
            "REGION 3A", "REGION 3B", "REGION 3C", "REGION 4A", "REGION 4B", "KEY ACCOUNT", "COMMERCIAL"
        ])->mapWithKeys(function ($item) {
            return [strtoupper(trim($item)) => strtoupper(str_replace(' ', '', trim($item)))];
        })->all();
    }

    protected function getAvailableDateRanges()
    {
        $minDateDb = SalesTransaction::selectRaw('MIN(STR_TO_DATE(tr_effdate, "%Y-%m-%d")) as min_date')->value('min_date');
        $today = Carbon::now();
        $minDateIso = $minDateDb ? Carbon::parse($minDateDb)->format('Y-m-d') : $today->format('Y-m-d');
        $maxDateIsoForPicker = $today->format('Y-m-d');
        
        $defaultStartDateIso = $today->format('Y-m-d');
        $defaultEndDateIso = $today->format('Y-m-d');

        return [
            'min_date_iso' => $minDateIso, 
            'max_date_iso' => $maxDateIsoForPicker,
            'default_start_date_iso' => $defaultStartDateIso,
            'default_end_date_iso' => $defaultEndDateIso,
        ];
    }

    public function showMapDashboard()
    {
        $dateRanges = $this->getAvailableDateRanges();
        $dbBrands = SalesTransaction::distinct()->orderBy('pl_desc')->pluck('pl_desc')->filter()->sort()->values()->all();
        
        $dbCities = SalesTransaction::distinct()->pluck('ad_city')
            ->filter()
            ->map(fn($city) => strtoupper(trim($city)))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $dbCodeCmmts = SalesTransaction::distinct()->pluck('code_cmmt')
            ->filter()
            ->map(fn($code) => strtoupper(trim($code)))
            ->unique()
            ->sort()
            ->values()
            ->all();
            
        $filterValues = ['brands' => $dbBrands, 'cities' => $dbCities, 'code_cmmts' => $dbCodeCmmts];
        return view('dashboard.dashboardSales', compact('dateRanges', 'filterValues'));
    }

    public function getSalesData(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date_format:Y-m-d', 'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'brand' => 'nullable|string', 'code_cmmt' => 'nullable|string', 'city' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->input('startDate'));
        $endDate = Carbon::parse($request->input('endDate'));
        $filterBrand = $request->input('brand');
        $filterCodeCmmt = $request->input('code_cmmt') ? strtoupper(trim($request->input('code_cmmt'))) : null;
        $filterCityDbKey = $request->input('city') ? strtoupper(trim($request->input('city'))) : null;

        // --- Base Query Builder ---
        $baseQuery = fn($start, $end) => SalesTransaction::query()
            ->whereBetween('tr_effdate', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->when($filterBrand && $filterBrand !== 'ALL', fn($q) => $q->where('pl_desc', $filterBrand))
            ->when($filterCodeCmmt && $filterCodeCmmt !== 'ALL', fn($q) => $q->where(DB::raw('UPPER(TRIM(code_cmmt))'), $filterCodeCmmt))
            ->when($filterCityDbKey && $filterCityDbKey !== 'ALL', fn($q) => $q->where(DB::raw('UPPER(TRIM(ad_city))'), $filterCityDbKey));

        // --- Get Aggregated Sales Data (Current Period) ---
        $aggregatedSales = $baseQuery($startDate, $endDate)
            ->select(
                DB::raw('UPPER(TRIM(code_cmmt)) as code_cmmt_upper'),
                DB::raw('UPPER(TRIM(ad_city)) as ad_city_upper'), // Untuk city markers
                DB::raw('SUM(tr_ton) as total_sales')
            )
            ->groupBy('code_cmmt_upper', 'ad_city_upper') // Group by city juga untuk city markers
            ->get();

        // --- Get Aggregated Sales Data (Last Year Period) ---
        $lastYearStartDate = $startDate->copy()->subYear();
        $lastYearEndDate = $endDate->copy()->subYear();
        $aggregatedSalesLy = $baseQuery($lastYearStartDate, $lastYearEndDate)
            ->select(
                DB::raw('UPPER(TRIM(code_cmmt)) as code_cmmt_upper'),
                // ad_city tidak perlu diagregat di sini jika hanya untuk total LY per region/country
                DB::raw('SUM(tr_ton) as total_sales_ly')
            )
            ->groupBy('code_cmmt_upper')
            ->get()
            ->keyBy('code_cmmt_upper'); // Key by code_cmmt_upper for easier lookup

        // --- Get Budgets ---
        $period = CarbonPeriod::create($startDate, $endDate);
        $uniqueYears = array_unique(array_map(fn($date) => $date->year, iterator_to_array($period)));
        
        $rawBudgetsQuery = DB::table('standard_budgets')->whereIn('year', $uniqueYears)->select('name_region', 'amount')->get();
        $budgets = [];
        foreach ($rawBudgetsQuery as $be) {
            $rk = strtoupper(str_replace(' ', '', trim($be->name_region)));
            $budgets[$rk] = ($budgets[$rk] ?? 0) + (float)$be->amount;
        }

        // --- Initialize Sales Arrays ---
        $worldSales = [];
        $indonesiaSuperRegionSales = [];
        foreach ($this->indonesiaSuperRegionKeys as $srk) {
            $indonesiaSuperRegionSales[$srk] = ['sales' => 0, 'budget' => $budgets[$srk] ?? 0, 'lastYearSales' => 0];
        }
        // Inisialisasi worldSales dari countryMapping dan juga code_cmmt unik dari data sales (jika ada negara baru)
        $allCountryDbKeys = array_unique(array_merge(
            array_keys($this->countryMapping),
            $aggregatedSales->pluck('code_cmmt_upper')->filter(fn($cc) => 
                !in_array($cc, $this->indonesiaSuperRegionKeys) && 
                !isset($this->rawCodeCmmtToSuperRegionKeyMap[$cc]) && 
                $cc !== 'INDONESIA'
            )->all(),
            $aggregatedSalesLy->pluck('code_cmmt_upper')->filter(fn($cc) => 
                !in_array($cc, $this->indonesiaSuperRegionKeys) && 
                !isset($this->rawCodeCmmtToSuperRegionKeyMap[$cc]) && 
                $cc !== 'INDONESIA'
            )->all()
        ));

        foreach ($allCountryDbKeys as $rawDbK) {
            $tName = $this->countryMapping[$rawDbK] ?? $rawDbK; // Display name
            $budgetKey = strtoupper(str_replace(' ', '', $rawDbK));
            if($budgetKey === "UNITEDSTATESOFAMERICA") $budgetKey = "UNITEDSTATES";

            $worldSales[$tName] = [
                'sales' => 0,
                'budget' => $budgets[$budgetKey] ?? 0,
                'lastYearSales' => 0
            ];
        }
        if (!isset($worldSales["Indonesia"])) { // Pastikan Indonesia ada
             $worldSales["Indonesia"] = ['sales' => 0, 'budget' => $budgets[strtoupper("INDONESIA")] ?? 0, 'lastYearSales' => 0];
        }


        // --- Process Aggregated Sales (Current Period) ---
        // Buat lookup untuk sales per kota dari aggregatedSales
        $citySalesAggregation = $aggregatedSales->groupBy('ad_city_upper')
                                               ->map(fn($group) => $group->sum('total_sales'));

        foreach ($aggregatedSales as $aggSale) {
            $codeCmmtUp = $aggSale->code_cmmt_upper;
            $totalTon = (float)$aggSale->total_sales;

            $srk = $this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUp] ?? (in_array($codeCmmtUp, $this->indonesiaSuperRegionKeys) ? $codeCmmtUp : null);

            if ($srk && isset($indonesiaSuperRegionSales[$srk])) {
                $indonesiaSuperRegionSales[$srk]['sales'] += $totalTon;
            } elseif (isset($this->countryMapping[$codeCmmtUp])) {
                $sName = $this->countryMapping[$codeCmmtUp];
                if (isset($worldSales[$sName])) $worldSales[$sName]['sales'] += $totalTon;
            } elseif ($codeCmmtUp !== 'INDONESIA' && !isset($this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUp]) && !in_array($codeCmmtUp, $this->indonesiaSuperRegionKeys)) {
                // Negara tidak di mapping
                $displayName = $codeCmmtUp;
                if(isset($worldSales[$displayName])) $worldSales[$displayName]['sales'] += $totalTon;
            }
        }
        // Hitung total sales Indonesia dari super regions
        $totalIdnSales = 0; foreach($indonesiaSuperRegionSales as $data) $totalIdnSales += $data['sales'];
        if(isset($worldSales["Indonesia"])) $worldSales["Indonesia"]['sales'] = $totalIdnSales;


        // --- Process Aggregated Sales (Last Year Period) ---
        foreach ($aggregatedSalesLy as $codeCmmtUp => $aggSaleLy) {
            $totalTonLy = (float)$aggSaleLy->total_sales_ly;
            $srk = $this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUp] ?? (in_array($codeCmmtUp, $this->indonesiaSuperRegionKeys) ? $codeCmmtUp : null);

            if ($srk && isset($indonesiaSuperRegionSales[$srk])) {
                $indonesiaSuperRegionSales[$srk]['lastYearSales'] += $totalTonLy;
            } elseif (isset($this->countryMapping[$codeCmmtUp])) {
                $sName = $this->countryMapping[$codeCmmtUp];
                if (isset($worldSales[$sName])) $worldSales[$sName]['lastYearSales'] += $totalTonLy;
            } elseif ($codeCmmtUp !== 'INDONESIA' && !isset($this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUp]) && !in_array($codeCmmtUp, $this->indonesiaSuperRegionKeys)) {
                 $displayName = $codeCmmtUp;
                if(isset($worldSales[$displayName])) $worldSales[$displayName]['lastYearSales'] += $totalTonLy;
            }
        }
        $totalIdnLySales = 0; foreach($indonesiaSuperRegionSales as $data) $totalIdnLySales += $data['lastYearSales'];
        if(isset($worldSales["Indonesia"])) $worldSales["Indonesia"]['lastYearSales'] = $totalIdnLySales;


        // --- Prepare City Markers ---
        $cityMarkers = [];
        $internationalCityMarkers = [];

        $isIndonesianSuperRegionFilter = false;
        $normalizedSrKeyForFilter = null;
        if ($filterCodeCmmt && $filterCodeCmmt !== 'ALL') {
            $normalizedSrKeyForFilter = $this->rawCodeCmmtToSuperRegionKeyMap[$filterCodeCmmt] ??
                                       (in_array($filterCodeCmmt, $this->indonesiaSuperRegionKeys) ? $filterCodeCmmt : null);
            if ($normalizedSrKeyForFilter && in_array($normalizedSrKeyForFilter, $this->indonesiaSuperRegionKeys)) {
                $isIndonesianSuperRegionFilter = true;
            }
        }
        
        $allowedIndonesianCitiesForRegionFilter = null;
        if ($isIndonesianSuperRegionFilter && $normalizedSrKeyForFilter) {
            $allowedIndonesianCitiesForRegionFilter = $this->getCitiesForSuperRegion($normalizedSrKeyForFilter);
        }

        // Loop untuk kota Indonesia
        foreach ($this->indonesiaCityData as $displayName => $cityInfo) {
            $dbKey = strtoupper(trim($cityInfo['db_key']));
            $sales = (float)($citySalesAggregation->get($dbKey, 0));

            $passesRegionFilter = true;
            if ($isIndonesianSuperRegionFilter) {
                $passesRegionFilter = $allowedIndonesianCitiesForRegionFilter !== null && in_array($dbKey, $allowedIndonesianCitiesForRegionFilter);
                 if ($allowedIndonesianCitiesForRegionFilter === null && ($normalizedSrKeyForFilter === "KEYACCOUNT" || $normalizedSrKeyForFilter === "COMMERCIAL")){
                    $passesRegionFilter = true;
                 }
            } elseif ($filterCodeCmmt && $filterCodeCmmt !== 'ALL' && $filterCodeCmmt !== 'INDONESIA' && !$isIndonesianSuperRegionFilter) {
                 $passesRegionFilter = false;
            }

            if ($passesRegionFilter) {
                if ($filterCityDbKey && $filterCityDbKey !== 'ALL') {
                    if ($dbKey === $filterCityDbKey && $sales > 0) {
                        $cityMarkers[] = ['name' => $displayName, 'db_key' => $dbKey, 'lat' => $cityInfo['lat'], 'lng' => $cityInfo['lng'], 'sales' => $sales];
                    }
                } else {
                    if ($sales > 0) {
                         $cityMarkers[] = ['name' => $displayName, 'db_key' => $dbKey, 'lat' => $cityInfo['lat'], 'lng' => $cityInfo['lng'], 'sales' => $sales];
                    }
                }
            }
        }

        // Loop untuk kota Internasional
        if (!empty($this->internationalCityData)) {
            foreach ($this->internationalCityData as $cityDbKeyForMarker => $cityInfo) {
                $sales = (float)($citySalesAggregation->get($cityDbKeyForMarker, 0));
                $countryCodeForThisCity = strtoupper(trim($cityInfo['country_code_cmmt_key']));
                $countryMatchesCodeCmmtFilter = (!$filterCodeCmmt || $filterCodeCmmt === 'ALL' || $countryCodeForThisCity === $filterCodeCmmt);

                if (!$isIndonesianSuperRegionFilter && $countryMatchesCodeCmmtFilter) {
                    if ($filterCityDbKey && $filterCityDbKey !== 'ALL') {
                        if ($cityDbKeyForMarker === $filterCityDbKey && $sales > 0) {
                            $internationalCityMarkers[] = ['name' => $cityInfo['display_name'], 'db_key' => $cityDbKeyForMarker, 'lat' => $cityInfo['lat'], 'lng' => $cityInfo['lng'], 'sales' => $sales, 'country' => $this->countryMapping[$countryCodeForThisCity] ?? $countryCodeForThisCity];
                        }
                    } else {
                        if ($sales > 0) {
                            $internationalCityMarkers[] = ['name' => $cityInfo['display_name'], 'db_key' => $cityDbKeyForMarker, 'lat' => $cityInfo['lat'], 'lng' => $cityInfo['lng'], 'sales' => $sales, 'country' => $this->countryMapping[$countryCodeForThisCity] ?? $countryCodeForThisCity];
                        }
                    }
                }
            }
        }

        return response()->json([
            'worldSales' => $worldSales,
            'indonesiaSuperRegionSales' => $indonesiaSuperRegionSales,
            'cityMarkers' => $cityMarkers,
            'internationalCityMarkers' => $internationalCityMarkers
        ]);
    }
}