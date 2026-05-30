<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenantInfo = 'groupe_2';

        // Créer le tenant
        $tenant = Tenant::create([
            'id' => $tenantInfo,
            'raison_sociale' => ucfirst($tenantInfo),
            'slug' => Str::slug($tenantInfo),
        ]);

        // Ajouter un domaine pour ce tenant (optionnel, selon votre configuration)
        $tenant->domains()->create([
            'domain' => $tenantInfo.'.localhost',
        ]);

        // Créer l'utilisateur dans le contexte central (pas dans le tenant)
        $user = User::factory()->create([
            'name' => ucfirst($tenantInfo),
            'email' => $tenantInfo.'@gmail.com',
        ]);

        $user->tenants()->attach($tenant->id, ['is_owner' => true]);
    }
}
