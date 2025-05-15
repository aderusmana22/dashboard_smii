<?php

namespace App\Http\Controllers\QAD;

use App\Http\Controllers\Controller;
use App\Models\QAD\Production;
use App\Models\QAD\StandardProduction;
use App\Models\QAD\StandardWarehouseProduction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class ProductionController extends Controller
{


    // =====================================================Production==============================================

    /*Dasboard Production */

    public function dashboardProductionIndex()
    {
        // Ambil tahun-tahun unik dari data Production
        $availableYears = Production::selectRaw('YEAR(tr_effdate) AS year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        return view("dashboard.dashboardProduction", compact('availableYears'));
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Production::query();
            return DataTables::of($data)->make(true);
        }
        return view('page.dataDashboard.production-index');
    }

    private function httpHeader($req)
    {
        return array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: ""',        // jika tidak pakai SOAPAction, isinya harus ada tanda petik 2 --> ""
            'Content-length: ' . strlen(preg_replace("/\s+/", " ", $req))
        );
    }

    public function getProductions()
    {
        $qxUrl = 'http://smii.qad:24079/wsa/smiiwsa';
        $timeout = 10;
        $domain = 'SMII';
        $batchSize = 2000; // Ukuran batch
        $offset = 0;
        $totalNewItems = 0;
        $totalUpdatedItems = 0;
        $startdate = date('Y-m-d', strtotime('-1 day'));
        $enddate = date('Y-m-d');

        do {
            $qdocRequest =
                '<Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                    <Body>
                        <getproduction xmlns="urn:services-qad-com:smiiwsa:0001:smiiwsa">
                            <ip_domain>' . $domain . '</ip_domain>
                            <ip_start_date>' . $startdate . '</ip_start_date>
                            <ip_end_date>' . $enddate . '</ip_end_date>
                            <ip_batch_size>' . $batchSize . '</ip_batch_size>
                            <ip_offset>' . $offset . '</ip_offset>
                        </getproduction>
                    </Body>
                </Envelope>';


            $curlOptions = array(
                CURLOPT_URL => $qxUrl,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_TIMEOUT => $timeout + 5,
                CURLOPT_HTTPHEADER => $this->httpHeader($qdocRequest),
                CURLOPT_POSTFIELDS => preg_replace("/\s+/", " ", $qdocRequest),
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            );

            $curl = curl_init();
            if ($curl) {
                curl_setopt_array($curl, $curlOptions);
                $qdocResponse = curl_exec($curl);
                curl_close($curl);
            } else {
                Log::channel('production')->error('Gagal menghubungi server.');
                return redirect()->back()->with('error', 'Gagal menghubungi server.');
            }

            if (!$qdocResponse) {
                Log::channel('production')->error('Tidak ada respons dari server.');
                return redirect()->back()->with('error', 'Tidak ada respons dari server.');
            }


            $xmlResp = simplexml_load_string($qdocResponse);
            $xmlResp->registerXPathNamespace('ns', 'urn:services-qad-com:smiiwsa:0001:smiiwsa');

            $qdocResult = (string) $xmlResp->xpath('//ns:opOk')[0];

            $invoices = $xmlResp->xpath('//ns:getproductionResponse/ns:ttTrHistData/ns:ttTrHistDataRow');
            $jumlahItemBaru = 0;
            $jumlahItemUpdate = 0;

            if ($qdocResult == 'true') {
                foreach ($invoices as $item) {
                    $tr_trnbr = (string) $item->tr_trnbr;
                    $tr_nbr = (string) $item->tr_nbr;
                    $tr_effdate = (string) $item->tr_effdate;
                    $tr_type = (string) $item->tr_type;
                    $tr_prod_line = (string) $item->tr_prod_line;
                    $tr_part = (string) $item->tr_part;
                    $pt_desc1 = (string) $item->pt_desc1;
                    $tr_qty_loc = (string) $item->tr_qty_loc;
                    $Weight_in_KG = (string) $item->Weight_in_KG;
                    $Line = (string) $item->Line;
                    $pt_draw = (string) $item->pt_draw;
                    $shift     = (string) $item->Shift;
                    $shift_date = (string) $item->Shift_Date;

                    $productionData = [
                        'tr_nbr' => $tr_nbr,
                        'tr_effdate' => $tr_effdate,
                        'tr_type' => $tr_type,
                        'tr_prod_line' => $tr_prod_line,
                        'tr_part' => $tr_part,
                        'pt_desc1' => $pt_desc1,
                        'tr_qty_loc' => floatval($tr_qty_loc),
                        'Weight_in_KG' => floatval($Weight_in_KG),
                        'Line' => $Line,
                        'pt_draw' => $pt_draw,
                        'shift' => $shift,
                        'shift_date' => $shift_date,
                    ];

                    $existingInvoice = Production::updateOrCreate(
                        ['tr_trnbr' => $tr_trnbr],
                        $productionData
                    );

                    if ($existingInvoice->wasRecentlyCreated) {
                        $jumlahItemBaru++;
                    } else {
                        $jumlahItemUpdate++;
                    }
                }
                $totalNewItems += $jumlahItemBaru;
                $totalUpdatedItems += $jumlahItemUpdate;
                $waktuSekarang = now();
            } else {
                session(['toastMessage' => 'Gagal mengambil data dari server.', 'toastType' => 'error']);
                return redirect()->back();
            }

            $offset += $batchSize;
        } while (count($invoices) == $batchSize);

        session(['toastMessage' => 'Data berhasil disimpan. Jumlah item baru: ' . $totalNewItems . ', Jumlah item update: ' . $totalUpdatedItems, 'toastType' => 'success']);
        return redirect()->back();
    }

    // =============================================standardProduction==============================================

    public function standardProduction()
    {
        $standardProductions = StandardProduction::all();
        return \view('page.standard.production-index', \compact('standardProductions'));
    }

    public function storeStandardProductions(Request $request)
    {
        $standardProduction = new StandardProduction();
        $standardProduction->line = $request->line;
        $standardProduction->total = $request->total;
        $standardProduction->save();
        Alert::toast('Standard Production successfully added', 'success');
        return redirect()->back();
    }

    public function updateStandardProductions(Request $request, $id)
    {
        $standardProduction = StandardProduction::findOrFail($id);
        $standardProduction->line = $request->line;
        $standardProduction->total = $request->total;
        $standardProduction->save();
        Alert::toast('Standard Production successfully updated', 'success');
        return redirect()->back();
    }

    public function destroyStandardProductions($id)
    {
        $standardProduction = StandardProduction::findOrFail($id);
        $standardProduction->delete();
        Alert::toast('Standard Production successfully deleted', 'success');
        return redirect()->back();
    }

    // =========================================================StandardWarehouse=====================================================

    public function warehouseindex()
    {
        $standardWarehouse = StandardWarehouseProduction::all();
        return \view('page.standard.warehouse-index', \compact('standardWarehouse'));
    }

    public function warehousestore(Request $request)
    {
        $standardWarehouse = new StandardWarehouseProduction();
        $standardWarehouse->location = $request->location;
        $standardWarehouse->rack = $request->rack;
        $standardWarehouse->temperature = $request->temperature;
        $standardWarehouse->pallet_rack = $request->pallet_rack;
        $standardWarehouse->estimated_tonnage = $request->estimated_tonnage;
        $standardWarehouse->save();
        Alert::toast('Standard Warehouse Production successfully added', 'success');
        return redirect()->back();
    }

    public function warehouseupdate(Request $request, $id)
    {
        $standardWarehouse = StandardWarehouseProduction::findOrFail($id);
        $standardWarehouse->location = $request->location;
        $standardWarehouse->rack = $request->rack;
        $standardWarehouse->temperature = $request->temperature;
        $standardWarehouse->pallet_rack = $request->pallet_rack;
        $standardWarehouse->estimated_tonnage = $request->estimated_tonnage;
        $standardWarehouse->save();
        Alert::toast('Standard Warehouse Production successfully updated', 'success');
        return redirect()->back();
    }

    public function warehousedelete($id)
    {
        $standardWarehouse = StandardWarehouseProduction::findOrFail($id);
        $standardWarehouse->delete();
        Alert::toast('Standard Production successfully deleted', 'success');
        return redirect()->back();
    }


    // ============================================DataMonth=======================================================

    public function getProductionData(Request $request)
    {
        try {
            $month = $request->query('month');
            $week = $request->query('week');

            $query = DB::table('productions')
                ->select('Line', DB::raw('SUM(Weight_in_KG) as total_qty'), DB::raw('SUM(standard_total) as standard_total'))
                ->groupBy('Line');

            if ($month) {
                $query->whereMonth('tr_effdate', Carbon::parse($month)->month);
            }

            if ($week) {
                $startOfWeek = Carbon::now()->startOfMonth()->addWeeks($week - 1)->startOfWeek();
                $endOfWeek = $startOfWeek->copy()->endOfWeek();
                $query->whereBetween('tr_effdate', [$startOfWeek, $endOfWeek]);
            }

            $data = $query->get();

            return response()->json([
                'labels' => $data->pluck('tr_prod_line'),
                'actual' => $data->pluck('total_qty'),
                'standard' => $data->pluck('standard_total')
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function dashboardProduction(Request $request)
    {
        // Data untuk standar production gauge chart
        $gaugeStandarData = StandardProduction::select('line')->distinct()->get()->mapWithKeys(function ($item) {
            return [$item->line => StandardProduction::where('line', $item->line)->sum('total')];
        })->toArray();
        return response()->json(compact(
            'gaugeStandarData',
        ));
    }

    public function getBarData(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        // Jika bulan atau tahun tidak diberikan, gunakan bulan dan tahun saat ini
        $month = $month ? $month : $currentMonth;
        $year = $year ? $year : $currentYear;

        $query = Production::query();

        $query->whereYear('tr_effdate', $year)
            ->whereMonth('tr_effdate', $month);

        $barData = $query->select('Line', DB::raw('SUM(Weight_in_KG) as total_qty'), DB::raw('DAY(tr_effdate) as day'))
            ->groupBy('tr_effdate', 'Line')
            ->orderBy('tr_effdate', 'asc')
            ->orderBy('line', 'asc')
            ->get();

        $standardData = StandardProduction::select(DB::raw('SUM(total) as total'))->get();

        // Mengorganisir data actual_qty supaya bisa ditampilkan sesuai dengan hari
        $hashMap = [];
        $temp = ['A', 'B', 'C', 'D', 'E'];
        $count = 0;
        $sumHeight = 0;
        $maxHeight = 0;
        foreach ($barData as $dataBar) {
            if (in_array(strtoupper($dataBar->Line), $temp)) {
                $index = $count % 5;
                if ($index == 0) {
                    $sumHeight = 0;
                }

                // Jika ada yang terskip akan diisi 0
                if ($temp[$index] != strtoupper($dataBar->Line)) {
                    $index2 = $count % 5;
                    while ($temp[$index2] != strtoupper($dataBar->Line)) {
                        $hashMap[$temp[$index2]][] = 0;
                        $count += 1;
                        $index2 = $count % 5;
                        if ($index2 == 0) {
                            $sumHeight = 0;
                        }
                    }
                }

                // Menyimpan nilai qty kedalam hashmap
                $hashMap[strtoupper($dataBar->Line)][] = $dataBar->total_qty;
                $sumHeight += $dataBar->total_qty;
                $maxHeight = max($sumHeight, $maxHeight);
                $count += 1;
            }
        }

        // Mengubah hashmap menjadi 2d array sesuai urutan
        $hashMap2 =  array_values($hashMap);

        // Perbaikan perhitungan weight comparison
        $prevMonth = $month - 1;
        $prevYear = $year;

        if ($prevMonth == 0) {
            $prevMonth = 12;
            $prevYear -= 1;
        }

        $weightLastMonth = Production::whereMonth('tr_effdate', $prevMonth)
            ->whereYear('tr_effdate', $prevYear)
            ->sum('Weight_in_KG');

        $weightThisMonth = Production::whereMonth('tr_effdate', $month)
            ->whereYear('tr_effdate', $year)
            ->sum('Weight_in_KG');

        $weightComparison = $this->getComparison($weightLastMonth, $weightThisMonth);

        // Perbaikan perhitungan qty comparison
        $qtyLastMonth = Production::whereMonth('tr_effdate', $prevMonth)
            ->whereYear('tr_effdate', $prevYear)
            ->sum('tr_qty_loc');

        $qtyThisMonth = Production::whereMonth('tr_effdate', $month)
            ->whereYear('tr_effdate', $year)
            ->sum('tr_qty_loc');

        $qtyComparison = $this->getComparison($qtyLastMonth, $qtyThisMonth);

        return response()->json([
            'actual_height' => $maxHeight,
            'labels' => $barData->pluck('day'),
            'actual_qty' => $hashMap2,
            'standard_qty' => $standardData->pluck('total'),
            'weightLastMonth' => $weightLastMonth,
            'weightThisMonth' => $weightThisMonth,
            'weightComparison' => $weightComparison,
            'qtyLastMonth' => number_format($qtyLastMonth, 0, ',', '.'),
            'qtyThisMonth' => number_format($qtyThisMonth, 0, ',', '.'),
            'qtyComparison' => $qtyComparison
        ]);
    }

    public function streamBarData(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        return response()->stream(function () use ($month, $year, $currentMonth, $currentYear) {
            $lastData = null; // Menyimpan data terakhir yang dikirim

            while (true) {
                $query = Production::query();

                if ($year && $month) {
                    $query->whereYear('tr_effdate', $year)
                        ->whereMonth('tr_effdate', $month);
                } else if ($year) {
                    $query->whereYear('tr_effdate', $year)
                        ->whereMonth('tr_effdate', $currentMonth);
                } else if ($month) {
                    $query->whereYear('tr_effdate', $currentYear)
                        ->whereMonth('tr_effdate', $month);
                } else {
                    $query->whereYear('tr_effdate', $currentYear)
                        ->whereMonth('tr_effdate', $currentMonth);
                }

                $barData = $query->select('Line', DB::raw('SUM(Weight_in_KG) as total_qty'), DB::raw('DAY(tr_effdate) as day'))
                    ->groupBy('tr_effdate', 'Line')
                    ->orderBy('tr_effdate', 'asc')
                    ->orderBy('Line', 'asc')
                    ->get();

                $standardData = StandardProduction::select(DB::raw('SUM(total) as total'))->get();

                $hashMap = [];
                $temp = ['A', 'B', 'C', 'D', 'E'];
                $count = 0;
                $sumHeight = 0;
                $maxHeight = 0;

                foreach ($barData as $dataBar) {
                    if (in_array(strtoupper($dataBar->Line), $temp)) {
                        $index = $count % 5;
                        if ($index == 0) {
                            $sumHeight = 0;
                        }

                        if ($temp[$index] != strtoupper($dataBar->Line)) {
                            $index2 = $count % 5;
                            while ($temp[$index2] != strtoupper($dataBar->Line)) {
                                $hashMap[$temp[$index2]][] = 0;
                                $count += 1;
                                $index2 = $count % 5;
                                if ($index2 == 0) {
                                    $sumHeight = 0;
                                }
                            }
                        }

                        $hashMap[strtoupper($dataBar->Line)][] = $dataBar->total_qty;
                        $sumHeight += $dataBar->total_qty;
                        $maxHeight = max($sumHeight, $maxHeight);
                        $count += 1;
                    }
                }

                $hashMap2 = array_values($hashMap);

                $prevMonth = $month ? (($month - 1) == 0 ? 12 : $month - 1) : now()->subMonth()->month;
                $weightLastMonth = Production::whereMonth('tr_effdate', $prevMonth)
                    ->whereYear('tr_effdate', $year ? $year : ($month == 1 ? $currentYear - 1 : $currentYear))
                    ->sum('Weight_in_KG');

                $weightThisMonth = Production::whereMonth('tr_effdate', $month ? $month : now()->month)
                    ->whereYear('tr_effdate', $year ? $year : now()->year)
                    ->sum('Weight_in_KG');

                $weightComparison = $this->getComparison($weightLastMonth, $weightThisMonth);

                $qtyLastMonth = Production::whereMonth('tr_effdate', $prevMonth)
                    ->whereYear('tr_effdate', $year ? $year : ($month == 1 ? $currentYear - 1 : $currentYear))
                    ->sum('tr_qty_loc');

                $qtyThisMonth = Production::whereMonth('tr_effdate', $month ? $month : now()->month)
                    ->whereYear('tr_effdate', $year ? $year : now()->year)
                    ->sum('tr_qty_loc');

                $qtyComparison = $this->getComparison($qtyLastMonth, $qtyThisMonth);

                // Siapkan data untuk dikirim
                $data = [
                    'actual_height' => $maxHeight,
                    'labels' => $barData->pluck('day'),
                    'actual_qty' => $hashMap2,
                    'standard_qty' => $standardData->pluck('total'),
                    'weightLastMonth' => $weightLastMonth,
                    'weightThisMonth' => $weightThisMonth,
                    'weightComparison' => $weightComparison,
                    'qtyLastMonth' => number_format($qtyLastMonth, 0, ',', '.'),
                    'qtyThisMonth' => number_format($qtyThisMonth, 0, ',', '.'),
                    'qtyComparison' => $qtyComparison
                ];

                // Bandingkan dengan data terakhir yang dikirim
                if ($lastData !== json_encode($data)) {
                    echo "data: " . json_encode($data) . "\n\n";
                    ob_flush();
                    flush();
                    $lastData = json_encode($data); // Simpan data terakhir yang dikirim
                }

                if (connection_aborted()) break;
                sleep(20);
            }
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache']);
    }



    public function filterData(Request $request)
    {
        $date = $request->input('date');

        if (!$date) {
            return ['data' => [], 'grandTotal' => 0]; // Kembalikan array kosong jika tanggal tidak valid
        }

        $data = Production::select(DB::raw('upper(line) as line'), 'shift', DB::raw('SUM(Weight_in_KG) as total_weight'))
            ->where(function ($query) use ($date) {
                $query->where(function ($query) use ($date) {
                    $query->where('shift', 'Shift 1')
                        ->whereDate('shift_date', Carbon::parse($date)->addDay());
                })
                    ->orWhere(function ($query) use ($date) {
                        $query->whereIn('shift', ['Shift 2', 'Shift 3'])
                            ->whereDate('shift_date', $date);
                    });
            })
            ->groupBy('line', 'shift')
            ->get(); // Pastikan ini mengembalikan koleksi

        $grandTotal = $data->sum('total_weight');

        // Kembalikan data sebagai array
        return ['data' => $data, 'grandTotal' => $grandTotal];
    }

    private function getYearlyComparison($lastYearTotal, $thisYearTotal)
    {
        if ($lastYearTotal == 0) {
            return 'N/A';
        }
        $difference = $thisYearTotal - $lastYearTotal;
        $percentage = number_format(($difference / $lastYearTotal) * 100, 3);
        return $percentage > 0 ? "Up by $percentage%" : "Down by $percentage%";
    }

    public function getBarDataByYear(Request $request)
    {
        // Mengambil tahun dari query parameter
        $year = $request->query('year');
        $currentYear = Carbon::today()->year;
        $currentDate = Carbon::today();

        // Validasi input
        if (!$year || !is_numeric($year)) {
            return response()->json(['error' => 'Invalid year parameter'], 400);
        }

        try {
            $query = Production::query();

            // Cek apakah data tahun yang diminta sudah lengkap hingga akhir tahun
            $isFullYear = Production::whereYear('tr_effdate', $year)
                ->whereMonth('tr_effdate', 12)
                ->whereDay('tr_effdate', 31)
                ->exists();

            if ($isFullYear) {
                // Jika data tahun penuh, ambil data untuk tahun penuh
                $query->whereYear('tr_effdate', $year);
            } else {
                // Jika tidak, batasi hingga tanggal saat ini
                $query->whereYear('tr_effdate', $year)
                    ->whereDate('tr_effdate', '<=', $currentDate);
            }

            // Mengelompokkan data berdasarkan bulan dan Line
            $barData = $query->select(
                DB::raw('UPPER(Line) as Line'), // Mengubah Line menjadi huruf besar
                DB::raw('SUM(Weight_in_KG) as total_qty'),
                DB::raw('DATE_FORMAT(tr_effdate, "%Y-%m") as month')
            )
                ->groupBy('month', 'Line')
                ->orderBy('month', 'asc')
                ->orderBy('Line', 'asc')
                ->get();

            $totalStandardQty = StandardProduction::sum('total') * 30;

            // Mengorganisir data actual_qty supaya bisa ditampilkan sesuai dengan bulan
            $lines = ['A', 'B', 'C', 'D', 'E'];
            $actualQty = [];
            $monthNames = [];
            $monthlySums = array_fill(0, 12, 0);

            foreach ($lines as $line) {
                $lineData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $monthData = $barData->firstWhere(function ($data) use ($i, $line, $year) {
                        return $data->month == sprintf('%04d-%02d', $year, $i) && strtoupper($data->Line) === $line;
                    });
                    $totalQty = $monthData ? $monthData->total_qty : 0;
                    $lineData[] = $totalQty;
                    $monthlySums[$i - 1] += $totalQty;

                    if (!isset($monthNames[$i - 1])) {
                        $monthNames[$i - 1] = Carbon::create()->month($i)->locale('id')->translatedFormat('M');
                    }
                }
                $actualQty[$line] = $lineData;
            }

            $maxHeight = max($monthlySums);

            // Menghitung total produksi tahun sebelumnya
            $lastYearTotalQuery = Production::whereYear('tr_effdate', $year - 1);
            if (!$isFullYear) {
                $lastYearTotalQuery->whereDate('tr_effdate', '<=', $currentDate->copy()->subYear());
            }
            $lastYearTotal = $lastYearTotalQuery->sum('Weight_in_KG');
            $thisYearTotal = array_sum($monthlySums);

            // Menghitung perbandingan tahunan
            $yearlyComparison = $this->getYearlyComparison($lastYearTotal, $thisYearTotal);

            return response()->json([
                'actual_height' => $maxHeight,
                'labels' => $monthNames,
                'actual_qty' => $actualQty,
                'standard_qty' => $totalStandardQty,
                'lastYearTotal' => $lastYearTotal,
                'thisYearTotal' => $thisYearTotal,
                'yearly_comparison' => $yearlyComparison
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching year data: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    private function getComparison($lastMonth, $thisMonth)
    {
        if ($lastMonth == 0) {
            return 'N/A';
        }
        $difference = $thisMonth - $lastMonth;
        $percentage = number_format(($difference / $lastMonth) * 100, 3);
        return $percentage > 0 ? "Up by $percentage%" : "Down by $percentage%";
    }
}
