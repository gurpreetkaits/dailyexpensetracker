<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'target' => $this->target,
            'saved' => $this->saved,
            'target_date' => $this->target_date->format('Y-m-d'),
            'is_completed' => $this->is_completed,
            'progress_percentage' => $this->progress_percentage,
            'remaining_amount' => $this->remaining_amount,
            'months_remaining' => $this->months_remaining,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
