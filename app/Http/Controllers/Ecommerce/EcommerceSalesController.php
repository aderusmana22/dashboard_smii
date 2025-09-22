<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EcommerceSalesController extends Controller
{
    /**
     * Menampilkan halaman data penjualan dengan data sample.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // --- DATA SAMPLE ---
        // Anda bisa mengganti bagian ini dengan query ke database Anda.

        // 1. Data Ringkasan Total
        $summary = [
            'total_revenue' => 20950000,
            'total_orders' => 185,
            'total_products_sold' => 450,
            'average_order_value' => 20950000 / 185,
        ];

        // 2. Data Daftar Penjualan (Transaksi Terakhir)
        $sales_list = [
            [
                'invoice_id' => 'INV-20230915-001',
                'customer_name' => 'Budi Susanto',
                'date' => Carbon::now()->subDays(1)->toDateTimeString(),
                'total_amount' => 350000,
                'status' => 'Selesai',
                'platform' => 'Shopee'
            ],
            [
                'invoice_id' => 'INV-20230915-002',
                'customer_name' => 'Eko Prasetyo',
                'date' => Carbon::now()->subDays(1)->addHours(2)->toDateTimeString(),
                'total_amount' => 150000,
                'status' => 'Selesai',
                'platform' => 'Tokopedia'
            ],
            [
                'invoice_id' => 'INV-20230914-001',
                'customer_name' => 'Citra Lestari',
                'date' => Carbon::now()->subDays(2)->toDateTimeString(),
                'total_amount' => 75000,
                'status' => 'Selesai',
                'platform' => 'Shopee'
            ],
            [
                'invoice_id' => 'INV-20230913-005',
                'customer_name' => 'Dewi Ayu',
                'date' => Carbon::now()->subDays(3)->toDateTimeString(),
                'total_amount' => 225000,
                'status' => 'Dibatalkan',
                'platform' => 'Website'
            ],
            [
                'invoice_id' => 'INV-20230913-004',
                'customer_name' => 'Gita Sari',
                'date' => Carbon::now()->subDays(3)->addHours(3)->toDateTimeString(),
                'total_amount' => 180000,
                'status' => 'Selesai',
                'platform' => 'Tokopedia'
            ],
        ];

        // 3. Data Produk Terlaris
        $top_products = [
            ['name' => 'Kacamata', 'image_url' => 'https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg', 'sold_count' => 120],
            ['name' => 'Kaos Putih', 'image_url' => 'https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg', 'sold_count' => 95],
            ['name' => 'Kaos Hitam', 'image_url' => 'https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg', 'sold_count' => 88],
            ['name' => 'Celana Jeans Biru', 'image_url' => 'https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg', 'sold_count' => 75],
            ['name' => 'Baju Kemeja Polos', 'image_url' => 'https://cdn.shopify.com/s/files/1/0601/5415/1102/files/casablanca_1000x.jpg', 'sold_count' => 72],
        ];
        
        // Mengirim semua data ke view
        return view('ecommerce.sales.index', compact('summary', 'sales_list', 'top_products'));
    }
}