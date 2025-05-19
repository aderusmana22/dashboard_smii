<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardSalesController extends Controller
{
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
        // "INDONESIA" => "Indonesia", // We will sum super regions for Indonesia totals
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

    // These are the keys we expect in `indonesiaSuperRegionSales`
    protected $indonesiaSuperRegions = [
        "REGION 1A",
        "REGION 1B",
        "REGION 1C",
        "REGION 1D",
        "REGION 2A",
        "REGION 2B",
        "REGION 2C",
        "REGION 2D",
        "REGION 3A",
        "REGION 3B",
        "REGION 3C",
        "REGION 4A",
        "REGION 4B",
        "KEY ACCOUNT",
        "COMMERCIAL"
    ];

    protected function getAvailableDateRanges()
    {
        $minDate = SalesTransaction::selectRaw('MIN(STR_TO_DATE(tr_effdate, "%Y-%m-%d")) as min_date')->value('min_date');
        $maxDate = SalesTransaction::selectRaw('MAX(STR_TO_DATE(tr_effdate, "%Y-%m-%d")) as max_date')->value('max_date');

        if (!$minDate || !$maxDate) {
            $currentDate = Carbon::now();
            return [
                'min_date_iso' => $currentDate->format('Y-m-d'),
                'max_date_iso' => $currentDate->format('Y-m-d'),
                'initial_start_date' => $currentDate->startOfMonth()->format('Y-m-d'),
                'initial_end_date' => $currentDate->endOfMonth()->format('Y-m-d'),
            ];
        }

        $minCarbon = Carbon::parse($minDate);
        $maxCarbon = Carbon::parse($maxDate);

        return [
            'min_date_iso' => $minCarbon->format('Y-m-d'),
            'max_date_iso' => $maxCarbon->format('Y-m-d'),
            'initial_start_date' => $maxCarbon->copy()->startOfMonth()->format('Y-m-d'),
            'initial_end_date' => $maxCarbon->copy()->endOfMonth()->format('Y-m-d'),
        ];
    }

    public function showMapDashboard()
    {
        // Get date ranges from the getAvailableDateRanges method
        $dateRanges = $this->getAvailableDateRanges();
        
        return view('dashboard.dashboardSales', compact('dateRanges'));
    }

    public function getSalesData(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
        ]);

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $sales = SalesTransaction::select('code_cmmt', DB::raw('SUM(tr_ton) as total_ton'))
            ->whereBetween('tr_effdate', [$startDate, $endDate])
            ->groupBy('code_cmmt')
            ->get();

        $worldSales = [];
        $indonesiaSuperRegionSales = [];
        $totalIndonesiaSalesFromSuperRegions = 0;

        foreach ($sales as $sale) {
            $codeCmmtUpper = strtoupper(trim($sale->code_cmmt));
            $totalTon = (float) $sale->total_ton;

            // Check if it's an Indonesian Super Region first
            $regionKey = str_replace(' ', '', $codeCmmtUpper);
            if (in_array($codeCmmtUpper, $this->indonesiaSuperRegions)) {
                $indonesiaSuperRegionSales[$regionKey] = ($indonesiaSuperRegionSales[$regionKey] ?? 0) + $totalTon;
                $totalIndonesiaSalesFromSuperRegions += $totalTon;
            } elseif (isset($this->countryMapping[$codeCmmtUpper])) {
                $shapeName = $this->countryMapping[$codeCmmtUpper];
                $worldSales[$shapeName] = ($worldSales[$shapeName] ?? 0) + $totalTon;
            } elseif ($codeCmmtUpper !== 'INDONESIA') {
                $worldSales[$sale->code_cmmt] = ($worldSales[$sale->code_cmmt] ?? 0) + $totalTon;
            }
        }

        if ($totalIndonesiaSalesFromSuperRegions > 0) {
            $worldSales["Indonesia"] = $totalIndonesiaSalesFromSuperRegions;
        }


        return response()->json([
            'worldSales' => $worldSales,
            'indonesiaSuperRegionSales' => $indonesiaSuperRegionSales,
        ]);
    }
}