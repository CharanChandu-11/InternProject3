<?php
// app/Http/Controllers/Api/Public/GalleryController.php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryController extends BaseController
{
    /**
     * Get featured gallery images
     */
    public function featured()
    {
        $images = Gallery::where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        return $this->sendResponse(
            GalleryResource::collection($images),
            'Featured gallery images retrieved successfully'
        );
    }

    /**
     * Get gallery categories with counts
     */
    public function categories()
    {
        $categories = Gallery::where('is_active', true)
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->category,
                    'display_name' => ucwords(str_replace('_', ' ', $item->category)),
                    'count' => $item->total,
                    'cover_image' => $this->getCategoryCoverImage($item->category)
                ];
            });

        return $this->sendResponse($categories, 'Gallery categories retrieved successfully');
    }

    /**
     * Get gallery images by category
     */
    public function byCategory($category, Request $request)
    {
        $images = Gallery::where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return $this->sendPaginatedResponse(
            GalleryResource::collection($images),
            'Gallery images retrieved successfully'
        );
    }

    /**
     * Get single gallery image
     */
    public function show($id)
    {
        $image = Gallery::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        // Get related images (same category)
        $related = Gallery::where('category', $image->category)
            ->where('id', '!=', $image->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return $this->sendResponse([
            'image' => new GalleryResource($image),
            'related' => GalleryResource::collection($related)
        ], 'Gallery image retrieved successfully');
    }

    /**
     * Get category cover image
     */
    private function getCategoryCoverImage($category)
    {
        $cover = Gallery::where('category', $category)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $cover ? $cover->thumbnail_url : null;
    }
}