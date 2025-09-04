<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardEcommerceController extends Controller
{
    /**
     * Menampilkan halaman dashboard e-commerce.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // PERUBAHAN DI SINI:
        // Mengarahkan ke file 'resources/views/ecommerce/index.blade.php'
        return view('ecommerce.index');
    }
}