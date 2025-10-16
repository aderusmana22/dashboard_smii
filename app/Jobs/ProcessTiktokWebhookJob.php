<?php

namespace App\Jobs;

use App\Models\TiktokProduct;
use App\Services\TiktokShop\TiktokGetOrderDetailService;
use App\Services\Shopee\ShopeeUpdateInventoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessTiktokWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(
        TiktokGetOrderDetailService $orderService,
        ShopeeUpdateInventoryService $shopeeUpdateInventoryService
    ): void {
        Log::info('Processing TikTok Webhook Job:', $this->payload);
        if (($this->payload['type'] ?? null) !== 1)
            return;

        $data = $this->payload['data'] ?? [];
        $orderId = $data['order_id'];
        $status = $data['order_status'];

        $orderDetails = $orderService->getOrderDetail($orderId);
        if (!$orderDetails || empty($orderDetails['line_items'])) {
            Log::error("Webhook TikTok: Gagal mendapatkan detail pesanan: {$orderId}");
            return;
        }

        foreach ($orderDetails['line_items'] as $item) {
            $tiktokProductId = $item['product_id'];
            $quantity = $item['quantity'];

            DB::transaction(function () use ($tiktokProductId, $quantity, $status, $shopeeUpdateInventoryService) {
                $tiktokProduct = TiktokProduct::where('tiktok_product_id', $tiktokProductId)->first();
                if (!$tiktokProduct)
                    return;

                $masterProduct = $tiktokProduct->master_product()->lockForUpdate()->first();
                if (!$masterProduct)
                    return;

                if ($status === 'AWAITING_SHIPMENT') {
                    $newStock = $masterProduct->total_stock - $quantity;
                } elseif ($status === 'CANCELLED') {
                    $newStock = $masterProduct->total_stock + $quantity;
                } else {
                    return;
                }

                $masterProduct->update(['total_stock' => $newStock]);
                Log::info("Master stock for '{$masterProduct->title}' updated to {$newStock} via TikTok webhook.");

                if ($masterProduct->shopee_product) {
                    $shopeeUpdateInventoryService->updateInventory($masterProduct->shopee_product, $newStock);
                }
            });
        }
    }
}