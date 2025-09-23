<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Award;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Award::query();

        // Language filter
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $awards = $query->orderBy('created_at', 'desc')
                       ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $awards->items(),
            'pagination' => [
                'current_page' => $awards->currentPage(),
                'last_page' => $awards->lastPage(),
                'per_page' => $awards->perPage(),
                'total' => $awards->total(),
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $award = Award::where('slug', $slug)->first();

        if (!$award) {
            return response()->json([
                'success' => false,
                'message' => 'Award not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $award
        ]);
    }

    /**
     * Get featured awards
     */
    public function featured()
    {
        $awards = Award::orderBy('created_at', 'desc')
                      ->limit(6)
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $awards
        ]);
    }
}
