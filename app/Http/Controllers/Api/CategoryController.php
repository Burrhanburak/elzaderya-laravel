<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Language filter
        if ($request->has('language')) {
            $language = $request->get('language');
            $query->orderByRaw("name_{$language} ASC");
        } else {
            $query->orderBy('name_tr', 'asc');
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_tr', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ru', 'like', "%{$search}%")
                  ->orWhere('name_az', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $categories = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $categories->items(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ]
        ]);
    }

    public function show($slug)
    {
        // Validate slug parameter
        if (empty($slug) || $slug === 'null' || $slug === 'undefined') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid slug parameter'
            ], 400);
        }

        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function featured()
    {
        $categories = Category::limit(6)->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}