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
        "MYANMAR" => "Myanmar",
        "EXPORT AUSTRALIA" => "Australia",
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
        "RUSSIAN FEDERATION" => "Russia"

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
        return view('dashboard.dashboardSales', compact('dateRanges'));
    }

    public function getSalesData(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
        ]);

        $startDate = Carbon::parse($request->input('startDate'));
        $endDate = Carbon::parse($request->input('endDate'));

        $period = CarbonPeriod::create($startDate, $endDate);
        $yearsInRange = [];
        foreach ($period as $date) {
            $yearsInRange[] = $date->year;
        }
        $uniqueYears = array_unique($yearsInRange);

        $currentSalesQuery = SalesTransaction::select('code_cmmt', DB::raw('SUM(tr_ton) as total_ton'))
            ->whereBetween('tr_effdate', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('code_cmmt')
            ->get();
        $currentSalesData = $currentSalesQuery->pluck('total_ton', 'code_cmmt');

        $lastYearStartDate = $startDate->copy()->subYear()->format('Y-m-d');
        $lastYearEndDate = $endDate->copy()->subYear()->format('Y-m-d');
        $lastYearSalesQuery = SalesTransaction::select('code_cmmt', DB::raw('SUM(tr_ton) as total_ton_ly'))
            ->whereBetween('tr_effdate', [$lastYearStartDate, $lastYearEndDate])
            ->groupBy('code_cmmt')
            ->get();
        $lastYearSalesData = $lastYearSalesQuery->pluck('total_ton_ly', 'code_cmmt');

        $rawBudgetsQuery = DB::table('standard_budgets')
            ->whereIn('year', $uniqueYears) 
            ->select('name_region', 'amount') 
            ->get();

        $budgets = [];
        foreach ($rawBudgetsQuery as $budgetEntry) {

            $regionKey = strtoupper(str_replace(' ', '', trim($budgetEntry->name_region)));
            $amount = (float) $budgetEntry->amount;

            if (!isset($budgets[$regionKey])) {
                $budgets[$regionKey] = 0;
            }
            $budgets[$regionKey] += $amount; 
        }

        $worldSales = [];
        $indonesiaSuperRegionSales = [];

        foreach ($this->indonesiaSuperRegionKeys as $srKey) {
            $indonesiaSuperRegionSales[$srKey] = [
                'sales' => 0,
                'budget' => $budgets[$srKey] ?? 0, 
                'lastYearSales' => 0
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

        foreach ($currentSalesData as $codeCmmt => $totalTon) {
            $codeCmmtUpper = strtoupper(trim($codeCmmt));
            $totalTon = (float) $totalTon;
            $superRegionKey = $this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUpper] ?? null;

            if ($superRegionKey && in_array($superRegionKey, $this->indonesiaSuperRegionKeys)) {
                $indonesiaSuperRegionSales[$superRegionKey]['sales'] += $totalTon;
            } elseif (isset($this->countryMapping[$codeCmmtUpper])) { 
                $shapeName = $this->countryMapping[$codeCmmtUpper]; 

                if (!isset($worldSales[$shapeName])) {
                    $budgetKey = strtoupper(str_replace(' ', '', $shapeName));
                    if ($budgetKey === "UNITEDSTATESOFAMERICA") $budgetKey = "UNITEDSTATES";
                    $worldSales[$shapeName] = ['sales' => 0, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => 0];
                }
                $worldSales[$shapeName]['sales'] += $totalTon;
            } elseif ($codeCmmtUpper !== 'INDONESIA') { 

                $displayName = $codeCmmt; 
                if (!isset($worldSales[$displayName])) {
                    $budgetKey = strtoupper(str_replace(' ', '', $codeCmmtUpper));
                    $worldSales[$displayName] = ['sales' => 0, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => 0];
                }
                $worldSales[$displayName]['sales'] += $totalTon;
            }
        }

        foreach ($lastYearSalesData as $codeCmmt => $totalTonLy) {
            $codeCmmtUpper = strtoupper(trim($codeCmmt));
            $totalTonLy = (float) $totalTonLy;
            $superRegionKey = $this->rawCodeCmmtToSuperRegionKeyMap[$codeCmmtUpper] ?? null;

            if ($superRegionKey && in_array($superRegionKey, $this->indonesiaSuperRegionKeys)) {
                $indonesiaSuperRegionSales[$superRegionKey]['lastYearSales'] += $totalTonLy;
            } elseif (isset($this->countryMapping[$codeCmmtUpper])) {
                $shapeName = $this->countryMapping[$codeCmmtUpper];
                if (!isset($worldSales[$shapeName])) { 
                    $budgetKey = strtoupper(str_replace(' ', '', $shapeName));
                    if ($budgetKey === "UNITEDSTATESOFAMERICA") $budgetKey = "UNITEDSTATES";
                    $worldSales[$shapeName] = ['sales' => 0, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => 0];
                }
                $worldSales[$shapeName]['lastYearSales'] += $totalTonLy;
            } elseif ($codeCmmtUpper !== 'INDONESIA') {
                $displayName = $codeCmmt;
                if (!isset($worldSales[$displayName])) {
                    $budgetKey = strtoupper(str_replace(' ', '', $codeCmmtUpper));
                    $worldSales[$displayName] = ['sales' => 0, 'budget' => $budgets[$budgetKey] ?? 0, 'lastYearSales' => 0];
                }
                $worldSales[$displayName]['lastYearSales'] += $totalTonLy;
            }
        }

        $totalIndonesiaSales = 0;
        $totalIndonesiaBudget = 0;
        $totalIndonesiaLastYearSales = 0;

        foreach ($this->indonesiaSuperRegionKeys as $srKey) {
            if (isset($indonesiaSuperRegionSales[$srKey])) {
                $totalIndonesiaSales += $indonesiaSuperRegionSales[$srKey]['sales'];
                $totalIndonesiaBudget += $indonesiaSuperRegionSales[$srKey]['budget']; 
                $totalIndonesiaLastYearSales += $indonesiaSuperRegionSales[$srKey]['lastYearSales'];
            }
        }

        $indonesiaOverallBudgetKey = strtoupper("INDONESIA");
        if (isset($budgets[$indonesiaOverallBudgetKey])) {

            $totalIndonesiaBudget = $budgets[$indonesiaOverallBudgetKey];
        }

        $worldSales["Indonesia"] = [
            'sales' => $totalIndonesiaSales,
            'budget' => $totalIndonesiaBudget,
            'lastYearSales' => $totalIndonesiaLastYearSales
        ];

        if (isset($worldSales['INDONESIA']) && array_key_exists('INDONESIA', $currentSalesData)) {
            unset($worldSales['INDONESIA']);
        }

        return response()->json([
            'worldSales' => $worldSales,
            'indonesiaSuperRegionSales' => $indonesiaSuperRegionSales,
        ]);
    }
}