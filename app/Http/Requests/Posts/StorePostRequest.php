<?php

// app/Http/Requests/Posts/StorePostRequest.php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'is_pinned' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:posts_categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'nullable|date',
            'scheduled_for' => 'nullable|date|after:now',
            'expires_at' => 'nullable|date|after:published_at',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'featured_image' => 'nullable|image|max:5120',
            'gallery' => 'array',
            'gallery.*' => 'image|max:5120',
        ];
    }
}
