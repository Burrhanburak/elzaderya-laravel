<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseConfirmation;
use App\Models\Book;
use App\Models\Order;
use App\Models\Poem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LemonWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('🔥 LEMON WEBHOOK RECEIVED', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        try {
            $payload = $request->all();

            // Check event type
            if (!isset($payload['meta']['event_name']) || $payload['meta']['event_name'] !== 'order_created') {
                Log::info('Webhook event ignored', ['event' => $payload['meta']['event_name'] ?? 'unknown']);
                return response('OK', 200);
            }

            $data = $payload['data'];
            $attributes = $data['attributes'];
            $customData = $payload['meta']['custom_data'] ?? [];

            Log::info('Processing order_created webhook', [
                'custom_data' => $customData,
                'email' => $attributes['user_email'] ?? null,
            ]);

            $purchasableType = $customData['purchasable_type'] ?? null;
            $purchasableId = $customData['purchasable_id'] ?? null;
            $email = $attributes['user_email'] ?? null;

            if (!$purchasableType || !$purchasableId || !$email) {
                Log::error('Missing required data in webhook');
                return response('OK', 200); // Still return 200 to acknowledge receipt
            }

            // Check if order already exists
            $existingOrder = Order::where('transaction_id', $data['id'])
                ->where('email', $email)
                ->first();

            if ($existingOrder) {
                Log::info('Order already exists', ['order_id' => $existingOrder->id]);
                return response('OK', 200);
            }

            // Get the purchasable model
            $model = ($purchasableType === 'book')
                ? Book::find($purchasableId)
                : Poem::find($purchasableId);

            if (!$model) {
                Log::error('Purchasable item not found');
                return response('OK', 200);
            }

            // Get variant ID and fetch files from Lemon Squeezy
            $variantId = $attributes['first_order_item']['variant_id'] ?? null;
            $downloadUrls = [];

            if ($variantId) {
                $downloadUrls = $this->getVariantFiles($variantId);
                Log::info('Variant files fetched', [
                    'variant_id' => $variantId,
                    'files_count' => count($downloadUrls),
                ]);
            }

            // Create order
            $order = Order::create([
                'email' => $email,
                'purchasable_type' => $purchasableType,
                'purchasable_id' => $purchasableId,
                'transaction_id' => $data['id'],
                'status' => 'paid',
                'amount' => $attributes['total'] / 100,
            ]);

            Log::info('✅ Order created via webhook', [
                'order_id' => $order->id,
                'email' => $email,
            ]);

            // Send confirmation email with download URLs
            Mail::to($email)->send(new PurchaseConfirmation($order, $downloadUrls));

            $order->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);

            Log::info('✅ Email sent via webhook', [
                'order_id' => $order->id,
                'download_urls_count' => count($downloadUrls),
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // ALWAYS return 200 OK
        return response('OK', 200);
    }

    /**
     * Get variant files from Lemon Squeezy API
     */
    private function getVariantFiles($variantId)
    {
        try {
            $response = Http::withToken(config('lemon-squeezy.api_key'))
                ->get("https://api.lemonsqueezy.com/v1/variants/{$variantId}/files");

            if ($response->successful()) {
                $files = $response->json('data', []);

                // Extract download URLs and file names
                return collect($files)->map(function ($file) {
                    return [
                        'name' => $file['attributes']['name'] ?? 'Download',
                        'url' => $file['attributes']['download_url'] ?? null,
                        'size' => $file['attributes']['size_formatted'] ?? null,
                    ];
                })->filter(function ($file) {
                    return !empty($file['url']);
                })->toArray();
            }

            Log::error('Failed to fetch variant files', [
                'variant_id' => $variantId,
                'status' => $response->status(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching variant files', [
                'variant_id' => $variantId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
