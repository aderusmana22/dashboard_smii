<?php

namespace App\Http\Controllers;

use App\Exports\SalesByBrandExport;
use App\Models\SalesTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesByBrandReports extends Controller
{
    /**
     * Menampilkan halaman laporan.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        // Mengatur nilai bulan default untuk filter
        return view('sales.reports.index', [
            'startMonth' => $request->input('start_month', Carbon::now()->format('Y-m')),
            'endMonth'   => $request->input('end_month', Carbon::now()->format('Y-m')),
        ]);
    }

    /**
     * Mengambil data penjualan untuk DataTables melalui AJAX.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchData(Request $request): JsonResponse
    {
        // 1. Dapatkan rentang bulan dari request AJAX
        $startMonth = $request->input('start_month', Carbon::now()->format('Y-m'));
        $endMonth = $request->input('end_month', Carbon::now()->format('Y-m'));

        // 2. Ubah input bulan menjadi rentang tanggal penuh
        $startDate = Carbon::parse($startMonth)->startOfMonth();
        $endDate = Carbon::parse($endMonth)->endOfMonth();

        // 3. Buat string periode untuk tampilan
        $period = $startDate->format('F Y');
        if ($startDate->format('Y-m') !== $endDate->format('Y-m')) {
            $period .= ' - ' . $endDate->format('F Y');
        }

        // 4. Query data, menggabungkan seluruh periode menjadi satu baris per brand
        $salesData = SalesTransaction::query()
            ->select(
                'pl_desc as brand',
                DB::raw('SUM(tr_ton) as total_tonnage'),
                DB::raw('SUM(value) as total_value'),
                DB::raw('SUM(margin) as total_margin')
            )
            ->whereBetween('tr_effdate', [$startDate, $endDate])
            ->groupBy('pl_desc') // Group by brand description
            ->orderBy('pl_desc')
            ->get()
            ->map(function ($item) use ($period) {
                // Tambahkan periode yang dihitung ke setiap item hasil
                $item->period = $period;
                return $item;
            });

        // 5. Kembalikan data dalam format yang dibutuhkan oleh DataTables
        return response()->json(['data' => $salesData]);
    }
    
    /**
     * Menangani permintaan ekspor Excel.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request)
    {
        // 1. Dapatkan rentang bulan dari request
        $startMonth = $request->input('start_month', Carbon::now()->format('Y-m'));
        $endMonth = $request->input('end_month', Carbon::now()->format('Y-m'));

        // 2. Buat nama file yang dinamis
        $fileName = 'Sales-by-Brand-Report-' . $startMonth . '-to-' . $endMonth . '.xlsx';

        // 3. Panggil Export Class dan unduh file
        return Excel::download(new SalesByBrandExport($startMonth, $endMonth), $fileName);
    }
}