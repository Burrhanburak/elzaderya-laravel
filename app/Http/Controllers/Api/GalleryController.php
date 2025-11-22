<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    /**
     * Display a listing of galleries.
     */
    public function index(Request $request)
    {
        $query = Gallery::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by tags
        if ($request->has('tags') && $request->tags) {
            $tags = explode(',', $request->tags);
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', trim($tag));
                }
            });
        }

        // Only active galleries
        $query->where('is_active', true);

        $perPage = $request->get('per_page', 12);
        $galleries = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform images to full URLs
        $galleries->getCollection()->transform(function ($gallery) {
            return $this->transformGallery($gallery);
        });

        return response()->json([
            'success' => true,
            'data' => $galleries->items(),
            'pagination' => [
                'current_page' => $galleries->currentPage(),
                'last_page' => $galleries->lastPage(),
                'per_page' => $galleries->perPage(),
                'total' => $galleries->total(),
            ],
        ]);
    }

    /**
     * Display the specified gallery.
     */
    public function show($id)
    {
        $gallery = Gallery::where('is_active', true)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformGallery($gallery),
        ]);
    }

    /**
     * Get only active galleries.
     */
    public function active()
    {
        $galleries = Gallery::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($gallery) {
                return $this->transformGallery($gallery);
            });

        return response()->json([
            'success' => true,
            'data' => $galleries,
        ]);
    }

    /**
     * Transform gallery images to full URLs.
     */
    private function transformGallery($gallery)
    {
        if ($gallery->images && is_array($gallery->images)) {
            $gallery->images = array_map(function ($image) {
                return Storage::disk('s3')->url($image);
            }, $gallery->images);
        }

        return $gallery;
    }
}
