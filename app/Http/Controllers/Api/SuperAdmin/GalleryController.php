<?php
// app/Http/Controllers/Api/SuperAdmin/GalleryController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SuperAdmin\StoreGalleryRequest;
use App\Http\Requests\Api\SuperAdmin\UpdateGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class GalleryController extends BaseController
{
    /**
     * Display a listing of gallery images
     */
    public function index(Request $request)
    {
        $query = Gallery::with(['uploader', 'event']);
        
        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter by event
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        
        // Filter featured
        if ($request->has('featured')) {
            $query->where('is_featured', $request->featured);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $galleries = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse(
            GalleryResource::collection($galleries),
            'Gallery images retrieved successfully'
        );
    }
    
    /**
     * Store a newly created gallery image
     */
    public function store(StoreGalleryRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Generate filename
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                
                // Store original image
                $path = $image->storeAs('gallery/original', $filename, 'public');
                
                // Create thumbnail
                $this->createThumbnail($image, $filename);
                
                $validated['image'] = $path;
            }
            
            // Add metadata
            $validated['uploaded_by'] = auth()->id();
            $validated['is_active'] = true;
            $validated['metadata'] = [
                'size' => $request->file('image')?->getSize(),
                'mime_type' => $request->file('image')?->getMimeType(),
                'dimensions' => $this->getImageDimensions($request->file('image'))
            ];
            
            $gallery = Gallery::create($validated);
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'gallery',
                'description' => "Added image to gallery: {$gallery->title}"
            ]);
            
            DB::commit();
            
            return $this->sendResponse(
                new GalleryResource($gallery->load(['uploader', 'event'])),
                'Image uploaded successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to upload image: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified gallery image
     */
    public function show(Gallery $gallery)
    {
        $gallery->load(['uploader', 'event']);
        
        return $this->sendResponse(
            new GalleryResource($gallery),
            'Gallery image retrieved successfully'
        );
    }
    
    /**
     * Update the specified gallery image
     */
    public function update(UpdateGalleryRequest $request, Gallery $gallery)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image and thumbnail
                $this->deleteImageFiles($gallery);
                
                // Upload new image
                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('gallery/original', $filename, 'public');
                
                // Create new thumbnail
                $this->createThumbnail($image, $filename);
                
                $validated['image'] = $path;
                $validated['metadata'] = array_merge($gallery->metadata ?? [], [
                    'updated_size' => $image->getSize(),
                    'updated_mime' => $image->getMimeType(),
                    'updated_at' => now()->toDateTimeString()
                ]);
            }
            
            $gallery->update($validated);
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'gallery',
                'description' => "Updated gallery image: {$gallery->title}"
            ]);
            
            DB::commit();
            
            return $this->sendResponse(
                new GalleryResource($gallery->fresh(['uploader', 'event'])),
                'Gallery image updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update image: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified gallery image
     */
    public function destroy(Gallery $gallery)
    {
        // Delete image files
        $this->deleteImageFiles($gallery);
        
        $title = $gallery->title;
        $gallery->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'gallery',
            'description' => "Deleted gallery image: {$title}"
        ]);
        
        return $this->sendResponse([], 'Gallery image deleted successfully');
    }
    
    /**
     * Toggle featured status
     */
    public function toggleFeatured(Gallery $gallery)
    {
        $gallery->update(['is_featured' => !$gallery->is_featured]);
        
        $status = $gallery->is_featured ? 'featured' : 'unfeatured';
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $status,
            'module' => 'gallery',
            'description' => "{$status} gallery image: {$gallery->title}"
        ]);
        
        return $this->sendResponse(
            ['is_featured' => $gallery->is_featured],
            "Image {$status} successfully"
        );
    }
    
    /**
     * Update sort order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:galleries,id',
            'orders.*.sort_order' => 'required|integer'
        ]);
        
        foreach ($request->orders as $item) {
            Gallery::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }
        
        return $this->sendResponse([], 'Gallery order updated successfully');
    }
    
    /**
     * Bulk delete gallery images
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:galleries,id'
        ]);
        
        $galleries = Gallery::whereIn('id', $request->ids)->get();
        
        foreach ($galleries as $gallery) {
            $this->deleteImageFiles($gallery);
            $gallery->delete();
        }
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_deleted',
            'module' => 'gallery',
            'description' => "Deleted " . count($request->ids) . " gallery images"
        ]);
        
        return $this->sendResponse([], 'Gallery images deleted successfully');
    }
    
    /**
     * Get gallery categories with counts
     */
    public function categories()
    {
        $categories = Gallery::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderBy('category')
            ->get();
        
        return $this->sendResponse($categories, 'Gallery categories retrieved successfully');
    }
    
    /**
     * Helper: Create thumbnail
     */
    private function createThumbnail($image, $filename)
    {
        if (!class_exists('Intervention\Image\Facades\Image')) {
            return;
        }
        
        try {
            $thumbnailPath = storage_path('app/public/gallery/thumbnails/');
            if (!file_exists($thumbnailPath)) {
                mkdir($thumbnailPath, 0755, true);
            }
            
            $img = Image::make($image->getRealPath());
            $img->resize(300, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($thumbnailPath . $filename);
        } catch (\Exception $e) {
            \Log::error('Thumbnail creation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Helper: Get image dimensions
     */
    private function getImageDimensions($image)
    {
        if (!$image) return null;
        
        try {
            list($width, $height) = getimagesize($image->getRealPath());
            return "{$width}x{$height}";
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Helper: Delete image files
     */
    private function deleteImageFiles(Gallery $gallery)
    {
        if ($gallery->image) {
            Storage::disk('public')->delete($gallery->image);
            
            // Delete thumbnail
            $filename = basename($gallery->image);
            Storage::disk('public')->delete('gallery/thumbnails/' . $filename);
        }
    }
}