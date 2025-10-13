<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\MasterProduct;
use App\Models\EcommerceSetting;
use App\Services\MasterProductService;
use App\Services\TiktokShop\TiktokProductSyncService;
use App\Services\TiktokShop\TiktokUpdateInventoryService;
use App\Services\TiktokShop\TiktokUpdatePriceService; // <-- TAMBAHKAN INI
use App\Services\Shopee\ShopeeProductSyncService;
use App\Services\Shopee\ShopeeUpdateInventoryService;
use App\Services\Shopee\ShopeeUpdatePriceService; // <-- TAMBAHKAN INI
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(
        protected TiktokProductSyncService $tiktokSyncService,
        protected ShopeeProductSyncService $shopeeSyncService,
        protected MasterProductService $masterProductService,
        protected TiktokUpdateInventoryService $tiktokUpdateInventoryService,
        protected ShopeeUpdateInventoryService $shopeeUpdateInventoryService,
        protected TiktokUpdatePriceService $tiktokUpdatePriceService, // <-- TAMBAHKAN INI
        protected ShopeeUpdatePriceService $shopeeUpdatePriceService  // <-- TAMBAHKAN INI
    ) {
    }

    // ... (fungsi index, syncTiktok, syncShopee tetap sama) ...
    public function index(): View
    {
        $products = MasterProduct::with(['tiktok_product', 'shopee_product'])
            ->orderByRaw(
                "CASE
                    WHEN
                        EXISTS (
                            SELECT 1 FROM tiktok_products
                            WHERE tiktok_products.id = master_products.tiktok_product_id
                            AND tiktok_products.status = 'ACTIVATE'
                        )
                    OR
                        EXISTS (
                            SELECT 1 FROM shopee_products
                            WHERE shopee_products.id = master_products.shopee_product_id
                            AND shopee_products.item_status = 'NORMAL'
                        )
                    THEN 1
                    ELSE 0
                END DESC"
            )
            ->latest('updated_at') // Urutan sekunder: yang terbaru di atas
            ->paginate(15);

        $lastSyncTiktok = EcommerceSetting::where('key', 'tiktok_products_last_sync')->value('value');
        $lastSyncShopee = EcommerceSetting::where('key', 'shopee_products_last_sync')->value('value');

        return view('ecommerce.product', compact('products', 'lastSyncTiktok', 'lastSyncShopee'));
    }

    public function syncTiktok(): RedirectResponse
    {
        try {
            $this->tiktokSyncService->syncProductsFromApi();
            $this->masterProductService->syncMasterTable();
            EcommerceSetting::updateOrCreate(['key' => 'tiktok_products_last_sync'], ['value' => now()]);
            return redirect()->route('ecommerce.products.index')->with('success', 'Sinkronisasi produk TikTok berhasil.');
        } catch (\Exception $e) {
            Log::error('Error sync TikTok: ' . $e->getMessage());
            return redirect()->route('ecommerce.products.index')->with('error', 'Gagal sinkronisasi TikTok: ' . $e->getMessage());
        }
    }

    public function syncShopee(): RedirectResponse
    {
        try {
            $this->shopeeSyncService->syncProductsFromApi();
            $this->masterProductService->syncMasterTable();
            EcommerceSetting::updateOrCreate(['key' => 'shopee_products_last_sync'], ['value' => now()]);
            return redirect()->route('ecommerce.products.index')->with('success', 'Sinkronisasi produk Shopee berhasil.');
        } catch (\Exception $e) {
            Log::error('Error sync Shopee: ' . $e->getMessage());
            return redirect()->route('ecommerce.products.index')->with('error', 'Gagal sinkronisasi Shopee: ' . $e->getMessage());
        }
    }


    public function updateStock(Request $request, MasterProduct $product): RedirectResponse
    {
        $validated = $request->validate(['stock' => 'required|integer|min:0']);
        $newMasterStock = $validated['stock'];
        $errors = [];

        $product->load(['tiktok_product', 'shopee_product']);

        // 1. Update di TikTok jika terhubung
        if ($product->tiktok_product) {
            try {
                $this->tiktokUpdateInventoryService->updateInventory($product->tiktok_product, $newMasterStock);
                $product->tiktok_product->update(['total_stock' => $newMasterStock]);
            } catch (\Exception $e) {
                Log::error("Gagal update stok TikTok untuk master product ID {$product->id}: " . $e->getMessage());
                $errors[] = 'Gagal update stok TikTok: ' . $e->getMessage();
            }
        }

        // 2. Update di Shopee jika terhubung
        if ($product->shopee_product) {
            try {
                $this->shopeeUpdateInventoryService->updateInventory($product->shopee_product, $newMasterStock);
                $product->shopee_product->update(['total_stock' => $newMasterStock]);
            } catch (\Exception $e) {
                Log::error("Gagal update stok Shopee untuk master product ID {$product->id}: " . $e->getMessage());
                $errors[] = 'Gagal update stok Shopee: ' . $e->getMessage();
            }
        }

        // 3. Jika tidak ada error, update stok di tabel master
        if (empty($errors)) {
            $product->update(['total_stock' => $newMasterStock]);
            return redirect()->route('ecommerce.products.index')->with('success', "Stok untuk '{$product->title}' berhasil diperbarui menjadi {$newMasterStock} di semua platform.");
        } else {
            return redirect()->route('ecommerce.products.index')->with('error', implode('; ', $errors));
        }
    }

    public function updatePrice(Request $request, MasterProduct $product): RedirectResponse
    {
        // 1. Validasi input yang masuk dari form modal
        $validated = $request->validate([
            'price' => 'sometimes|required|numeric|min:0',
            'tokopedia_price' => 'sometimes|required|numeric|min:0',
            'shopee_price' => 'sometimes|required|numeric|min:0',
        ]);

        // 2. Tentukan harga dan platform mana yang akan diupdate berdasarkan input
        $newPrice = null;
        $platformsToUpdate = [];

        if ($request->has('tokopedia_price')) {
            $newPrice = (float) $validated['tokopedia_price'];
            $platformsToUpdate[] = 'tiktok';
        } elseif ($request->has('shopee_price')) {
            $newPrice = (float) $validated['shopee_price'];
            $platformsToUpdate[] = 'shopee';
        } elseif ($request->has('price')) {
            // Jika input 'price' ada, artinya update keduanya (jika terhubung)
            $newPrice = (float) $validated['price'];
            $platformsToUpdate = ['tiktok', 'shopee'];
        } else {
            // Seharusnya tidak terjadi jika form di-submit dengan benar
            return back()->with('error', 'Input harga tidak ditemukan.');
        }

        // 3. Muat relasi produk untuk efisiensi
        $product->load(['tiktok_product', 'shopee_product']);
        $errors = [];
        $successes = [];

        // 4. Proses update ke setiap platform yang telah ditentukan
        foreach ($platformsToUpdate as $platform) {
            if ($platform === 'tiktok' && $product->tiktok_product) {
                try {
                    $this->tiktokUpdatePriceService->updatePrice($product->tiktok_product, (string) $newPrice);
                    $successes[] = 'Tokopedia';
                } catch (\Exception $e) {
                    Log::error("Gagal update harga Tokopedia untuk master product ID {$product->id}: " . $e->getMessage());
                    $errors[] = 'Gagal update harga Tokopedia: ' . $e->getMessage();
                }
            }

            if ($platform === 'shopee' && $product->shopee_product) {
                try {
                    $this->shopeeUpdatePriceService->updatePrice($product->shopee_product, $newPrice);
                    $successes[] = 'Shopee';
                } catch (\Exception $e) {
                    Log::error("Gagal update harga Shopee untuk master product ID {$product->id}: " . $e->getMessage());
                    $errors[] = 'Gagal update harga Shopee: ' . $e->getMessage();
                }
            }
        }

        // 5. Buat pesan feedback berdasarkan hasil proses
        $feedbackMessage = '';
        if (!empty($successes)) {
            $formattedPrice = 'Rp ' . number_format($newPrice, 0, ',', '.');
            $platformNames = implode(' & ', $successes);
            $feedbackMessage .= "Harga untuk '{$product->title}' di platform {$platformNames} berhasil diupdate menjadi {$formattedPrice}.";
        }

        if (!empty($errors)) {
            // Jika ada sukses dan juga error
            if (!empty($successes)) {
                $feedbackMessage .= ' Namun terjadi error: ' . implode('; ', $errors);
                return redirect()->route('ecommerce.products.index')->with('error', $feedbackMessage);
            }
            // Jika hanya error
            return redirect()->route('ecommerce.products.index')->with('error', implode('; ', $errors));
        }

        if (empty($successes)) {
            return redirect()->route('ecommerce.products.index')->with('error', 'Produk tidak terhubung ke platform yang dipilih untuk diupdate.');
        }

        return redirect()->route('ecommerce.products.index')->with('success', $feedbackMessage);
    }
}