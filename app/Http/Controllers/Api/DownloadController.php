<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\Poem;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function verifyAccess(Request $request, $type, $id)
    {
        $email = $request->email;
        
        // Order kontrolü
        $order = Order::where('email', $email)
            ->where('purchasable_type', $type)
            ->where('purchasable_id', $id)
            ->where('status', 'paid')
            ->first();

        if (!$order) {
            return response()->json(['has_access' => false], 403);
        }

        // Ürün bilgilerini getir
        $model = ($type === 'book') ? Book::findOrFail($id) : Poem::findOrFail($id);
        
        return response()->json([
            'has_access' => true,
            'download_url' => $model->full_pdf ? 
                (str_starts_with($model->full_pdf, 'http') ? 
                    $model->full_pdf : 
                    "https://bioenerjist-books.s3.amazonaws.com/{$model->full_pdf}") : 
                null
        ]);
    }
}
