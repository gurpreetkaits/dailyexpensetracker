<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // check subscription
        if (! $user->subscribed('default')) {
            return response()->json([
                'error' => 'Upgrade to Premium to create custom categories.'
            ], 403);
        }

        $category = $user->customCategories()->create([
            'name' => $request->name
        ]);

        return response()->json($category, 201);
    }
}
