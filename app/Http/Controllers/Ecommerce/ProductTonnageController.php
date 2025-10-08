<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\MasterProduct;
use App\Models\ProductTonnage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductTonnageController extends Controller
{
    /**
     * Menampilkan halaman untuk mengisi data tonase produk.
     */
    public function index(): View
    {
        // Ambil semua produk master, urutkan berdasarkan nama, dan ambil relasi tonase-nya
        $products = MasterProduct::with('tonnage')
            ->orderBy('title', 'asc')
            ->get();

        return view('ecommerce.tonnage', compact('products'));
    }

    /**
     * Menyimpan atau memperbarui data tonase dari form.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi bahwa input adalah array
        $request->validate([
            'tonnages' => 'required|array',
            'tonnages.*' => 'nullable|numeric|min:0', // Setiap item dalam array harus numerik
        ]);

        foreach ($request->tonnages as $productId => $tonnageValue) {
            // Gunakan updateOrCreate untuk efisiensi:
            // - Jika sudah ada, perbarui nilainya.
            // - Jika belum ada, buat entri baru.
            ProductTonnage::updateOrCreate(
                ['master_product_id' => $productId],
                ['tonnage' => $tonnageValue ?? 0] // Jika null, set ke 0
            );
        }

        return redirect()->route('ecommerce.products.tonnage.index')
                         ->with('success', 'Data tonase produk berhasil disimpan.');
    }
}