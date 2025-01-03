<?php

namespace App\Http\Controllers\API\Dashboard\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{

    public function index()
    {
        $locale = request()->header('Accept-Language', 'ar');
        $nameField = $locale === 'ar' ? 'name_ar' : 'name_en';
        $categories = Category::where('status', 'active')
            ->select('id', "$nameField as name", 'image')
            ->get();
        $categories->map(function ($category) {
            $category->image = $category->image ? asset('storage/' . $category->image) : null;
            return $category;
        });
        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No Categories Found',
                'data' => [],
            ]);
        }

        return response()->json([
            'message' => "All Categories",
            'data' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image')
                ->store('uploads/category', ['disk' => 'public']);
        }

        $category = Category::create($data);
        $category->image = $category->image ? asset('storage/' . $category->image) : null;

        return response()->json([
            'message' => "Category Created Successfully",
            'data' => $category,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $data = $request->validate([
            'name_ar' => 'nullable|string',
            'name_en' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
                'data' => [],
            ]);
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')
                ->store('uploads/category', ['disk' => 'public']);
        }

        $category->update($data);

        return response()->json([
            'message' => "Category Edited Successfully",
            'data' => $category,
        ]);
    }

    public function delete($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
                'data' => [],
            ]);
        }

        $imagePath = $category->image;
        $category->delete();
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return response()->json([
            'message' => "Category Deleted Successfully",
            'data' => [],
        ]);
    }
}
