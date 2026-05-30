<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'status' => $this->status,
            'is_pinned' => $this->is_pinned,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'reading_time_minutes' => $this->reading_time_minutes,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'published_at' => $this->published_at?->format('Y-m-d'), // ->diffForHumans(),
            'scheduled_for' => $this->scheduled_for,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at,

            // Relations
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar_url' => $this->user->avatar_url,
            ]),

            'categories' => $this->whenLoaded(
                'categories',
                fn () => $this->categories->map(fn ($category) => [
                    'id' => $category->id,
                    'nom' => $category->nom,
                    'slug' => $category->slug,
                    'color' => $category->color,
                ])
            ),

            'tags' => $this->whenLoaded(
                'tags',
                fn () => $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'order_column' => $tag->order_column,
                ])
            ),

            'media' => $this->whenLoaded(
                'media',
                fn () => $this->getMedia('gallery')->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'medium_url' => $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl(),
                    'large_url' => $media->hasGeneratedConversion('large') ? $media->getUrl('large') : $media->getUrl(),
                    'name' => $media->name,
                    'size' => $media->size,
                ])
            ),

            // Accesseurs
            'url' => $this->url,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'is_published' => $this->is_published,
            'featured_image_url' => $this->featured_image_url,
            'featured_image_thumb' => $this->featured_image_thumb,
            'featured_image_deatil' => $this->featured_image_detail,
            'featured_image_card' => $this->featured_image_card,
            'gallery_images' => $this->gallery_images,
        ];
    }
}
