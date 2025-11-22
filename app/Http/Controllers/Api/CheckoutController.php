<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Poem;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Mail\PurchaseConfirmation;
use Illuminate\Support\Facades\Mail;
use LemonSqueezy\Laravel\Checkout;

class CheckoutController extends Controller
{

    public function create(Request $request, $type, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

        $model = ($type === 'book') ? Book::findOrFail($id) : Poem::findOrFail($id);

        try {
            $checkoutUrl = $this->createLemonSqueezyCheckout($model, $type, $request);

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);
        } catch (\Exception $e) {
            \Log::error('Lemon Squeezy checkout creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'type' => $type,
                'id' => $id,
                'email' => $request->email,
                'model_data' => [
                    'lemon_variant_id' => $model->lemon_variant_id ?? 'null',
                    'title' => $model->title ?? 'null',
                    'id' => $model->id ?? 'null'
                ],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Checkout creation failed: ' . $e->getMessage(),
                'error_details' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function processSuccess(Request $request)
    {
        \Log::info('=== PROCESS SUCCESS REQUEST RECEIVED ===', [
            'request_data' => $request->all(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        $request->validate([
            'type' => 'required|string|in:book,poem',
            'id' => 'required|integer',
            'email' => 'required|email',
            'customer_session_token' => 'nullable|string',
        ]);

        $type = $request->input('type');
        $id = $request->input('id');
        $email = $request->input('email');
        $customerSessionToken = $request->input('customer_session_token');

        try {
            $model = ($type === 'book') ? Book::find($id) : Poem::find($id);

            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if order already exists
            $existingOrder = null;

            if ($customerSessionToken) {
                // If we have customer_session_token, check by that
                $existingOrder = Order::where('transaction_id', $customerSessionToken)
                    ->where('email', $email)
                    ->where('purchasable_type', $type)
                    ->where('purchasable_id', $id)
                    ->first();
            } else {
                // If no customer_session_token, check if user already purchased this item recently (last 5 minutes)
                $existingOrder = Order::where('email', $email)
                    ->where('purchasable_type', $type)
                    ->where('purchasable_id', $id)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->first();
            }

            if (!$existingOrder) {
                // Generate transaction_id if not provided
                $transactionId = $customerSessionToken ?? 'manual_' . uniqid() . '_' . time();

                // Create new order
                $order = Order::create([
                    'email' => $email,
                    'purchasable_type' => $type,
                    'purchasable_id' => $id,
                    'transaction_id' => $transactionId,
                    'status' => 'paid',
                    'amount' => $model->price,
                ]);

                \Log::info('Order created via processSuccess', [
                    'order_id' => $order->id,
                    'email' => $email,
                    'transaction_id' => $transactionId,
                    'has_customer_session_token' => !empty($customerSessionToken),
                ]);

                // Send confirmation email (without Lemon Squeezy download URLs for manual orders)
                Mail::to($email)->send(new PurchaseConfirmation($order, []));

                // Mark email as sent
                $order->update([
                    'email_sent' => true,
                    'email_sent_at' => now(),
                ]);

                \Log::info('Purchase confirmation email sent', [
                    'order_id' => $order->id,
                    'email' => $email,
                    'product' => $model->title,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order created and email sent successfully',
                    'order_id' => $order->id,
                ]);
            } else {
                // Order already exists
                if (!$existingOrder->email_sent) {
                    // Resend email if not sent before
                    Mail::to($email)->send(new PurchaseConfirmation($existingOrder, []));
                    
                    $existingOrder->update([
                        'email_sent' => true,
                        'email_sent_at' => now(),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Email sent successfully',
                        'order_id' => $existingOrder->id,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Order already processed',
                    'order_id' => $existingOrder->id,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error processing purchase success', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id,
                'email' => $email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createLemonSqueezyCheckout($model, $type, $request)
    {
        \Log::info('Creating Lemon Squeezy checkout', [
            'model_id' => $model->id,
            'lemon_variant_id' => $model->lemon_variant_id,
            'type' => $type,
            'customer_email' => $request->email,
        ]);

        if (empty($model->lemon_variant_id)) {
            throw new \Exception('Lemon Squeezy variant ID not configured for this product');
        }

        try {
            $storeId = config('lemon-squeezy.store');

            \Log::info('Lemon Squeezy checkout data before creation', [
                'email' => $request->email,
                'name' => $request->name,
                'has_name' => !empty($request->name),
                'store_id' => $storeId,
            ]);

            // Create checkout with prefilled data
            $checkoutBuilder = Checkout::make($storeId, $model->lemon_variant_id);

            // Prefill customer data - these should appear in the checkout form
            $email = trim($request->email);
            $name = !empty($request->name) ? trim($request->name) : null;

            \Log::info('Setting prefill data', [
                'email_to_prefill' => $email,
                'name_to_prefill' => $name,
            ]);

            $checkoutBuilder->withEmail($email);

            if ($name) {
                $checkoutBuilder->withName($name);
            }

            // Add custom data for webhook
            $checkoutBuilder->withCustomData([
                'purchasable_type' => $type,
                'purchasable_id' => (string)$model->id,
                'title' => $model->title,
            ]);

            // Set redirect URL - use frontend URL
            $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
            $redirectUrl = $frontendUrl . "/tr/success?type={$type}&id={$model->id}&email=" . urlencode($email);
            $checkoutBuilder->redirectTo($redirectUrl);

            \Log::info('About to call Lemon Squeezy API', [
                'redirect_url' => $redirectUrl,
            ]);

            // Debug: Log the internal state of checkout builder
            try {
                $reflection = new \ReflectionClass($checkoutBuilder);
                $checkoutDataProperty = $reflection->getProperty('checkoutData');
                $checkoutDataProperty->setAccessible(true);
                $customProperty = $reflection->getProperty('custom');
                $customProperty->setAccessible(true);

                \Log::info('Checkout Builder Internal State', [
                    'checkout_data' => $checkoutDataProperty->getValue($checkoutBuilder),
                    'custom_data' => $customProperty->getValue($checkoutBuilder),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Could not inspect checkout builder', ['error' => $e->getMessage()]);
            }

            // Get checkout URL - this makes the API call
            $checkoutUrl = $checkoutBuilder->url();

            \Log::info('Lemon Squeezy checkout created successfully', [
                'checkout_url' => $checkoutUrl,
            ]);

            return $checkoutUrl;

        } catch (\Exception $e) {
            \Log::error('Lemon Squeezy checkout creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Lemon Squeezy checkout failed: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        return view('checkout.cancel', compact('type', 'id'));
    }
}
