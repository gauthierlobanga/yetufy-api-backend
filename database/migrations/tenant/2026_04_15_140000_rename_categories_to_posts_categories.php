<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // if (Schema::hasTable('categorie_posts') && ! Schema::hasTable('posts_categories')) {
        //     Schema::rename('categorie_posts', 'posts_categories');
        // }
    }

    public function down(): void
    {
        // if (Schema::hasTable('posts_categories') && ! Schema::hasTable('categories')) {
        //     Schema::rename('posts_categories', 'categories');
        // }
    }
};
