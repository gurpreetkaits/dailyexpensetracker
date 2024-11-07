<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalRequest;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalsController extends Controller
{
    public function index()
    {
        $goals = auth()->user()->goals()->latest()->get();
        return GoalResource::collection($goals);
    }

    public function store(GoalRequest $request): GoalResource
    {
        $goal = auth()->user()->goals()->create($request->validated());
        return new GoalResource($goal);
    }

    public function show(Goal $goal): GoalResource
    {
        return new GoalResource($goal);
    }

    public function update(GoalRequest $request, Goal $goal): GoalResource
    {
        if ($goal->user_id !== auth()->id()) {
            abort(401, 'unauthorized');
        }
        $goal->update($request->validated());
        return new GoalResource($goal);
    }

    public function destroy(Goal $goal): JsonResponse
    {
        if ($goal->user_id !== auth()->id()) {
            abort(401, 'unauthorized');
        }
        $goal->delete();
        return response()->json(['message' => 'Goal deleted successfully']);
    }

    public function updateProgress(Goal $goal, GoalRequest $request): GoalResource
    {
        $goal->update(['saved' => $request->saved]);
        
        if ($goal->saved >= $goal->target) {
            $goal->update(['is_completed' => true]);
        }

        return new GoalResource($goal);
    }
}