<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::query();

        // Language filter
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $books = $query->orderBy('created_at', 'desc')
                      ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $books->items(),
            'pagination' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $book = Book::where('slug', $slug)->first();

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $book
        ]);
    }

    /**
     * Get featured books
     */
    public function featured()
    {
        $books = Book::orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }
}
