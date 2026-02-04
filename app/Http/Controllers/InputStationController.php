<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\OilUtilityGasInputController;
use App\Http\Controllers\OilBatchRefineryInputController;

class InputStationController extends Controller
{
    /**
     * Menampilkan halaman stasiun input utama.
     * Secara dinamis merender formulir berdasarkan query parameter 'type'.
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $viewData = ['type' => $type];

        // Switch case untuk menentukan data apa yang perlu disiapkan
        switch ($type) {
            case 'utility_gas':
                // Memanggil logic dari controller yang ada untuk menyiapkan data
                $gasController = new OilUtilityGasInputController();
                // Menggabungkan data yang dikembalikan ke dalam $viewData
                $viewData = array_merge($viewData, $gasController->prepareDataForInput());
                break;

            case 'batch_refinery':
                // Memanggil logic dari controller yang ada untuk menyiapkan data
                $refineryController = new OilBatchRefineryInputController();
                // Menggabungkan data yang dikembalikan ke dalam $viewData
                $viewData = array_merge($viewData, $refineryController->prepareDataForInput());
                break;
        }

        return view('oil.input_station.index', $viewData);
    }
}