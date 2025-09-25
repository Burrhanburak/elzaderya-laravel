<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Blog::query();

        // Language filter
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        // Debug logging
        \Log::info('Blog API Request', [
            'language' => $request->get('language'),
            'search' => $request->get('search'),
            'page' => $request->get('page'),
            'per_page' => $request->get('per_page')
        ]);

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $blogs = $query->with('categories')
                      ->orderBy('published_at', 'desc')
                      ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $blogs->items(),
            'pagination' => [
                'current_page' => $blogs->currentPage(),
                'last_page' => $blogs->lastPage(),
                'per_page' => $blogs->perPage(),
                'total' => $blogs->total(),
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

        $blog = Blog::with('categories')->where('slug', $slug)->first();

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $blog
        ]);
    }

    /**
     * Get featured blogs
     */
    public function featured()
    {
        $blogs = Blog::with('categories')
                    ->whereNotNull('published_at')
                    ->orderBy('published_at', 'desc')
                    ->limit(6)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }
}
