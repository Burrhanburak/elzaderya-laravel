<?php

namespace App\Listeners;

use App\Mail\PurchaseConfirmation;
use App\Models\Book;
use App\Models\Order;
use App\Models\Poem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use LemonSqueezy\Laravel\Events\OrderCreated;

class HandleLemonSqueezyOrderCreated
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        Log::info('=== WEBHOOK RECEIVED: Lemon Squeezy Order Created Event ===', [
            'payload' => $event->payload,
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            $data = $event->payload['data'];
            $attributes = $data['attributes'];

            // Get custom data
            $checkoutData = $attributes['first_order_item']['product_name'] ?? null;
            $customData = $attributes['meta']['custom_data'] ?? [];

            Log::info('Custom data from webhook', [
                'custom_data' => $customData,
                'checkout_data' => $checkoutData,
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
                    'full_payload' => $event->payload
                ]);
                return;
            }

            // Check if order already exists
            $existingOrder = Order::where('transaction_id', $data['id'])
                ->where('email', $email)
                ->first();

            if ($existingOrder) {
                Log::info('Order already exists', ['order_id' => $existingOrder->id]);
                return;
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
                return;
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

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'email' => $email,
            ]);

            // Send confirmation email (event listener - no Lemon Squeezy files)
            try {
                Mail::to($email)->send(new PurchaseConfirmation($order, []));

                // Mark email as sent
                $order->update([
                    'email_sent' => true,
                    'email_sent_at' => now(),
                ]);

                Log::info('Purchase confirmation email sent', [
                    'order_id' => $order->id,
                    'email' => $email,
                    'product' => $model->title,
                ]);
            } catch (\Exception $mailException) {
                Log::error('Failed to send confirmation email', [
                    'order_id' => $order->id,
                    'error' => $mailException->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error processing order created event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
