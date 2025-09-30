<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::ordered()->paginate(10);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_url' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        try {
            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('banners', $filename, 'public');
                $imageUrl = asset('storage/' . $path);
            }

            Banner::create([
                'title' => $request->title,
                'image_url' => $imageUrl,
                'description' => $request->description,
                'action_url' => $request->action_url,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order,
            ]);

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create banner: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_url' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'action_url' => $request->action_url,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order,
            ];

            // Handle image upload if new image is provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($banner->image_url) {
                    $oldPath = str_replace(asset('storage/'), '', $banner->image_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $file = $request->file('image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('banners', $filename, 'public');
                $data['image_url'] = asset('storage/' . $path);
            }

            $banner->update($data);

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update banner: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Banner $banner)
    {
        try {
            // Delete image file if exists
            if ($banner->image_url) {
                $imagePath = str_replace(asset('storage/'), '', $banner->image_url);
                Storage::disk('public')->delete($imagePath);
            }

            $banner->delete();

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner deleted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete banner: ' . $e->getMessage()]);
        }
    }

    public function toggleStatus(Banner $banner)
    {
        try {
            $banner->update(['is_active' => !$banner->is_active]);
            
            $status = $banner->is_active ? 'activated' : 'deactivated';
            return redirect()->route('admin.banners.index')
                ->with('success', "Banner {$status} successfully!");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to toggle banner status: ' . $e->getMessage()]);
        }
    }
}
