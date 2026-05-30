<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRegistrationRequest;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TypeDocumentLegal;
use App\Services\VendorRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\Language;

class VendorRegistrationController extends Controller
{
    public function __construct(
        private readonly VendorRegistrationService $vendorService
    ) {}

    /**
     * Étape 1 : Choix du plan.
     */
    public function vendeurIndex()
    {
        $user = Auth::user();

        if ($user && ($tenant = $user->tenants()->wherePivot('is_owner', true)->first())) {
            return response()->json([
                'message' => 'Vous possédez déjà une boutique.',
                'tenant' => [
                    'id' => $tenant->id,
                    'slug' => $tenant->slug,
                    'raison_sociale' => $tenant->raison_sociale,
                    'url' => $this->vendorService->getShopUrl($tenant),
                    'admin_url' => $this->vendorService->getVendeurUrl($tenant),
                    'sso_url' => $this->vendorService->getTenantSsoLoginUrl($tenant, $user),
                ],
            ], 409);
        }

        $plans = Plan::active()->ordered()->get()->map(fn ($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'highlight' => $plan->highlight,
            'price' => (float) $plan->price,
            'currency' => $plan->currency,
            'interval' => $plan->interval,
            'trial_days' => (int) $plan->trial_days,
            'is_featured' => $plan->is_featured,
            'is_recommended' => $plan->is_recommended,
            'features' => $plan->features ?? [],
            'limits' => $plan->limits ?? [],
            'badge' => $plan->badge,
            'badge_color' => $plan->badge_color,
            'button_text' => $plan->button_text,
        ]);

        return response()->json([
            'plans' => $plans,
            'canBecomeVendor' => $user ? $this->vendorService->canBecomeVendor($user) : true,
        ]);
    }

