<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Poem;
use App\Models\Order;
use Illuminate\Http\Request;
use Polar\Polar;
use Polar\SDKConfiguration;
use Polar\Models\Components\CheckoutCreate;
use GuzzleHttp\Client;
use App\Mail\PurchaseConfirmation;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function testPolar()
    {
        try {
            $polar = Polar::builder()
                ->setClient(new Client([
                    'timeout' => 30,
                    'debug' => false,
                ]))
                ->setServerUrl('https://sandbox-api.polar.sh')
                ->setSecurity(env('POLAR_ACCESS_TOKEN'))
                ->build();

            // Test simple API call - get organizations
            $organizations = $polar->organizations->list();
            
            return response()->json([
                'success' => true,
                'message' => 'Polar API connection successful',
                'organizations_available' => true,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Polar API test failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function create(Request $request, $type, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

        $model = ($type === 'book') ? Book::findOrFail($id) : Poem::findOrFail($id);

        try {
            $checkoutUrl = $this->createPolarCheckout($model, $type, $request);

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);
        } catch (\Exception $e) {
            \Log::error('Polar checkout creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'type' => $type,
                'id' => $id,
                'email' => $request->email,
                'model_data' => [
                    'polar_product_id' => $model->polar_product_id ?? 'null',
                    'title' => $model->title ?? 'null',
                    'id' => $model->id ?? 'null'
                ],
                'polar_access_token_set' => !empty(env('POLAR_ACCESS_TOKEN')),
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

    private function createPolarCheckout($model, $type, $request)
    {
        \Log::info('Creating Polar checkout', [
            'model_id' => $model->id,
            'polar_product_id' => $model->polar_product_id,
            'type' => $type,
            'customer_email' => $request->email,
        ]);

        try {
            $polar = Polar::builder()
                ->setClient(new Client([
                    'timeout' => 30,
                    'debug' => false, // Guzzle debug'ını kapatıyoruz
                ]))
                ->setServerUrl('https://sandbox-api.polar.sh')
                ->setSecurity(env('POLAR_ACCESS_TOKEN'))
                ->build();

            // CheckoutCreate objesi ile Polar checkout oluşturuyoruz
            $checkoutCreate = new CheckoutCreate(
                products: [$model->polar_product_id],
                customerEmail: $request->email,
                customerName: $request->name,
                successUrl: url("/tr/success?type={$type}&id={$model->id}&email=" . urlencode($request->email)),
                metadata: [
                    'purchasable_type' => $type,
                    'purchasable_id' => (string)$model->id,
                    'title' => $model->title,
                    'cancel_url' => url("/checkout/cancel?type={$type}&id={$model->id}"),
                ]
            );

            \Log::info('CheckoutCreate object created', [
                'products' => $checkoutCreate->products,
                'customerEmail' => $checkoutCreate->customerEmail,
                'successUrl' => $checkoutCreate->successUrl,
            ]);

            $checkoutResponse = $polar->checkouts->create($checkoutCreate);

            \Log::info('Polar checkout created successfully', [
                'checkout_url' => $checkoutResponse->checkout->url ?? 'No URL returned',
                'status_code' => $checkoutResponse->statusCode,
            ]);

            return $checkoutResponse->checkout->url;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Guzzle Request Exception', [
                'message' => $e->getMessage(),
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body',
                'status_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'No status code',
            ]);
            throw new \Exception('HTTP Request failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('General exception in createPolarCheckout', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function processSuccess(Request $request)
    {
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

            // Check if order already exists for this customer_session_token
            $existingOrder = null;
            if ($customerSessionToken) {
                $existingOrder = Order::where('transaction_id', $customerSessionToken)
                    ->where('email', $email)
                    ->where('purchasable_type', $type)
                    ->where('purchasable_id', $id)
                    ->first();
            }

            if (!$existingOrder) {
                // Create new order
                $order = Order::create([
                    'email' => $email,
                    'purchasable_type' => $type,
                    'purchasable_id' => $id,
                    'transaction_id' => $customerSessionToken,
                    'status' => 'paid',
                    'amount' => $model->price,
                ]);

                // Send confirmation email
                Mail::to($email)->send(new PurchaseConfirmation($order));

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
                    Mail::to($email)->send(new PurchaseConfirmation($existingOrder));
                    
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

    public function cancel(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');

        return view('checkout.cancel', compact('type', 'id'));
    }
}
