<?php

namespace App\Http\Middleware\Webhook;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopeeWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('Authorization');
        $webhookKey = config('services.shopee.webhook_key');

        if (!$signature || !$webhookKey) {
            abort(401, 'Unauthorized: Configuration missing.');
        }

        $body = $request->getContent();
        $possibleUrls = [
            'fullUrl()' => $request->fullUrl(),
            'url()' => $request->url(),
            'url() with https' => 'https://' . $request->getHttpHost() . $request->getRequestUri(),
            'url() with http' => 'http://' . $request->getHttpHost() . $request->getRequestUri(),
        ];

        $foundMatch = false;
        $debugInfo = [];

        foreach ($possibleUrls as $name => $url) {
            $baseString = $url . '|' . $body;
            $calculatedSign = hash_hmac('sha256', $baseString, $webhookKey);

            $debugInfo[$name] = [
                'url' => $url,
                'base_string' => $baseString,
                'calculated_sign' => $calculatedSign,
            ];

            if (hash_equals($calculatedSign, $signature)) {
                $foundMatch = true;
                Log::info('Shopee Webhook: Signature match found!', ['matched_using' => $name, 'url' => $url]);
                break; // Hentikan perulangan jika sudah cocok
            }
        }

        if ($foundMatch) {
            return $next($request);
        }

        // Jika tidak ada yang cocok, log semua percobaan dan gagalkan request
        Log::error('Shopee Webhook: INVALID SIGNATURE. No URL variation matched.', [
            'signature_received' => $signature,
            'debug_attempts' => $debugInfo,
        ]);

        abort(403, 'Invalid signature.');
    }
}