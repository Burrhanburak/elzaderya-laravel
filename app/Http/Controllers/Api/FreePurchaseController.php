<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\Poem;
use Illuminate\Http\Request;

class FreePurchaseController extends Controller
{
    public function create(Request $request, $type, $id)
    {
        $model = ($type === 'book') ? Book::findOrFail($id) : Poem::findOrFail($id);

        // Fiyat 0 olmayan ürünler için hata döndür
        if ($model->price > 0) {
            return response()->json(['message' => 'Bu ürün ücretlidir'], 400);
        }

        // Ücretsiz ürün için order oluştur
        Order::create([
            'email' => $request->email,
            'purchasable_type' => $type,
            'purchasable_id' => $id,
            'transaction_id' => 'FREE_' . time() . '_' . $id,
            'status' => 'paid',
            'amount' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ücretsiz satın alma işlemi tamamlandı'
        ]);
    }
}
