<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ProduitCategorySeeder::class,
            BrandSeeder::class,
            // ProductSeeder::class,
        ]);
    }
}
