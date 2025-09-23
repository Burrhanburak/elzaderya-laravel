<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Certificate::query();

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
        $certificates = $query->orderBy('created_at', 'desc')
                             ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $certificates->items(),
            'pagination' => [
                'current_page' => $certificates->currentPage(),
                'last_page' => $certificates->lastPage(),
                'per_page' => $certificates->perPage(),
                'total' => $certificates->total(),
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $certificate = Certificate::where('slug', $slug)->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $certificate
        ]);
    }

    /**
     * Get featured certificates
     */
    public function featured()
    {
        $certificates = Certificate::orderBy('created_at', 'desc')
                                  ->limit(6)
                                  ->get();

        return response()->json([
            'success' => true,
            'data' => $certificates
        ]);
    }
}
