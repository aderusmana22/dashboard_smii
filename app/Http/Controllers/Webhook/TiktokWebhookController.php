<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessTiktokWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TiktokWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        ProcessTiktokWebhookJob::dispatch($request->all());
        return response()->json(['message' => 'TikTok webhook received and queued.'], 200);
    }
}