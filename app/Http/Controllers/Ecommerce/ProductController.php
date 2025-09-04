<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Menampilkan halaman daftar produk.
     */
    public function index()
    {
        // Nantinya, Anda akan mengambil data produk dari database di sini.
        // Untuk sekarang, kita hanya menampilkan view-nya.
        return view('ecommerce.product');
    }
}