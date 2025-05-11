<?php

namespace App\Http\Controllers;

use App\Data\CategoryStoreData;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $defaultCategories = Category::where('is_custom', false)->get();
        $userCategories = Category::where('is_custom', true)->where('user_id', auth()->user()->id)->get();

        $categories = $defaultCategories->concat($userCategories);
        return response()->json($categories);
    }

    public function getUserCategories(): \Illuminate\Http\JsonResponse
    {
        $userCategories = Category::where('is_custom', true)
            ->where('user_id', auth()->user()->id)
            ->paginate(10);
        return response()->json($userCategories);
    }


    public function store(CategoryStoreData $data): \Illuminate\Http\JsonResponse
    {

        $category = new Category();
        $category->is_custom = 1;
        $category->user_id = auth()->user()->id;
        $category->type = $data->type;
        $category->icon = $data->icon;
        $category->color = $data->color;
        $category->name = $data->name;
        $category->save();

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    public function update(CategoryStoreData $data, Category $category): \Illuminate\Http\JsonResponse
    {
        $category->update($data->all());
        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ], 200);
    }

    public function destroy(Category $category): \Illuminate\Http\JsonResponse
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
