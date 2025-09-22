<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL; // <-- Pastikan facade URL diimpor

class SalesDashboardController extends Controller
{
    /**
     * Menyiapkan data untuk halaman dashboard utama.
     */
    public function index(Request $request)
    {
        // --------------------------------------------------------------------
        // DATA DINAMIS DARI DATABASE (UNTUK TOKOPEDIA)
        // --------------------------------------------------------------------
        
        // Data untuk ringkasan (hanya yang COMPLETED)
        $completedOrders = Order::where('status', 'COMPLETED');
        $tokopedia_summary = [
            'total_revenue' => $completedOrders->sum('total_amount'),
            'total_orders' => $completedOrders->count(),
            'total_products_sold' => 0, // Memerlukan tabel order_items
            'average_order_value' => $completedOrders->count() > 0 ? $completedOrders->average('total_amount') : 0,
        ];
        
        // Data untuk tabel ringkasan di halaman utama
        $tokopedia_sales_list = Order::latest('created_at_tiktok')->take(5)->get();
        $tokopedia_top_products = [];
        $lastSyncTimestamp = EcommerceSetting::where('key', 'tiktok_last_sync')->value('value');

        // Buat URL AJAX secara eksplisit sebagai HTTPS di controller
        $tokopediaAjaxUrl = secure_url(route('ecommerce.tokopedia.orders.data'));


        // --------------------------------------------------------------------
        // DATA DUMMY UNTUK SHOPEE (LENGKAP)
        // --------------------------------------------------------------------
        
        $shopee_last_sync = null;
            
        $shopee_summary = [
            'total_revenue' => 125850000,
            'total_orders' => 480,
            'total_products_sold' => 912,
            'average_order_value' => 262187,
        ];
        
        $shopee_top_products = [
            ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP1', 'name' => 'Tas Ransel Eiger Pro', 'sold_count' => 152],
            ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP2', 'name' => 'Sepatu Lari Adidas Run', 'sold_count' => 110],
            ['image_url' => 'https://placehold.co/100x100/f97316/white?text=SP3', 'name' => 'Kaos Polos Cotton Combed', 'sold_count' => 98],
        ];
        
        $shopee_sales_list = [
            ['invoice_id' => 'INV/SP/2023/00123', 'customer_name' => 'Andi Wijaya', 'date' => '2023-10-26 14:30', 'total_amount' => 350000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00122', 'customer_name' => 'Bunga Citra', 'date' => '2023-10-26 11:15', 'total_amount' => 185000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00121', 'customer_name' => 'Rian Hidayat', 'date' => '2023-10-25 20:05', 'total_amount' => 75000, 'status' => 'Dibatalkan'],
        ];
        
        $all_shopee_sales = array_merge($shopee_sales_list, [
            ['invoice_id' => 'INV/SP/2023/00120', 'customer_name' => 'Dewi Lestari', 'date' => '2023-10-25 18:45', 'total_amount' => 550000, 'status' => 'Selesai'],
            ['invoice_id' => 'INV/SP/2023/00119', 'customer_name' => 'Siska Amelia', 'date' => '2023-10-25 15:00', 'total_amount' => 210000, 'status' => 'Selesai'],
        ]);

        return view('ecommerce.sales.index', [
            // Data untuk bagian Tokopedia
            'all_tokopedia_sales' => collect(), 
            'tokopedia_last_sync' => $lastSyncTimestamp,
            'tokopedia_summary' => $tokopedia_summary,
            'tokopedia_sales_list' => $tokopedia_sales_list,
            'tokopedia_top_products' => $tokopedia_top_products,
            'tokopedia_ajax_url' => $tokopediaAjaxUrl, // Kirim URL yang sudah aman ke view
            
            // Data untuk bagian Shopee
            'all_shopee_sales' => $all_shopee_sales,
            'shopee_last_sync' => $shopee_last_sync,
            'shopee_summary' => $shopee_summary,
            'shopee_sales_list' => $shopee_sales_list,
            'shopee_top_products' => $shopee_top_products,
        ]);
    }

    /**
     * Menyediakan data pesanan yang dipaginasi sebagai HTML untuk AJAX.
     */
    public function getPaginatedOrders(Request $request)
    {
        try {
            $query = Order::query();
            $searchTerm = $request->input('search', '');

            if ($searchTerm != '') {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('tiktok_order_id', 'like', '%' . $searchTerm . '%')
                      ->orWhere('recipient_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('status', 'like', '%' . $searchTerm . '%');
                });
            }

            $paginatedOrders = $query->latest('created_at_tiktok')->paginate(10)->withQueryString();

            $tableHtml = view('ecommerce.sales.partials.tokopedia-sales-table', ['all_tokopedia_sales' => $paginatedOrders])->render();

            return response()->json(['html' => $tableHtml]);

        } catch (\Exception $e) {
            Log::error('AJAX_MODAL: Terjadi error saat memproses permintaan.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['error' => 'Terjadi kesalahan pada server.'], 500);
        }
    }
}