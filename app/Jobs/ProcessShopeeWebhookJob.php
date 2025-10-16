<?php

namespace App\Jobs;

use App\Models\ShopeeProduct;
use App\Services\Shopee\ShopeeGetOrderDetailService;
use App\Services\TiktokShop\TiktokUpdateInventoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessShopeeWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(
        ShopeeGetOrderDetailService $orderService,
        TiktokUpdateInventoryService $tiktokUpdateInventoryService
    ): void {
        Log::info('Processing Shopee Webhook Job:', $this->payload);
        if (($this->payload['code'] ?? null) !== 3)
            return;

        $data = $this->payload['data'] ?? [];
        $orderSn = $data['ordersn'];
        $status = $data['status'];

        $orderDetails = $orderService->getOrderDetail($orderSn);
        if (!$orderDetails || empty($orderDetails['item_list'])) {
            Log::error("Webhook Shopee: Gagal mendapatkan detail pesanan: {$orderSn}");
            return;
        }

        foreach ($orderDetails['item_list'] as $item) {
            $shopeeItemId = $item['item_id'];
            $quantity = $item['model_quantity_purchased'];

            DB::transaction(function () use ($shopeeItemId, $quantity, $status, $tiktokUpdateInventoryService) {
                $shopeeProduct = ShopeeProduct::where('shopee_item_id', $shopeeItemId)->first();
                if (!$shopeeProduct)
                    return;

                $masterProduct = $shopeeProduct->master_product()->lockForUpdate()->first();
                if (!$masterProduct)
                    return;

                if ($status === 'PROCESSED' || $status === 'READY_TO_SHIP') {
                    $newStock = $masterProduct->total_stock - $quantity;
                } elseif ($status === 'CANCELLED') {
                    $newStock = $masterProduct->total_stock + $quantity;
                } else {
                    return;
                }

                $masterProduct->update(['total_stock' => $newStock]);
                Log::info("Master stock for '{$masterProduct->title}' updated to {$newStock} via Shopee webhook.");

                if ($masterProduct->tiktok_product) {
                    $tiktokUpdateInventoryService->updateInventory($masterProduct->tiktok_product, $newStock);
                }
            });
        }
    }
}