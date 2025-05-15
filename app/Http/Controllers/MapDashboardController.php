<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction; // Pastikan ini SalesTransaction, bukan Sales
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MapDashboardController extends Controller
{
    // ... (countryMapping dan indonesiaSuperRegions tetap sama) ...
    protected $countryMapping = [
        "TAIWAN" => "Taiwan",
        "PHILLIPPINES" => "Philippines",
        "MALAYSIA" => "Malaysia",
        "MYANMAR" => "Myanmar",
        "EXPORT AUSTRALIA" => "Australia",
        "SRILANKA" => "Sri Lanka",
        "UNI ARAB EMIRATES" => "United Arab Emirates",
        "HONGKONG" => "Hong Kong",
        "PEOPLE'S REPUBLIC OF CHINA" => "China",
        "INDONESIA" => "Indonesia",
        "BRAZIL" => "Brazil",
        "UNITED STATES OF AMERICA" => "United States of America",
        "GERMANY" => "Germany",
        "INDIA" => "India",
        "CANADA" => "Canada",
        "SOUTH AFRICA" => "South Africa",
        "NEPAL" => "Nepal",
        "FIJI" => "Fiji",
        "RUSSIAN FEDERATION" => "Russia"
    ];

    protected $indonesiaSuperRegions = [
        "REGION 1A", "REGION 1B", "REGION 1C", "REGION 1D",
        "REGION 2A", "REGION 2B", "REGION 2C", "REGION 2D",
        "REGION 3A", "REGION 3B", "REGION 3C",
        "REGION 4A", "REGION 4B",
        "KEY ACCOUNT",
        "COMMERCIAL"
    ];

    /**
     * Menampilkan halaman dashboard peta dan mengirimkan data rentang tanggal.
     */
    public function showMapDashboard() // Metode ini akan dipanggil oleh route
    {
        $dateRanges = $this->getAvailableDateRanges();
        return view('dashboard.map-sales', compact('dateRanges'));
    }

    /**
     * Mengambil rentang tahun dan bulan yang tersedia dari data sales.
     */
    protected function getAvailableDateRanges()
    {
        $minDate = SalesTransaction::min('tr_effdate');
        $maxDate = SalesTransaction::max('tr_effdate');

        if (!$minDate || !$maxDate) {
            $currentYear = date('Y');
            $currentMonth = date('n'); // 1-12
            return [
                'min_year' => (int)$currentYear,
                'max_year' => (int)$currentYear,
                'available_years' => [(int)$currentYear],
                'initial_year' => (int)$currentYear,
                'initial_month' => (int)$currentMonth,
                'available_months_for_initial_year' => [$currentMonth => Carbon::createFromDate(null, $currentMonth, 1)->format('F')]
            ];
        }

        $minCarbon = Carbon::parse($minDate);
        $maxCarbon = Carbon::parse($maxDate);

        $availableYears = [];
        for ($year = $minCarbon->year; $year <= $maxCarbon->year; $year++) {
            $availableYears[] = $year;
        }
        
        $initialYear = $maxCarbon->year;
        $initialMonth = $maxCarbon->month;

        $monthsInInitialYearQuery = SalesTransaction::select(DB::raw('DISTINCT MONTH(tr_effdate) as month_num'))
            ->whereYear('tr_effdate', $initialYear)
            ->orderBy('month_num')
            ->get();

        $availableMonthsForInitialYear = [];
        foreach ($monthsInInitialYearQuery as $monthData) {
            $availableMonthsForInitialYear[$monthData->month_num] = Carbon::createFromDate($initialYear, $monthData->month_num, 1)->format('F');
        }

        return [
            'min_year' => $minCarbon->year,
            'max_year' => $maxCarbon->year,
            'available_years' => $availableYears,
            'initial_year' => $initialYear,
            'initial_month' => $initialMonth,
            'available_months_for_initial_year' => $availableMonthsForInitialYear,
        ];
    }

    public function getMonthsForYear(Request $request)
    {
        $request->validate(['year' => 'required|integer']);
        $year = $request->input('year');

        $monthsQuery = SalesTransaction::select(DB::raw('DISTINCT MONTH(tr_effdate) as month_num'))
            ->whereYear('tr_effdate', $year)
            ->orderBy('month_num')
            ->get();

        $availableMonths = [];
        foreach ($monthsQuery as $monthData) {
            $availableMonths[$monthData->month_num] = Carbon::createFromDate($year, $monthData->month_num, 1)->format('F');
        }
        return response()->json($availableMonths);
    }

    public function getSalesData(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        $sales = SalesTransaction::select('code_cmmt', DB::raw('SUM(tr_ton) as total_ton'))
            ->whereYear('tr_effdate', $year)
            ->whereMonth('tr_effdate', $month)
            ->groupBy('code_cmmt')
            ->get();

        $worldSales = [];
        $indonesiaSuperRegionSales = [];

        foreach ($sales as $sale) {
            $codeCmmtUpper = strtoupper(trim($sale->code_cmmt));
            $totalTon = (float) $sale->total_ton;

            if (in_array($codeCmmtUpper, $this->indonesiaSuperRegions)) {
                $regionKey = str_replace(' ', '', $codeCmmtUpper);
                $indonesiaSuperRegionSales[$regionKey] = ($indonesiaSuperRegionSales[$regionKey] ?? 0) + $totalTon;
            }
            elseif (isset($this->countryMapping[$codeCmmtUpper])) {
                $shapeName = $this->countryMapping[$codeCmmtUpper];
                $worldSales[$shapeName] = ($worldSales[$shapeName] ?? 0) + $totalTon;
            }
            else {
                $worldSales[$sale->code_cmmt] = ($worldSales[$sale->code_cmmt] ?? 0) + $totalTon;
            }
        }
        
        return response()->json([
            'worldSales' => $worldSales,
            'indonesiaSuperRegionSales' => $indonesiaSuperRegionSales,
        ]);
    }
}