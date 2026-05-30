<?php

namespace App\Http\Resources\Tenants;

use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryTenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'nom' => $this->nom,
            'slug' => $this->slug,
            'color' => $this->color,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relations
            'parent' => $this->whenLoaded('parent', fn () => [
                'id' => $this->parent->id,
                'nom' => $this->parent->nom,
                'slug' => $this->parent->slug,
                'color' => $this->parent->color,
            ]),

            'enfants' => $this->whenLoaded('enfants', fn () => [
                'id' => $this->enfants->id,
                'nom' => $this->enfants->nom,
                'slug' => $this->enfants->slug,
                'color' => $this->enfants->color,
            ]),

            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}
