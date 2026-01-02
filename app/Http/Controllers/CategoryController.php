<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    use HttpResponses;

    /**
     * Display a list of all categories.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return $this->success($categories);
        } catch (\Exception $e) {
            Log::error("Category index error: {$e->getMessage()}");
            return $this->error('Failed to retrieve categories', 500);
        }
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = Category::create([
                'name' => $request->validated()['name'],
            ]);

            return $this->created($category, 'Category created successfully');
        } catch (\Exception $e) {
            Log::error("Category store error: {$e->getMessage()}");
            return $this->error('Failed to create category', 500);
        }
    }

    /**
     * Get all podcasts that belong to a specific category.
     */
    public function podcasts(int $id)
    {
        try {
            $category = Category::with('podcasts.channel.user')->findOrFail($id);
            return $this->success($category->podcasts);
        } catch (\Exception $e) {
            Log::error("Category podcasts error [{$id}]: {$e->getMessage()}");
            return $this->error('Failed to retrieve podcasts for category', 500);
        }
    }
}
