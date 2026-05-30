<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->sentence;
        $status = $this->faker->randomElement(array_keys(Post::getStatuses()));
        $publishedAt = null;
        $scheduledFor = null;

        if ($status === Post::STATUS_PUBLISHED) {
            $publishedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        } elseif ($status === Post::STATUS_SCHEDULED) {
            $scheduledFor = $this->faker->dateTimeBetween('+1 day', '+1 month');
        }

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'content' => $this->faker->paragraphs(5, true),
            'status' => $status,
            'is_pinned' => $this->faker->boolean(10),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'likes_count' => $this->faker->numberBetween(0, 100),
            'comments_count' => $this->faker->numberBetween(0, 50),
            'published_at' => $publishedAt,
            'scheduled_for' => $scheduledFor,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now()->subDays(rand(1, 30)),
                'scheduled_for' => null,
            ];
        });
    }

    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Post::STATUS_DRAFT,
                'published_at' => null,
                'scheduled_for' => null,
            ];
        });
    }

    public function scheduled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Post::STATUS_SCHEDULED,
                'published_at' => null,
                'scheduled_for' => now()->addDays(rand(1, 30)),
            ];
        });
    }
}
