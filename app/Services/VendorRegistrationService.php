<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VendorRequest;
use App\Notifications\VendorApproved;
use App\Notifications\VendorRejected;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Tenancy;

class VendorRegistrationService
{
    /**
     * Liste des slugs réservés (interdits pour les boutiques).
     */
    private const RESERVED_SLUGS = [
        'admin', 'app', 'api', 'www', 'mail', 'smtp', 'cdn', 'assets',
        'static', 'status', 'docs', 'help', 'support', 'blog', 'news',
        'login', 'register', 'logout', 'dashboard', 'devenir-vendeur',
        'shop', 'boutique', 'vendeur', 'stripe', 'payment', 'checkout',
        'settings', 'profile', 'account', 'billing', 'invoice',
        'webhook', 'callback', 'oauth', 'auth', 'password',
    ];

    /**
     * Initier une demande de vendeur.
     */
    public function initiateRegistration(User $user, array $data): VendorRequest
    {
        $plan = Plan::findOrFail($data['plan_id']);

        $slugErrors = $this->validateSlugFormat($data['shop_slug']);
        if (! empty($slugErrors)) {
            throw new \InvalidArgumentException(implode(' ', $slugErrors));
        }

        if (! $this->isShopSlugAvailable($data['shop_slug'])) {
            throw new \InvalidArgumentException('Ce sous-domaine est déjà utilisé.');
        }

        return DB::transaction(function () use ($user, $data, $plan) {
            $vr = VendorRequest::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'shop_name' => trim($data['shop_name']),
                'shop_slug' => Str::lower(trim($data['shop_slug'])),
                'shop_description' => $data['shop_description'] ?? null,
                'contact_email' => $data['contact_email'] ?? $user->email,
                'contact_phone' => $data['contact_phone'] ?? null,
                'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
                'status' => $plan->is_free
                    ? VendorRequest::STATUS_PENDING
                    : VendorRequest::STATUS_PAYMENT_PENDING,
                'metadata' => [
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'submitted_at' => now()->toIso8601String(),
                    'currency' => $data['currency'] ?? 'CDF',
                    'language' => $data['language'] ?? 'fr',
                    'facebook_url' => $data['facebook_url'] ?? null,
                    'instagram_url' => $data['instagram_url'] ?? null,
                    'twitter_url' => $data['twitter_url'] ?? null,
                    'youtube_url' => $data['youtube_url'] ?? null,
                    'tiktok_url' => $data['tiktok_url'] ?? null,
                ],
            ]);

            // ✅ Enregistrer les documents légaux (table centrale)
            if (! empty($data['documents'])) {
                foreach ($data['documents'] as $doc) {
                    $vr->documents()->create([
                        'type_document_id' => $doc['type_document_id'],
                        'numero_document' => $doc['numero_document'] ?? null,
                        'date_delivrance' => $doc['date_delivrance'] ?? null,
                        'date_expiration' => $doc['date_expiration'] ?? null,
                        'lieu_delivrance' => $doc['lieu_delivrance'] ?? null,
                        'autorite_delivrance' => $doc['autorite_delivrance'] ?? null,
                    ]);
                }
            }

            return $vr;
        });
    }

    /**
     * Approuver une demande de vendeur et créer le tenant.
     */
    public function approve(VendorRequest $vendorRequest): Tenant
    {
        $plan = $vendorRequest->plan;
        $user = $vendorRequest->user;
        $password = session('temp_password') ?? 'password';

        // 1ère phase : créer le tenant (sans événements) et le domaine
        $tenant = DB::transaction(function () use ($vendorRequest, $plan, $user, $password) {
            $tenantId = (string) Str::orderedUuid();
            $tenant = Tenant::withoutEvents(function () use ($vendorRequest, $plan, $password, $tenantId, $user) {
                return Tenant::create([
                    'id' => $tenantId,
                    'raison_sociale' => $vendorRequest->shop_name,
                    'slug' => $vendorRequest->shop_slug,
                    'description' => $vendorRequest->shop_description,
                    'email' => $vendorRequest->contact_email,
                    'password' => $password,
                    'telephone' => $vendorRequest->contact_phone,
                    'plan_id' => $plan->id,
                    'statut' => Tenant::STATUT_ACTIF,
                    'is_active' => true,
                    'user_id' => $user->id,
                ]);
            });

            $tenant->domains()->create([
                'id' => (string) Str::orderedUuid(),
                'domain' => str_replace('_', '-', $vendorRequest->shop_slug).'.localhost',
            ]);

            $vendorRequest->update([
                'status' => VendorRequest::STATUS_APPROVED,
                'approved_at' => now(),
                'tenant_id' => $tenant->id,
            ]);

            return $tenant;
        });

        // 2ème phase : créer la base de données du tenant
        event(new TenantCreated($tenant));

        // 3ème phase : attacher l'utilisateur et le reste (maintenant la base existe)
        $user = $vendorRequest->user; //
        $user->tenants()->attach($tenant->id, ['is_owner' => true]);

        if ($plan->trial_days > 0) {
            $tenant->update([
                'date_activation' => now(),
                'date_expiration' => now()->addDays($plan->trial_days),
            ]);
        } else {
            $tenant->update(['date_activation' => now()]);
        }

        $this->transferDocumentsToTenant($vendorRequest, $tenant);
        $this->createTenantStorageSymlink($tenant);

        // Rôles / permissions (contexte central)
        try {
            setPermissionsTeamId($tenant->id);
            if (! $user->hasRole('owner')) {
                $user->assignRole('owner');
            }
            $this->seedDefaultTenantRoles($tenant);
        } catch (\Exception $e) {
            Log::warning('Failed to set up permissions for tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Nettoyage de la session
        session()->forget('temp_password');

        Log::info('Vendor approved', [
            'vendor_request_id' => $vendorRequest->id,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'plan' => $plan->name,
        ]);

        try {
            $user->notify(new VendorApproved($tenant));
        } catch (\Exception $e) {
            Log::error('Failed to send vendor approval notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        return $tenant;
    }

    /**
     * Transférer les documents légaux d'une VendorRequest vers le Tenant.
     */
    protected function transferDocumentsToTenant(VendorRequest $vendorRequest, Tenant $tenant): void
    {
        $vendorDocuments = $vendorRequest->documents;

        foreach ($vendorDocuments as $doc) {
            $tenant->documentsLegaux()->attach($doc->type_document_id, [
                'id' => (string) Str::uuid(),
                'numero_document' => $doc->numero_document,
                'date_delivrance' => $doc->date_delivrance,
                'date_expiration' => $doc->date_expiration,
                'lieu_delivrance' => $doc->lieu_delivrance,
                'autorite_delivrance' => $doc->autorite_delivrance,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Vérifier si un utilisateur peut devenir vendeur.
     */
    public function canBecomeVendor(User $user): bool
    {
        // Vérifier qu'il n'a pas déjà une demande en cours
        $hasPendingRequest = VendorRequest::where('user_id', $user->id)
            ->whereIn('status', [
                VendorRequest::STATUS_PENDING,
                VendorRequest::STATUS_PAYMENT_PENDING,
            ])
            ->exists();

        if ($hasPendingRequest) {
            return false;
        }

        // Vérifier que l'utilisateur n'a pas déjà une boutique active
        $hasActiveTenant = $user->tenants()
            ->where('statut', Tenant::STATUT_ACTIF)
            ->exists();

        if ($hasActiveTenant) {
            return false;
        }

        return true;
    }

    /**
     * Vérifier si un slug de boutique est disponible.
     */
    public function isShopSlugAvailable(string $slug): bool
    {
        // Nettoyer le slug
        $slug = Str::lower(trim($slug));

        // Vérifier les slugs réservés
        if (in_array($slug, self::RESERVED_SLUGS)) {
            return false;
        }

        // Vérifier la disponibilité dans la table tenants
        if (Tenant::where('slug', $slug)->exists()) {
            return false;
        }

        // Vérifier la disponibilité dans les demandes en cours
        if (VendorRequest::where('shop_slug', $slug)
            ->whereIn('status', [
                VendorRequest::STATUS_PENDING,
                VendorRequest::STATUS_PAYMENT_PENDING,
            ])
            ->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Suggérer des slugs alternatifs si le slug demandé est pris.
     */
    public function suggestAlternativeSlugs(string $baseSlug, int $count = 5): array
    {
        $suggestions = [];
        $baseSlug = Str::slug($baseSlug);
        $attempt = 0;

        while (count($suggestions) < $count && $attempt < 50) {
            $attempt++;
            $suggestion = $baseSlug;

            if ($attempt > 1) {
                $suggestion .= '-'.random_int(10, 99);
            }

            // Ajouter un mot aléatoire si toujours indisponible
            if ($attempt > 10) {
                $words = ['shop', 'store', 'boutique', 'art', 'craft', 'market', 'online', 'hub'];
                $suggestion = $baseSlug.'-'.$words[array_rand($words)];
            }

            // Ajouter un nombre si toujours indisponible
            if ($attempt > 20) {
                $suggestion = $baseSlug.'-'.random_int(100, 999);
            }

            if ($this->isShopSlugAvailable($suggestion) && ! in_array($suggestion, $suggestions)) {
                $suggestions[] = $suggestion;
            }
        }

        return $suggestions;
    }

    /**
     * Valider le format d'un slug de boutique.
     */
    public function validateSlugFormat(string $slug): array
    {
        $errors = [];

        if (strlen($slug) < 3) {
            $errors[] = 'Le nom de la boutique doit contenir au moins 3 caractères.';
        }

        if (strlen($slug) > 63) {
            $errors[] = 'Le nom de la boutique ne peut pas dépasser 63 caractères.';
        }

        if (! preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $slug)) {
            $errors[] = 'Le nom ne peut contenir que des lettres minuscules, des chiffres et des tirets.';
        }

        if (str_starts_with($slug, '-') || str_ends_with($slug, '-')) {
            $errors[] = 'Le nom ne peut pas commencer ou se terminer par un tiret.';
        }

        if (str_contains($slug, '--')) {
            $errors[] = 'Le nom ne peut pas contenir de tirets consécutifs.';
        }

        return $errors;
    }

    /**
     * Créer les rôles par défaut pour un nouveau tenant.
     */
    private function seedDefaultTenantRoles(Tenant $tenant): void
    {
        // Ne pas exécuter si les tables de permissions ne sont pas dans le schéma du tenant
        // (selon votre configuration stancl/tenancy)

        $defaultRoles = [
            'owner' => 'Propriétaire de la boutique',
            'manager' => 'Gestionnaire de la boutique',
            'staff' => 'Employé',
            'viewer' => 'Consultation seule',
        ];

        foreach ($defaultRoles as $name => $label) {
            Role::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => 'web',
                    'tenant_id' => $tenant->id,
                ],
                [
                    'name' => $name,
                    'guard_name' => 'web',
                    'tenant_id' => $tenant->id,
                ]
            );
        }

        // Donner toutes les permissions au rôle "owner"
        $ownerRole = Role::where('name', 'owner')
            ->where('tenant_id', $tenant->id)
            ->first();

        if ($ownerRole) {
            $allPermissions = Permission::where('tenant_id', $tenant->id)->get();
            $ownerRole->syncPermissions($allPermissions);
        }

        Log::info('Default roles seeded for tenant', ['tenant_id' => $tenant->id]);
    }

    /**
     * Rejeter une demande de vendeur.
     */
    public function reject(VendorRequest $vendorRequest, string $reason): void
    {
        DB::transaction(function () use ($vendorRequest, $reason) {

            $vendorRequest->update([
                'status' => VendorRequest::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'rejected_at' => now(),
            ]);

            Log::info('Vendor rejected', [
                'vendor_request_id' => $vendorRequest->id,
                'reason' => $reason,
            ]);

            try {
                $vendorRequest->user->notify(new VendorRejected($reason));
            } catch (\Exception $e) {
                Log::error('Failed to send vendor rejection notification', [
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Marquer le paiement comme reçu pour une demande.
     */
    public function markPaymentReceived(VendorRequest $vendorRequest, string $transactionId): void
    {
        $vendorRequest->update([
            'status' => VendorRequest::STATUS_PENDING,
            'payment_status' => 'paid',
            'payment_transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);
    }

    /**
     * Marquer le paiement comme échoué.
     */
    public function markPaymentFailed(VendorRequest $vendorRequest, string $reason): void
    {
        $vendorRequest->update([
            'payment_status' => 'failed',
            'payment_failure_reason' => $reason,
            'status' => VendorRequest::STATUS_PAYMENT_PENDING,
        ]);

        Log::warning('Vendor payment failed', [
            'vendor_request_id' => $vendorRequest->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Récupérer les demandes en attente de validation.
     */
    public function getPendingRequests()
    {
        /** @phpstan-ignore-next-line */
        return VendorRequest::where('status', VendorRequest::STATUS_PENDING)
            ->with(['user', 'plan'])
            ->latest()
            ->get();
    }

    /**
     * Récupérer les demandes en attente de paiement.
     */
    public function getAwaitingPaymentRequests()
    {
        /** @phpstan-ignore-next-line */
        return VendorRequest::where('status', VendorRequest::STATUS_PAYMENT_PENDING)
            ->with(['user', 'plan'])
            ->latest()
            ->get();
    }

    /**
     * Récupérer les statistiques des demandes.
     */
    /** @phpstan-ignore-next-line */
    public function getRegistrationStats(): array
    {
        return [
            'total' => VendorRequest::count(),
            'pending' => VendorRequest::where('status', VendorRequest::STATUS_PENDING)->count(),
            'awaiting_payment' => VendorRequest::where('status', VendorRequest::STATUS_PAYMENT_PENDING)->count(),
            'approved' => VendorRequest::where('status', VendorRequest::STATUS_APPROVED)->count(),
            'rejected' => VendorRequest::where('status', VendorRequest::STATUS_REJECTED)->count(),
            'this_month' => VendorRequest::whereMonth('created_at', now()->month)->count(),
            'last_month' => VendorRequest::whereMonth('created_at', now()->subMonth()->month)->count(),
            'conversion_rate' => $this->calculateConversionRate(),
        ];
    }

    /**
     * Calculer le taux de conversion (demandes → approbations).
     */
    private function calculateConversionRate(): float
    {
        $total = VendorRequest::whereIn('status', [
            VendorRequest::STATUS_APPROVED,
            VendorRequest::STATUS_REJECTED,
        ])->count();

        if ($total === 0) {
            return 0;
        }

        $approved = VendorRequest::where('status', VendorRequest::STATUS_APPROVED)->count();

        return round(($approved / $total) * 100, 1);
    }

    /**
     * Obtenir l'URL du panneau vendeur.
     */
    // public function getVendeurUrl(Tenant $tenant): string
    // {
    //     return $this->tenantBaseUrl($tenant).'/vendeur';
    // }

    /**
     * Obtenir l'URL publique de la boutique.
     */
    public function getShopUrl(Tenant $tenant): string
    {
        return $this->tenantBaseUrl($tenant);
    }

    private function tenantBaseUrl(Tenant $tenant): string
    {
        $host = $tenant->domains()->value('domain')
            ?: $tenant->slug.'.'.config('app.domain', parse_url(config('app.url'), PHP_URL_HOST));

        $appUrl = config('app.url', 'http://localhost');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        $port = parse_url($appUrl, PHP_URL_PORT);

        $portSuffix = app()->environment('local') && $port && ! str_contains($host, ':')
            ? ':'.$port
            : '';

        return rtrim($scheme.'://'.$host.$portSuffix, '/');
    }

    /**
     * Créer le symlink pour le storage du tenant.
     */
    private function createTenantStorageSymlink(Tenant $tenant): void
    {
        $tenantSlug = $tenant->slug;
        $tenantId = $tenant->id;

        // Dossier cible : storage/tenantXXXX/app/public
        $targetDir = storage_path('tenant'.$tenantId.'/app/public');

        // S'assurer que le dossier cible existe
        if (! is_dir($targetDir)) {
            if (! mkdir($targetDir, 0755, true)) {
                Log::error("Impossible de créer le dossier de stockage du tenant : {$targetDir}");

                return;
            }
        }

        // Chemin du lien public
        $publicLinkPath = public_path('storage/tenant-'.$tenantSlug);

        // S'assurer que le dossier public/storage existe
        $publicStorageDir = public_path('storage');
        if (! is_dir($publicStorageDir)) {
            mkdir($publicStorageDir, 0755, true);
        }

        // Supprimer l'ancien lien ou dossier s'il existe
        if (file_exists($publicLinkPath) || is_link($publicLinkPath)) {
            if (is_dir($publicLinkPath) && ! is_link($publicLinkPath)) {
                // C'est un vrai dossier (cas rare), on le supprime récursivement
                $this->deleteDirectory($publicLinkPath);
            } else {
                unlink($publicLinkPath);
            }
        }

        // Déterminer la cible relative selon l'OS
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows natif (hors Docker) : utiliser mklink /J avec chemin relatif
            $relativeTarget = str_replace('/', '\\', '../../storage/tenant'.$tenantId.'/app/public');
            $command = sprintf('mklink /J "%s" "%s"', $publicLinkPath, $relativeTarget);
            exec($command, $output, $returnCode);
            if ($returnCode !== 0) {
                Log::error("Échec de création du lien Windows pour le tenant {$tenantSlug}");
            }
        } else {
            // Linux / macOS / WSL / Docker : lien symbolique relatif
            $relativeTarget = '../../storage/tenant'.$tenantId.'/app/public';
            if (! symlink($relativeTarget, $publicLinkPath)) {
                Log::error("Échec de création du lien symbolique pour le tenant {$tenantSlug}");
            }
        }
    }

    /**
     * Supprimer un répertoire récursivement.
     */
    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * Envoyer un rappel pour les demandes en attente de paiement.
     */
    public function sendPaymentReminders(): void
    {
        $pendingRequests = VendorRequest::where('status', VendorRequest::STATUS_PAYMENT_PENDING)
            ->where('created_at', '<', now()->subHours(24))
            ->where('reminder_sent', false)
            ->get();

        foreach ($pendingRequests as $request) {
            try {
                // Envoyer la notification de rappel
                // $request->user->notify(new VendorPaymentReminder($request));

                $request->update(['reminder_sent' => true]);

                Log::info('Payment reminder sent', [
                    'vendor_request_id' => $request->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send payment reminder', [
                    'vendor_request_id' => $request->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Obtenir l'URL du tableau de bord vendeur (tenant).
     * En local : route de redirection sans sous‑domaine.
     * En production : vrai sous‑domaine.
     */
    public function getVendeurDashboardUrl(Tenant $tenant): string
    {
        return $this->tenantBaseUrl($tenant).'/vendor/dashboard';
    }

    /**
     * Obtenir l'URL du tableau de bord vendeur (tenant).
     * En local : route de redirection sans sous‑domaine.
     * En production : vrai sous‑domaine.
     */
    public function getAdminDashboardUrl(): string
    {
        return route('filament.admin.pages.dashboard');
    }

    /**
     * URL du panneau Filament vendeur.
     */
    public function getVendeurUrl(Tenant $tenant): string
    {
        return $this->tenantBaseUrl($tenant).'/vendeur';
    }

    /**
     * URL de connexion automatique depuis le central vers le dashboard tenant.
     */
    public function getTenantSsoLoginUrl(Tenant $tenant, User $user): string
    {
        $payload = [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ];
        $token = Crypt::encryptString(json_encode($payload));

        $query = http_build_query([
            'token' => $token,
            'tenant_id' => $tenant->id,
        ]);

        // Tenter d'utiliser le domaine du tenant s'il existe
        $domain = $tenant->domains->first();
        if ($domain) {
            $appUrl = config('app.url', 'http://localhost');
            $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
            $port = parse_url($appUrl, PHP_URL_PORT);
            $host = $domain->domain;

            $portSuffix = app()->environment('local') && $port && ! str_contains($host, ':')
                ? ':'.$port
                : '';

            return $scheme.'://'.$host.$portSuffix.'/tenant-sso-login?'.$query;
        }

        // Si les sous-domaines sont activés (fallback pour production)
        if (env('APP_SUBDOMAINS_ENABLED', false)) {
            $domainName = $tenant->slug.'.'.config('app.domain');
            $scheme = app()->environment('production') ? 'https' : 'http';

            return $scheme.'://'.$domainName.'/tenant-sso-login?'.$query;
        }

        // Sinon, développement local sans sous-domaine (fallback central)
        return route('tenant.sso.central', ['token' => $token, 'tenant_id' => $tenant->id]);
    }

    /**
     * Vérifie et connecte un utilisateur via SSO (utilisé par TenantSsoLoginController)
     */
    public function handleSsoLogin(string $token, Tenant $tenant): ?User
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (\Exception $e) {
            return null;
        }

        if (! isset($payload['user_id'], $payload['tenant_id'], $payload['expires_at'])) {
            return null;
        }

        if ($payload['tenant_id'] !== $tenant->id) {
            return null;
        }

        if (now()->timestamp > $payload['expires_at']) {
            return null;
        }

        // ✅ Utiliser la connexion centrale pour vérifier la propriété
        $centralConnection = $this->centralConnection();

        $isOwner = DB::connection($centralConnection)
            ->table('user_tenant')
            ->where('user_id', $payload['user_id'])
            ->where('tenant_id', $tenant->id)
            ->where('is_owner', true)
            ->exists();

        if (! $isOwner) {
            return null;
        }

        // Récupérer l’utilisateur central
        $centralUser = DB::connection($centralConnection)
            ->table('users')
            ->where('id', $payload['user_id'])
            ->first();

        if (! $centralUser) {
            return null;
        }

        // Créer ou mettre à jour l’utilisateur dans le tenant
        $user = User::updateOrCreate(
            ['id' => $centralUser->id],
            [
                'name' => $centralUser->name,
                'email' => $centralUser->email,
                'password' => $centralUser->password,
                'email_verified_at' => $centralUser->email_verified_at,
            ]
        );

        return $user;
    }

    private function centralConnection(): string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }
}
