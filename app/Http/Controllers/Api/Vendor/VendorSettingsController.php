<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantPropsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class VendorSettingsController extends Controller
{
    /**
     * Affiche le formulaire des paramètres de la boutique.
     */
    public function edit(TenantPropsService $tenantProps)
    {
        $user = Auth::user();
        $tenant = $this->resolveOwnedTenant($user);

        if (! $tenant) {
            abort(403);
        }

        return response()->json([
            'tenant' => $tenantProps->getTenantProps($tenant),
        ]);
    }

    /**
     * Met à jour les informations de la boutique.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $tenant = $this->resolveOwnedTenant($user);

        if (! $tenant) {
            abort(403);
        }

        // Connexion vers la base centrale (ici 'pgsql', mais on peut la récupérer dynamiquement)
        $centralConnection = config('tenancy.database.central_connection', config('database.default'));

        $validated = $request->validate([
            'raison_sociale' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($tenant, $centralConnection) {
                    $exists = DB::connection($centralConnection)
                        ->table('tenants')
                        ->where('email', $value)
                        ->where('id', '<>', $tenant->id)
                        ->exists();

                    if ($exists) {
                        $fail('Cet email est déjà utilisé par une autre boutique.');
                    }
                },
            ],
            'telephone' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $tenant->raison_sociale = $validated['raison_sociale'];
        $tenant->description = $validated['description'] ?? null;
        $tenant->email = $validated['email'];
        $tenant->telephone = $validated['telephone'] ?? null;

        $tenant->setConfiguration('facebook_url', $validated['facebook_url'] ?? null);
        $tenant->setConfiguration('instagram_url', $validated['instagram_url'] ?? null);
        $tenant->setConfiguration('twitter_url', $validated['twitter_url'] ?? null);
        $tenant->setConfiguration('youtube_url', $validated['youtube_url'] ?? null);
        $tenant->setConfiguration('tiktok_url', $validated['tiktok_url'] ?? null);

        $tenant->save();

        if ($request->hasFile('logo') || $request->boolean('remove_logo')) {
            $this->replaceLogo($tenant, $request);
        }

        return response()->json(['success' => 'Paramètres mis à jour avec succès.']);
    }

    private function resolveOwnedTenant($user): ?Tenant
    {
        $tenant = function_exists('tenant') ? tenant() : null;

        if (! $tenant || ! $user) {
            return null;
        }

        $ownsTenant = DB::connection($this->centralConnection())
            ->table('user_tenant')
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('is_owner', true)
            ->exists();

        return $ownsTenant ? $tenant : null;
    }

    private function replaceLogo(Tenant $tenant, Request $request): void
    {
        $this->clearTenantScopedLogoArtifacts($tenant);

        tenancy()->central(function () use ($tenant, $request) {
            $centralTenant = Tenant::query()->findOrFail($tenant->id);
            $centralTenant->clearMediaCollection('tenant_avatar');

            if (! $request->hasFile('logo')) {
                return;
            }

            $file = $request->file('logo');

            $centralTenant
                ->addMedia($file)
                ->usingFileName('logo-'.$centralTenant->id.'-'.Str::uuid().'.'.$file->getClientOriginalExtension())
                ->toMediaCollection('tenant_avatar', 'public');
        });
    }

    private function clearTenantScopedLogoArtifacts(Tenant $tenant): void
    {
        if (! function_exists('tenancy') || ! tenancy()->initialized) {
            return;
        }

        try {
            $tenant->clearMediaCollection('tenant_avatar');
        } catch (Throwable) {
            //
        }
    }

    private function centralConnection(): string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }
}
