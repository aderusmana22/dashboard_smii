<?php

use App\Http\Controllers\Webhook\ShopeeWebhookController;
use App\Http\Controllers\Webhook\TiktokWebhookController;
use App\Http\Middleware\Webhook\VerifyShopeeWebhookSignature;
use App\Http\Middleware\Webhook\VerifyTiktokWebhookSignature;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| Rute ini khusus untuk menerima notifikasi dari platform eksternal.
|
*/

Route::prefix('webhooks')->group(function () {
    Route::post('/tiktok', [TiktokWebhookController::class, 'handle'])
         ->middleware(VerifyTiktokWebhookSignature::class);

    Route::post('/shopee', [ShopeeWebhookController::class, 'handle'])
         ->middleware(VerifyShopeeWebhookSignature::class);
});