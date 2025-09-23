<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaddleWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        // Log webhook data for debugging
        \Log::info('Paddle webhook received:', [
            'event_type' => $data['event_type'] ?? 'unknown',
            'event_id' => $data['event_id'] ?? 'unknown',
            'data_keys' => isset($data['data']) ? array_keys($data['data']) : [],
        ]);
        
        // Email arama için detaylı log
        if ($data['event_type'] === 'transaction.completed') {
            $transactionData = $data['data'] ?? $data;
            \Log::info('Email Debug - Transaction Data Keys:', array_keys($transactionData));
            \Log::info('Email Debug - Customer Data:', [$transactionData['customer'] ?? 'NO CUSTOMER']);
            \Log::info('Email Debug - Customer ID:', [$transactionData['customer_id'] ?? 'NO CUSTOMER_ID']);
            \Log::info('Email Debug - Billing Details:', [$transactionData['billing_details'] ?? 'NO BILLING']);
            
            // Payments array'inde email var mı?
            if (isset($transactionData['payments']) && !empty($transactionData['payments'])) {
                \Log::info('Email Debug - Payment Details:', $transactionData['payments'][0] ?? 'NO PAYMENT');
            }
        }

        // Customer created/updated event'ini dinle ve email'i kaydet
        if (in_array($data['event_type'], ['customer.created', 'customer.updated'])) {
            $customerData = $data['data'] ?? [];
            if (isset($customerData['id']) && isset($customerData['email'])) {
                // Customer email'ini cache'e kaydet (24 saat)
                \Cache::put('paddle_customer_' . $customerData['id'], $customerData['email'], 86400);
                \Log::info('Customer email cached from ' . $data['event_type'], [
                    'customer_id' => $customerData['id'], 
                    'email' => $customerData['email'],
                    'name' => $customerData['name'] ?? 'unknown'
                ]);
            }
            return response()->json(['success' => true]);
        }

        // Paddle Billing webhook event type kontrolü
        if (!isset($data['event_type']) || $data['event_type'] !== 'transaction.completed') {
            \Log::info('Webhook ignored - not a transaction.completed event', ['event_type' => $data['event_type'] ?? 'unknown']);
            return response()->json(['success' => true]);
        }

        try {
            // Paddle Billing formatında data structure
            $transactionData = $data['data'] ?? $data;
            $customData = $transactionData['custom_data'] ?? [];
            
            // Custom data yoksa, price ID'den ürünü bul
            if (empty($customData) || !isset($customData['purchasable_type'])) {
                $priceId = $transactionData['items'][0]['price_id'] ?? null;
                if (!$priceId) {
                    \Log::error('No price_id found in transaction');
                    return response()->json(['error' => 'No price_id found'], 400);
                }
                
                // Price ID'den ürünü bul
                $book = \App\Models\Book::where('paddle_price_id', $priceId)->first();
                $poem = \App\Models\Poem::where('paddle_price_id', $priceId)->first();
                
                if ($book) {
                    $customData = [
                        'purchasable_type' => 'book',
                        'purchasable_id' => $book->id,
                        'title' => $book->title
                    ];
                } elseif ($poem) {
                    $customData = [
                        'purchasable_type' => 'poem', 
                        'purchasable_id' => $poem->id,
                        'title' => $poem->title
                    ];
                } else {
                    \Log::error('Product not found for price_id:', ['price_id' => $priceId]);
                    return response()->json(['error' => 'Product not found'], 400);
                }
                
                \Log::info('Generated custom_data from price_id:', ['custom_data' => $customData]);
            }

            // Custom data'dan email'i çek
            $customerEmailFromCustomData = $customData['customer_email'] ?? null;

            // Paddle Billing formatında customer ve transaction bilgileri
            $customer = $transactionData['customer'] ?? [];
            $billing = $transactionData['billing_details'] ?? [];
            
            // Email'i farklı yerlerden çekmeye çalış
            $customerEmail = $customerEmailFromCustomData ?? $customer['email'] ?? null;
            
            // Eğer hala email yoksa, payments array'inde ara
            if (!$customerEmail && isset($transactionData['payments']) && !empty($transactionData['payments'])) {
                $payment = $transactionData['payments'][0];
                $customerEmail = $payment['customer_email'] ?? 
                               $payment['billing_details']['email'] ?? 
                               $payment['method_details']['email'] ?? null;
                
                // Cardholder name'den email çıkarmaya çalış
                if (!$customerEmail && isset($payment['method_details']['card']['cardholder_name'])) {
                    $cardholderName = $payment['method_details']['card']['cardholder_name'];
                    // Eğer cardholder name email formatındaysa kullan
                    if (filter_var($cardholderName, FILTER_VALIDATE_EMAIL)) {
                        $customerEmail = $cardholderName;
                        \Log::info('Email extracted from cardholder name', ['email' => $customerEmail]);
                    }
                }
                
                if ($customerEmail) {
                    \Log::info('Email found in payments array', ['email' => $customerEmail]);
                }
            }
            
            // Son çare: customer_id'den cache'den çek
            if (!$customerEmail && isset($transactionData['customer_id'])) {
                $customerEmail = $this->getCustomerEmail($transactionData['customer_id']);
            }
            
            // Fallback
            $customerEmail = $customerEmail ?: 'unknown@example.com';

            $order = Order::create([
                'email' => $customerEmail,
                'purchasable_type' => $customData['purchasable_type'],
                'purchasable_id' => $customData['purchasable_id'],
                'transaction_id' => $transactionData['id'] ?? null,
                'status' => 'paid',
                'amount' => ($transactionData['details']['totals']['total'] ?? 0) / 100, // Paddle Billing uses cents
                'name' => $customer['name'] ?? null,
                'address' => $billing['address']['first_line'] ?? null,
                'country' => $billing['address']['country_code'] ?? null,
                'postal_code' => $billing['address']['postal_code'] ?? null,
            ]);

            // Email gönder
            try {
                \Mail::to($order->email)->send(new \App\Mail\PurchaseConfirmation($order));
                
                // Email gönderildiğini kaydet
                $order->update([
                    'email_sent' => true,
                    'email_sent_at' => now(),
                ]);
                
                \Log::info('Purchase confirmation email sent', ['order_id' => $order->id, 'email' => $order->email]);
            } catch (\Exception $e) {
                \Log::error('Failed to send purchase confirmation email', ['error' => $e->getMessage(), 'order_id' => $order->id]);
                
                // Email gönderilemediyse false olarak kaydet
                $order->update([
                    'email_sent' => false,
                    'email_sent_at' => null,
                ]);
            }

            \Log::info('Order created successfully for Paddle Billing transaction:', [
                'transaction_id' => $transactionData['id'] ?? 'unknown'
            ]);
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            \Log::error('Paddle Billing webhook processing failed:', [
                'error' => $e->getMessage(), 
                'data' => $data
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
    
    private function getCustomerEmail($customerId)
    {
        // Cache'den customer email'ini çek
        $cachedEmail = \Cache::get('paddle_customer_' . $customerId);
        if ($cachedEmail) {
            \Log::info('Customer email found in cache', ['customer_id' => $customerId, 'email' => $cachedEmail]);
            return $cachedEmail;
        }
        
        // Cache'de yoksa Paddle API'den çek
        try {
            $apiKey = 'apikey_01k5phkk6fs472f0rxp3yvd05r'; // Test API key
            $url = "https://sandbox-api.paddle.com/customers/{$customerId}";
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->get($url);
            
            if ($response->successful()) {
                $customerData = $response->json();
                $email = $customerData['data']['email'] ?? null;
                
                if ($email) {
                    // Email'i cache'e kaydet
                    \Cache::put('paddle_customer_' . $customerId, $email, 3600);
                    \Log::info('Customer email fetched from API and cached', ['customer_id' => $customerId, 'email' => $email]);
                    return $email;
                }
            } else {
                \Log::error('Failed to fetch customer from Paddle API', [
                    'customer_id' => $customerId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception while fetching customer from Paddle API', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
        }
        
        \Log::warning('Customer email not found anywhere', ['customer_id' => $customerId]);
        return 'customer@paddle.com'; // Fallback
    }
}
