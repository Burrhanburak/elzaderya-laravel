<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PurchaseConfirmation;
use App\Models\Book;
use App\Models\Order;
use App\Models\Poem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LemonSqueezyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('=== CUSTOM WEBHOOK RECEIVED ===', [
            'payload' => $request->all(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            $payload = $request->all();

            // Check event type
            if (!isset($payload['meta']['event_name']) || $payload['meta']['event_name'] !== 'order_created') {
                Log::info('Webhook event ignored', ['event' => $payload['meta']['event_name'] ?? 'unknown']);
                return response()->json(['status' => 'success', 'message' => 'Event ignored'], 200);
            }

            $data = $payload['data'];
            $attributes = $data['attributes'];
            $customData = $attributes['meta']['custom_data'] ?? [];

            Log::info('Processing order_created webhook', [
                'custom_data' => $customData,
                'full_attributes' => $attributes
            ]);

            $purchasableType = $customData['purchasable_type'] ?? null;
            $purchasableId = $customData['purchasable_id'] ?? null;
            $email = $attributes['user_email'] ?? null;

            if (!$purchasableType || !$purchasableId || !$email) {
                Log::error('Missing required data in webhook', [
                    'purchasable_type' => $purchasableType,
                    'purchasable_id' => $purchasableId,
                    'email' => $email,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Missing required data'], 400);
            }

            // Check if order already exists
            $existingOrder = Order::where('transaction_id', $data['id'])
                ->where('email', $email)
                ->first();

            if ($existingOrder) {
                Log::info('Order already exists', ['order_id' => $existingOrder->id]);
                return response()->json(['status' => 'success', 'message' => 'Order already exists'], 200);
            }

            // Get the purchasable model
            $model = ($purchasableType === 'book')
                ? Book::find($purchasableId)
                : Poem::find($purchasableId);

            if (!$model) {
                Log::error('Purchasable item not found', [
                    'type' => $purchasableType,
                    'id' => $purchasableId,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
            }

            // Create order
            $order = Order::create([
                'email' => $email,
                'purchasable_type' => $purchasableType,
                'purchasable_id' => $purchasableId,
                'transaction_id' => $data['id'],
                'status' => 'paid',
                'amount' => $attributes['total'] / 100, // Convert from cents
            ]);

            Log::info('Order created successfully via webhook', [
                'order_id' => $order->id,
                'email' => $email,
            ]);

            // Send confirmation email
            try {
                Mail::to($email)->send(new PurchaseConfirmation($order));

                // Mark email as sent
                $order->update([
                    'email_sent' => true,
                    'email_sent_at' => now(),
                ]);

                Log::info('Purchase confirmation email sent via webhook', [
                    'order_id' => $order->id,
                    'email' => $email,
                    'product' => $model->title,
                ]);
            } catch (\Exception $mailException) {
                Log::error('Failed to send confirmation email via webhook', [
                    'order_id' => $order->id,
                    'error' => $mailException->getMessage(),
                ]);
            }

            return response()->json(['status' => 'success', 'order_id' => $order->id], 200);

        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
