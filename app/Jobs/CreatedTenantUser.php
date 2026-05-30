<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatedTenantUser implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Tenant $tenant) {}

    public function handle(): void
    {
        $this->tenant->run(function () {
            // Récupérer l'utilisateur central depuis la base de données centrale
            // Utiliser la connexion par défaut (public schema)
            $centralUser = DB::connection('pgsql')
                ->table('users')
                ->where('id', $this->tenant->user_id)
                ->first();

            if (! $centralUser) {
                return;
            }

            // Créer l'utilisateur dans le tenant avec les mêmes données
            DB::table('users')->insert([
                'id' => $centralUser->id,
                'name' => $centralUser->name,
                'email' => $centralUser->email,
                'password' => $this->tenant->password, // Mot de passe du tenant
                'email_verified_at' => $centralUser->email_verified_at,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Artisan::call('shield:super-admin', [
                '--user' => $centralUser->id,
                '--panel' => 'vendeur',
                '--no-interaction' => true,
            ]);
        });
    }
}
