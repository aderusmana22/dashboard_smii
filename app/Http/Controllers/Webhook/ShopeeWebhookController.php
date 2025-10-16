<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessShopeeWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopeeWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        ProcessShopeeWebhookJob::dispatch($request->all());
        return response()->json(['message' => 'Shopee webhook received and queued.'], 200);
    }
}