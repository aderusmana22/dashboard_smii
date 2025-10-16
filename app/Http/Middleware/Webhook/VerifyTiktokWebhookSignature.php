<?php

namespace App\Http\Middleware\Webhook;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyTiktokWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('Authorization');
        $appSecret = config('services.tiktok.secret');

        if (!$signature || !$appSecret) {
            Log::warning('TikTok Webhook: Signature or App Secret is missing in config.');
            abort(401, 'Unauthorized: Configuration missing.');
        }

        $calculatedSign = hash_hmac('sha256', $request->getContent(), $appSecret);

        if (!hash_equals($calculatedSign, $signature)) {
            Log::error('TikTok Webhook: Invalid signature.', ['received' => $signature]);
            abort(403, 'Invalid signature.');
        }

        return $next($request);
    }
}