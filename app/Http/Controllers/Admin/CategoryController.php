<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }
        
        $categories = $query->latest()->paginate(15);
        
        return view('admin.categories.index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);
        
        $validated['slug'] = Str::slug($validated['name']);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('category_images', 'public');
        }
        
        // Handle is_active checkbox
        $validated['is_active'] = $request->has('is_active');
        
        Category::create($validated);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $category = Category::withCount('products')
            ->with(['products' => function($query) {
                $query->latest()->take(10);
            }])
            ->findOrFail($id);
        
        return view('admin.categories.show', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        
        return view('admin.categories.edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);
        
        $validated['slug'] = Str::slug($validated['name']);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('category_images', 'public');
        }
        
        // Handle is_active checkbox
        $validated['is_active'] = $request->has('is_active');
        
        $category->update($validated);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with existing products. Please reassign or delete products first.');
        }
        
        // Delete image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        
        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}

