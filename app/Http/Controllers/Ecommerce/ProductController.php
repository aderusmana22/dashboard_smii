<?php

// app/Http/Controllers/Ecommerce/ProductController.php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Services\TiktokShop\TiktokProductService;
use App\Services\TiktokShop\TiktokUpdateInventoryService; // <-- 1. IMPORT SERVICE BARU
use App\Exceptions\TiktokApiException;
use App\Models\EcommerceSetting;
use App\Models\TiktokProduct;
use Illuminate\Http\Request; // <-- 2. IMPORT REQUEST
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    protected TiktokProductService $tiktokProductService;
    protected TiktokUpdateInventoryService $tiktokUpdateInventoryService; // <-- 3. TAMBAHKAN PROPERTI

    public function __construct(
        TiktokProductService $tiktokProductService,
        TiktokUpdateInventoryService $tiktokUpdateInventoryService // <-- 4. INJECT DI CONSTRUCTOR
    ) {
        $this->tiktokProductService = $tiktokProductService;
        $this->tiktokUpdateInventoryService = $tiktokUpdateInventoryService; // <-- 5. ASSIGN
    }

    public function index(): View
    {
        // ... (metode index tidak berubah)
        $orderClause = "CASE status
                            WHEN 'ACTIVATE' THEN 1
                            WHEN 'SELLER_DEACTIVATED' THEN 2
                            WHEN 'DELETED' THEN 3
                            WHEN 'UNKNOWN' THEN 4
                            ELSE 5
                        END";
        $products = TiktokProduct::orderByRaw($orderClause)
                                   ->latest('updated_at')
                                   ->paginate(15);
        $lastSync = EcommerceSetting::where('key', 'tiktok_products_last_sync')->value('value');
        return view('ecommerce.product', compact('products', 'lastSync'));
    }

    public function sync(): RedirectResponse
    {
        // ... (metode sync tidak berubah)
        try {
            $count = $this->tiktokProductService->syncProductsFromApi();
            return redirect()->route('ecommerce.products.index')
                ->with('success', "Sinkronisasi berhasil. {$count} produk telah diperbarui.");
        } catch (TiktokApiException $e) {
            Log::error('Error di ProductController@sync: ' . $e->getMessage());
            return redirect()->route('ecommerce.products.index')
                ->with('error', 'Gagal sinkronisasi produk dari TikTok: ' . $e->getMessage());
        }
    }

    /**
     * ==================================================================
     * --- 6. TAMBAHKAN METODE BARU UNTUK UPDATE STOK ---
     * ==================================================================
     */
    public function updateStock(Request $request, TiktokProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        try {
            // Panggil service untuk update stok di API TikTok
            $this->tiktokUpdateInventoryService->updateInventory($product, $validated['stock']);

            // Jika berhasil, update juga stok di database lokal
            $product->update(['total_stock' => $validated['stock']]);

            return redirect()->route('ecommerce.products.index')
                ->with('success', "Stok untuk produk '{$product->title}' berhasil diperbarui.");

        } catch (TiktokApiException $e) {
            Log::error("Gagal update stok untuk produk ID {$product->id}: " . $e->getMessage());
            return redirect()->route('ecommerce.products.index')
                ->with('error', "Gagal memperbarui stok: " . $e->getMessage());
        }
    }
}