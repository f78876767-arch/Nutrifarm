<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        try {
            $banners = Banner::active()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image_url' => 'required|url',
            'description' => 'nullable|string',
            'action_url' => 'nullable|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        try {
            $banner = Banner::create($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $banner,
                'message' => 'Banner created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'string|max:255',
            'image_url' => 'url',
            'description' => 'nullable|string',
            'action_url' => 'nullable|url',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        try {
            $banner->update($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $banner,
                'message' => 'Banner updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Banner $banner)
    {
        try {
            $banner->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
