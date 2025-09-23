<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Poem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Paddle\Checkout;
use Laravel\Paddle\Price;

class CheckoutController extends Controller
{
    public function create(Request $request, $type, $id)
    {
        try {
            // Validate input
            $request->validate([
                'email' => 'required|email',
                'name' => 'nullable|string|max:255',
            ]);

            $model = ($type === 'book') ? Book::findOrFail($id) : Poem::findOrFail($id);
            
            // Create checkout session with Paddle (no user required)
            $checkoutUrl = $this->createPaddleCheckout($model, $type, $request);
            
            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl
            ]);

        } catch (\Exception $e) {
            \Log::error('Checkout creation failed', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id,
                'email' => $request->email ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Checkout creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createPaddleCheckout($model, $type, $request)
    {
        // If we have a Paddle price ID, use it directly
        if ($model->paddle_price_id) {
            $checkout = Checkout::create([
                'items' => [
                    [
                        'price_id' => $model->paddle_price_id,
                        'quantity' => 1,
                    ],
                ],
                'customer_email' => $request->email,
                'custom_data' => [
                    'purchasable_type' => $type,
                    'purchasable_id' => $model->id,
                    'title' => $model->title,
                    'customer_email' => $request->email,
                    'customer_name' => $request->name,
                ],
                'return_url' => url("/checkout/success?type={$type}&id={$model->id}&email=" . urlencode($request->email)),
                'discount_url' => url("/checkout/cancel?type={$type}&id={$model->id}"),
            ]);

            return $checkout->url;
        }

        // Fallback: Create dynamic price and checkout
        $price = Price::create([
            'description' => $model->title,
            'name' => ($type === 'book' ? 'Kitap: ' : 'Şiir: ') . $model->title,
            'unit_price' => [
                'amount' => strval(intval($model->price * 100)), // Convert to cents as string
                'currency_code' => 'TRY',
            ],
            'type' => 'standard', // One-time payment
        ]);

        $checkout = Checkout::create([
            'items' => [
                [
                    'price_id' => $price->id,
                    'quantity' => 1,
                ],
            ],
            'customer_email' => $request->email,
            'custom_data' => [
                'purchasable_type' => $type,
                'purchasable_id' => $model->id,
                'title' => $model->title,
                'customer_email' => $request->email,
                'customer_name' => $request->name,
                'dynamic_price' => true,
            ],
            'return_url' => url("/checkout/success?type={$type}&id={$model->id}&email=" . urlencode($request->email)),
            'discount_url' => url("/checkout/cancel?type={$type}&id={$model->id}"),
        ]);

        return $checkout->url;
    }

    public function success(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');
        $email = $request->query('email');
        $transactionId = $request->query('_ptxn');

        // Create order record (without user)
        if ($transactionId && $type && $id && $email) {
            $model = ($type === 'book') ? Book::find($id) : Poem::find($id);
            
            if ($model) {
                Order::create([
                    'email' => $email,
                    'purchasable_type' => $type,
                    'purchasable_id' => $id,
                    'transaction_id' => $transactionId,
                    'status' => 'completed',
                    'amount' => $model->price,
                ]);
            }
        }

        return view('checkout.success', compact('type', 'id', 'email', 'transactionId'));
    }

    public function cancel(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        return view('checkout.cancel', compact('type', 'id'));
    }

    // Legacy test method - keep for development
    public function createTest(Request $request, $type, $id)
    {
        try {
            $model = ($type === 'book') ? Book::findOrFail($id) : Poem::findOrFail($id);

            // Simple test checkout URL for development
            $checkoutUrl = $this->generateTestCheckoutUrl($model, $type, $id, $request->email ?? null);
            
            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateTestCheckoutUrl($model, $type, $id, $email)
    {
        // Simple test checkout URL for development
        $params = [
            'type' => $type,
            'id' => $id,
            'title' => $model->title,
            'price' => $model->price,
            'email' => $email,
            'paddle_price_id' => $model->paddle_price_id
        ];

        return url('/checkout/test?' . http_build_query($params));
    }
}