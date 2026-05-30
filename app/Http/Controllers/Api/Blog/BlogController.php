<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    /**
     * Affiche la liste des posts de l'utilisateur connecté.
     */
    public function blogIndex(Request $request)
    {
        if ($request->filled('category_id') && ! $request->filled('tag')) {
            $legacyCategory = PostCategory::query()
                ->select('slug')
                ->find($request->integer('category_id'));

            if ($legacyCategory) {
                return redirect()->route('tenant.blog.index', [
                    'tag' => $legacyCategory->slug,
                    ...$request->except('category_id'),
                ]);
            }
        }

        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:'.implode(',', array_keys(Post::getStatuses())),
            'tag' => 'nullable|exists:posts_categories,slug',
            'sort' => 'nullable|string|in:created_at,published_at,title,views_count',
            'direction' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // S'assurer que les clés existent toujours
        $filters = array_merge([
            'search' => null,
            'status' => null,
            'tag' => null,
            'sort' => 'published_at',
            'direction' => 'desc',
        ], $filters);

        // Nettoyer les valeurs nulles ou vides
        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        $query = Post::with([
            'categories' => fn ($q) => $q->select('posts_categories.id', 'nom', 'slug', 'color'),
            'media',
            'user' => fn ($q) => $q->select('id', 'name', 'email'),
        ])->where('status', Post::STATUS_PUBLISHED);

        // Appliquer les filtres...
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('content', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('categories', fn ($q) => $q->where('posts_categories.slug', $filters['tag']));
        }

        // Tri
        $sort = $filters['sort'] ?? 'published_at';
        $direction = $filters['direction'] ?? 'desc';
        $query->orderBy($sort, $direction);

        $perPage = $filters['per_page'] ?? 9;
        $posts = $query->paginate($perPage);

        // N'ajouter query string que s'il y a des filtres ou si pas page 1
        if (count(array_filter($filters)) > 0 || ($posts->currentPage() > 1)) {
            $posts->withQueryString();
        }

        $categories = PostCategory::select('id', 'nom', 'slug', 'color')
            ->where('est_active', true)
            ->orderBy('nom')
            ->get();

        return response()->json([
            'posts' => PostResource::collection($posts),
            'categories' => CategoryResource::collection($categories),
            'filters' => $filters,
            'statuses' => Post::getStatuses(),
        ]);
    }


    public function blogShow(Post $post, Request $request)
    {
        $user = Auth::user();
        $post->incrementViews();
        $post->load(['categories', 'media', 'user', 'tags']);

        $previousPost = $post->getPreviousPublished();
        $nextPost = $post->getNextPublished();
        $relatedPosts = $post->getRelatedPosts(3);

        $postResource = (new PostResource($post))->resolve();

        return response()->json([
            'post' => [
                'data' => array_merge($postResource, [
                    'is_liked' => $post->isLikedBy($user),
                    'is_bookmarked' => $post->isBookmarkedBy($user),
                    'likes_count' => $post->likes()->count(),
                    'bookmarks_count' => $post->bookmarkedBy()->count(),
                ]),
            ],
            'previousPost' => $previousPost ? new PostResource($previousPost) : null,
            'nextPost' => $nextPost ? new PostResource($nextPost) : null,
            'relatedPosts' => PostResource::collection($relatedPosts),
        ]);

    }

    public function blogByCategory(PostCategory $category)
    {
        return route('tenant.blog.index', ['tag' => $category->slug]);
    }

    public function blogComment() {}

    public function blogLike(Post $post)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Authentification requise'], 401);
        }

        $existing = $post->likes()->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
            $message = 'Like retiré';
            $isLiked = false;
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            $message = 'Article aimé';
            $isLiked = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_liked' => $isLiked,
            'likes_count' => $post->likes()->count(),
        ]);
    }

    public function blogBookmark(Post $post)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Authentification requise'], 401);
        }

        $existing = $post->bookmarkedBy()->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
            $message = 'Favori retiré';
            $isBookmarked = false;
        } else {
            $post->bookmarkedBy()->attach($user->id);
            $message = 'Article ajouté aux favoris';
            $isBookmarked = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_bookmarked' => $isBookmarked,
            'bookmarks_count' => $post->bookmarkedBy()->count(),
        ]);
    }
}
