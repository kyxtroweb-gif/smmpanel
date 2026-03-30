<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of categories.
     */
    public function getIndex(Request $request)
    {
        $query = Category::withCount('services');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the create category form.
     */
    public function getCreate()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function postStore(Request $request)
    {
        $this->validator($request->all())->validate();

        DB::beginTransaction();

        try {
            // Get the next sort order
            $maxSortOrder = Category::max('sort_order') ?? 0;

            $category = Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? ($maxSortOrder + 1),
                'is_active' => $request->boolean('is_active'),
            ]);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('category-icons', 'public');
                $category->icon = $iconPath;
                $category->save();
            }

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Created category: {$category->name}");

            DB::commit();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Show the edit category form.
     */
    public function getEdit(int $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function postUpdate(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        $this->validator($request->all(), $id)->validate();

        DB::beginTransaction();

        try {
            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? $category->sort_order,
                'is_active' => $request->boolean('is_active'),
            ]);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                // Delete old icon if exists
                if ($category->icon && \Storage::disk('public')->exists($category->icon)) {
                    \Storage::disk('public')->delete($category->icon);
                }
                $iconPath = $request->file('icon')->store('category-icons', 'public');
                $category->icon = $iconPath;
                $category->save();
            }

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Updated category: {$category->name}");

            DB::commit();

            return back()->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Delete a category.
     */
    public function postDelete(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        // Check if category has services
        if ($category->services()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing services. Please remove or reassign services first.');
        }

        $categoryName = $category->name;

        // Delete icon if exists
        if ($category->icon && \Storage::disk('public')->exists($category->icon)) {
            \Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        // Reorder remaining categories
        $this->reorderCategories();

        // Create activity log
        activity()
            ->causedBy(auth()->user())
            ->log("Deleted category: {$categoryName}");

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle category active status.
     */
    public function postToggle(int $id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'enabled' : 'disabled';

        activity()
            ->causedBy(auth()->user())
            ->log("Category {$category->name} has been {$status}");

        return back()->with('success', "Category has been {$status}.");
    }

    /**
     * Reorder categories based on sort_order.
     */
    public function postReorder(Request $request)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:categories,id'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->order as $index => $categoryId) {
                Category::where('id', $categoryId)->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Categories reordered successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Reorder categories after deletion.
     */
    protected function reorderCategories()
    {
        $categories = Category::orderBy('sort_order')->get();
        $order = 1;

        foreach ($categories as $category) {
            $category->sort_order = $order;
            $category->save();
            $order++;
        }
    }

    /**
     * Get a validator for an incoming validation request.
     */
    protected function validator(array $data, int $id = null)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];

        return Validator::make($data, $rules);
    }

    /**
     * Bulk action on categories.
     */
    public function postBulkAction(Request $request)
    {
        $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'action' => ['required', 'in:enable,disable,delete'],
        ]);

        $categoryIds = $request->category_ids;
        $action = $request->action;

        DB::beginTransaction();

        try {
            $query = Category::whereIn('id', $categoryIds);

            switch ($action) {
                case 'enable':
                    $query->update(['is_active' => true]);
                    break;

                case 'disable':
                    $query->update(['is_active' => false]);
                    break;

                case 'delete':
                    // Only delete categories without services
                    $categoriesToDelete = $query->whereDoesntHave('services')->get();
                    foreach ($categoriesToDelete as $category) {
                        $category->delete();
                    }
                    break;
            }

            $this->reorderCategories();

            DB::commit();

            return back()->with('success', 'Bulk action completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }
}
