<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class OilController extends Controller
{
    /**
     * Menampilkan halaman utama monitoring.
     */
    public function index(): View
    {
        return view('oil.index');
    }

    /**
     * Memuat partial view komponen berdasarkan nama via AJAX.
     */
    public function loadComponent(string $componentName)
    {
        // Daftar semua komponen yang valid untuk keamanan
        $validComponents = [
            'tank_yard_bdt',
            'batch_refinery',
            'fat_blend_tank',
            'tank_yard_1t',
            'bleached_oil_tank',
            'packing_room',
            'current_oil_stock',
            'hydrogen_nitrogen_ammonia', // Komponen gabungan untuk data manual
        ];

        if (!in_array($componentName, $validComponents)) {
            abort(404, 'Component not found.');
        }

        $viewPath = 'oil.partials._' . $componentName;

        // Cek jika file view-nya ada
        if (!view()->exists($viewPath)) {
            abort(404, 'View for component not found.');
        }

        // Di aplikasi nyata, Anda akan mengambil data dari database di sini
        // dan meneruskannya ke view.
        // Contoh:
        // $data = \App\Models\TankData::where('area', $componentName)->get();
        // return view($viewPath, ['records' => $data]);
        
        return view($viewPath);
    }
}