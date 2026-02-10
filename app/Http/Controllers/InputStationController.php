<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InputStationController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $data = [];

        if ($type === 'utility_gas') {
            // Ambil data untuk Utility Gas
            $controller = new OilUtilityGasInputController();
            $data = $controller->prepareDataForInput($request);
        } 
        elseif ($type === 'batch_refinery') {
            // Ambil data untuk Batch Refinery (Mode Full Page)
            $controller = new OilBatchRefineryInputController();
            $data = $controller->prepareDataForInputFull();
        }

        return view('oil.input_station.index', compact('type', 'data'));
    }
}