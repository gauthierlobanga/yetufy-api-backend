<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            // BlogCategorySeeder::class,
            // PostSeeder::class,
            ProduitCategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
