<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poem;
use Illuminate\Http\Request;

class PoemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Poem::query();

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

        // Pagination
        $perPage = $request->get('per_page', 10);
        $poems = $query->orderBy('created_at', 'desc')
                      ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $poems->items(),
            'pagination' => [
                'current_page' => $poems->currentPage(),
                'last_page' => $poems->lastPage(),
                'per_page' => $poems->perPage(),
                'total' => $poems->total(),
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        // Validate slug parameter
        if (empty($slug) || $slug === 'null' || $slug === 'undefined') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid slug parameter'
            ], 400);
        }

        $poem = Poem::where('slug', $slug)->first();

        if (!$poem) {
            return response()->json([
                'success' => false,
                'message' => 'Poem not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $poem
        ]);
    }

    /**
     * Get featured poems
     */
    public function featured()
    {
        $poems = Poem::orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $poems
        ]);
    }
}