    /**
     * Étape 2 : Configuration de la boutique.
     */
    public function vendeurConfigure(Request $request)
    {
        $planId = $request->input('plan_id') ?? session('selected_plan_id');

        if (! $planId) {
            return response()->json(['message' => 'Veuillez sélectionner un plan.'], 422);
        }

        session(['selected_plan_id' => $planId]);

        $plan = Plan::findOrFail($planId);

        // Devises depuis nnjeim/world
        $currencies = Currency::select('code', 'symbol', 'name')
            ->orderBy('code')
            ->get()
            ->unique('code')
            ->values()
            ->map(fn ($c) => [
                'code' => $c->code,
                'symbol' => $c->symbol,
                'name' => $c->name,
            ]);

        // Langues depuis nnjeim/world
        $languages = Language::select('code', 'name')
            ->orderBy('name')
            ->get()
            ->unique('code')
            ->values()
            ->map(fn ($l) => [
                'code' => $l->code,
                'name' => $l->name,
            ]);

        // Pays pour les codes téléphoniques (nnjeim/world)
        $countries = Country::select('id', 'iso2', 'name', 'phone_code')
            ->whereNotNull('phone_code')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'iso2' => strtolower($c->iso2),
                'name' => $c->name,
                'phone_code' => '+'.$c->phone_code,
            ]);

        $requiredDocuments = TypeDocumentLegal::obligatoires()
            ->orderBy('ordre', 'asc')
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'code' => $doc->code,
                'nom' => $doc->nom,
                'description' => $doc->description,
                'est_obligatoire' => true,
                'forme_juridique' => $doc->forme_juridique, // ← déjà présent en base
            ])
            ->values(); // on obtient une simple collection, pas un Record

        $optionalDocuments = TypeDocumentLegal::optionnels()
            ->orderBy('ordre', 'asc')
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'code' => $doc->code,
                'nom' => $doc->nom,
                'description' => $doc->description,
                'est_obligatoire' => false,
                'forme_juridique' => $doc->forme_juridique,
            ])
            ->values();

        return response()->json([
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'formatted_price' => $plan->price > 0 ? $plan->formatted_price : 'Gratuit',
                'price' => $plan->price,
            ],
            'currencies' => $currencies,
            'languages' => $languages,
            'countries' => $countries,
            'requiredDocuments' => $requiredDocuments,
            'optionalDocuments' => $optionalDocuments,
        ]);
    }

    /**
     * Vérifier la disponibilité d'un domaine (retour JSON).
     */
    public function checkDomain(Request $request)
    {
        $request->validate(['slug' => 'required|string|min:3|max:63']);
        $slug = Str::lower(trim($request->slug));
        $service = $this->vendorService;

        // Nettoyage automatique
        $cleanedSlug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $cleanedSlug = trim($cleanedSlug, '-');
        $cleanedSlug = preg_replace('/-+/', '-', $cleanedSlug);

        $formatErrors = $service->validateSlugFormat($cleanedSlug);
        $available = empty($formatErrors) && $service->isShopSlugAvailable($cleanedSlug);

        $suggestions = [];
        if (! $available && empty($formatErrors)) {
            $suggestions = $service->suggestAlternativeSlugs($cleanedSlug, 5);
        }

        return response()->json([
            'available' => $available,
            'cleaned_slug' => $cleanedSlug,
            'errors' => $formatErrors,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Suggérer des domaines à partir du nom de la boutique.
     */
    public function suggestDomain(Request $request)
    {
        $request->validate(['shop_name' => 'required|string|min:2']);
        $shopName = $request->shop_name;
        $service = $this->vendorService;

        // Génération de variantes
        $baseSlug = Str::slug($shopName);
        $variants = $this->generateDomainVariants($baseSlug);

        $suggestions = [];
        foreach ($variants as $variant) {
            if ($service->isShopSlugAvailable($variant)) {
                $suggestions[] = [
                    'slug' => $variant,
                    'domain' => $variant.'.'.config('app.domain'),
                ];
                if (count($suggestions) >= 5) {
                    break;
                }
            }
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    private function generateDomainVariants(string $baseSlug): array
    {
        $variants = [$baseSlug];
        $prefixes = ['shop', 'store', 'boutique', 'mon', 'my'];
        $suffixes = ['shop', 'store', 'boutique', 'online', 'cd', 'rdc'];
        foreach ($prefixes as $p) {
            $variants[] = $p.'-'.$baseSlug;
        }
        foreach ($suffixes as $s) {
            $variants[] = $baseSlug.'-'.$s;
        }

        return array_unique($variants);
    }

    // public function vendeurStore(VendorRegistrationRequest $request)
    // {
    //     $user = Auth::user();
    //     $plan = Plan::findOrFail($request->plan_id);

    //     if (! $this->vendorService->canBecomeVendor($user)) {
    //         return back()->with('error', 'Vous avez déjà une demande en cours.');
    //     }

    //     if (! $this->vendorService->isShopSlugAvailable($request->shop_slug)) {
    //         return back()->withErrors(['shop_slug' => 'Ce sous-domaine est déjà utilisé.']);
    //     }

    //     $vendorRequest = $this->vendorService->initiateRegistration($user, $request->validated());
    //     session()->forget('selected_plan_id');

    //     // Stocker le mot de passe en session pour l'utiliser lors de l'approbation
    //     session(['temp_password' => $request->password]);

    //     // Sauvegarder temporairement le logo s'il existe
    //     if ($request->hasFile('logo')) {
    //         session(['temp_logo_path' => $request->file('logo')->store('temp')]);
    //     }

    //     // Paiement
    //     if ($plan->price > 0) {
    //         session(['vendor_request_id' => $vendorRequest->id]);

    //         return redirect()->route('vendor.payment');
    //     }

    //     // Approbation immédiate (plan gratuit)
    //     $tenant = $this->vendorService->approve($vendorRequest);

    //     // Attacher le logo au tenant
    //     if ($logoPath = session('temp_logo_path')) {
    //         try {
    //             $tenant->addMedia(storage_path('app/'.$logoPath))
    //                 ->usingFileName('logo-'.$tenant->id.'.png')
    //                 ->toMediaCollection('tenant_avatar');
    //             // Supprimer le fichier temporaire
    //             Storage::delete($logoPath);
    //             session()->forget('temp_logo_path');
    //         } catch (\Exception $e) {
    //             Log::error('Erreur sauvegarde logo', [
    //                 'error' => $e->getMessage(),
    //                 'tenant_id' => $tenant->id,
    //             ]);
    //         }
    //     }

    //     return redirect()->route('vendor.success', ['tenant' => $tenant->slug]);
    // }

    public function vendeurStore(VendorRegistrationRequest $request)
    {
        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan_id);

        if (! $this->vendorService->canBecomeVendor($user)) {
            return response()->json(['message' => 'Vous avez déjà une demande en cours.'], 409);
        }

        if (! $this->vendorService->isShopSlugAvailable($request->shop_slug)) {
            return response()->json([
                'message' => 'Ce sous-domaine est déjà utilisé.',
                'errors' => ['shop_slug' => ['Ce sous-domaine est déjà utilisé.']],
            ], 422);
        }

        // Initier la demande (les documents sont déjà dans $request->validated() si la FormRequest les accepte)
        $vendorRequest = $this->vendorService->initiateRegistration($user, $request->validated());
        session()->forget('selected_plan_id');

        // Stocker le mot de passe en session pour l'utiliser lors de l'approbation
        session(['temp_password' => $request->password]);

        // Sauvegarder temporairement le logo s'il existe
        if ($request->hasFile('logo')) {
            session(['temp_logo_path' => $request->file('logo')->store('temp')]);
        }

        // Si le plan est payant, on redirige vers le paiement
        if ($plan->price > 0) {
            session(['vendor_request_id' => $vendorRequest->id]);

            return response()->json([
                'message' => 'Demande créée. Paiement requis pour activer la boutique.',
                'vendor_request' => [
                    'id' => $vendorRequest->id,
                    'status' => $vendorRequest->status,
                ],
                'payment_required' => true,
            ], 201);
        }

        // Approbation immédiate (plan gratuit)
        $tenant = $this->vendorService->approve($vendorRequest);

        // Attacher le logo au tenant (avec vérification d'existence du fichier)
        if ($logoPath = session('temp_logo_path')) {
            try {
                $fullPath = storage_path('app/'.$logoPath);
                if (file_exists($fullPath)) {
                    $tenant->addMedia($fullPath)
                        ->usingFileName('logo-'.$tenant->id.'.png')
                        ->toMediaCollection('tenant_avatar');

                    Log::info('Logo attaché au tenant', [
                        'tenant_id' => $tenant->id,
                        'file' => $logoPath,
                    ]);
                } else {
                    Log::warning('Logo temporaire introuvable', [
                        'path' => $logoPath,
                        'tenant_id' => $tenant->id,
                    ]);
                }
                // Nettoyage systématique
                Storage::delete($logoPath);
                session()->forget('temp_logo_path');
            } catch (\Exception $e) {
                Log::error('Erreur sauvegarde logo', [
                    'error' => $e->getMessage(),
                    'tenant_id' => $tenant->id,
                ]);
            }
        }

        return response()->json([
            'message' => 'Boutique créée avec succès.',
            'payment_required' => false,
            'tenant' => [
                'id' => $tenant->id,
                'raison_sociale' => $tenant->raison_sociale,
                'slug' => $tenant->slug,
                'url' => $this->vendorService->getShopUrl($tenant),
                'admin_url' => $this->vendorService->getVendeurUrl($tenant),
                'logo_url' => $tenant->logo_url,
            ],
            'tenant_slug' => $tenant->slug,
        ], 201);
    }

    /**
     * Page de succès après approbation du vendeur.
     *
     * @return void
     */
    public function vendeurSuccess(Tenant $tenant)
    {
        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'raison_sociale' => $tenant->raison_sociale,
                'slug' => $tenant->slug,
                'url' => $this->vendorService->getShopUrl($tenant),
                'admin_url' => $this->vendorService->getVendeurUrl($tenant),
                'logo_url' => $tenant->logo_url,
            ],
        ]);
    }
}
