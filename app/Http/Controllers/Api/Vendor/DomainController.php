<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VendorRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DomainController extends Controller
{
    public function __construct(
        private readonly VendorRegistrationService $vendorService
    ) {}

    /**
     * Vérifier la disponibilité d'un slug de domaine.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => ['required', 'string', 'min:3', 'max:63'],
        ]);

        $slug = Str::lower(trim($request->input('slug')));

        // Valider le format
        $formatErrors = $this->vendorService->validateSlugFormat($slug);

        if (! empty($formatErrors)) {
            return response()->json([
                'available' => false,
                'errors' => $formatErrors,
                'cleaned_slug' => $this->cleanSlug($slug),
            ]);
        }

        // Nettoyer le slug automatiquement
        $cleanedSlug = $this->cleanSlug($slug);

        // Vérifier la disponibilité
        $isAvailable = $this->vendorService->isShopSlugAvailable($cleanedSlug);

        // Suggérer des alternatives si indisponible
        $suggestions = [];
        if (! $isAvailable) {
            $suggestions = $this->vendorService->suggestAlternativeSlugs($cleanedSlug, 5);
        }

        return response()->json([
            'available' => $isAvailable,
            'cleaned_slug' => $cleanedSlug,
            'suggestions' => $suggestions,
            'errors' => [],
        ]);
    }

    /**
     * Suggérer des noms de domaine à partir d'un nom de boutique.
     */
    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'shop_name' => ['required', 'string', 'min:2'],
        ]);

        $shopName = $request->input('shop_name');
        $baseSlug = $this->generateBaseSlug($shopName);

        // Générer plusieurs variantes
        $variants = $this->generateVariants($baseSlug);

        // Vérifier la disponibilité de chaque variante
        $suggestions = [];
        foreach ($variants as $variant) {
            if ($this->vendorService->isShopSlugAvailable($variant)) {
                $suggestions[] = [
                    'slug' => $variant,
                    'available' => true,
                    'domain' => $variant.'.'.config('app.domain'),
                ];

                // Limiter à 5 suggestions disponibles
                if (count($suggestions) >= 5) {
                    break;
                }
            }
        }

        // Si moins de 5 suggestions disponibles, ajouter des variantes avec préfixes/suffixes
        if (count($suggestions) < 3) {
            $extendedVariants = $this->generateExtendedVariants($baseSlug, 5 - count($suggestions));
            foreach ($extendedVariants as $variant) {
                if ($this->vendorService->isShopSlugAvailable($variant) &&
                    ! collect($suggestions)->pluck('slug')->contains($variant)) {
                    $suggestions[] = [
                        'slug' => $variant,
                        'available' => true,
                        'domain' => $variant.'.'.config('app.domain'),
                    ];
                }
            }
        }

        return response()->json([
            'base_slug' => $baseSlug,
            'suggestions' => array_slice($suggestions, 0, 5),
        ]);
    }

    /**
     * Générer un slug de base à partir du nom de la boutique.
     */
    private function generateBaseSlug(string $name): string
    {
        // Supprimer les mots courants inutiles
        $stopWords = ['la', 'le', 'les', 'des', 'de', 'du', 'mon', 'ma', 'mes', 'notre', 'nos', 'votre', 'vos'];
        $words = explode(' ', Str::lower($name));
        $filteredWords = array_diff($words, $stopWords);

        // Si tous les mots sont filtrés, utiliser le nom complet
        if (empty($filteredWords)) {
            $filteredWords = $words;
        }

        $baseSlug = Str::slug(implode('-', $filteredWords));

        // Si le slug est trop court, utiliser tout le nom
        if (strlen($baseSlug) < 3) {
            $baseSlug = Str::slug($name);
        }

        return $this->cleanSlug($baseSlug);
    }

    /**
     * Générer des variantes de slug.
     */
    private function generateVariants(string $baseSlug): array
    {
        $variants = [$baseSlug];

        // Sans mots vides
        $variants[] = $this->removeStopWords($baseSlug);

        // Avec des préfixes courants
        $prefixes = ['shop', 'store', 'boutique', 'mon', 'my', 'the'];
        foreach ($prefixes as $prefix) {
            $variants[] = $prefix.'-'.$baseSlug;
            $variants[] = $prefix.$baseSlug;
        }

        // Avec des suffixes courants
        $suffixes = ['shop', 'store', 'boutique', 'online', 'cd', 'rdc', 'congo'];
        foreach ($suffixes as $suffix) {
            $variants[] = $baseSlug.'-'.$suffix;
        }

        // Abréviations (premières lettres de chaque mot)
        $words = explode('-', $baseSlug);
        if (count($words) >= 3) {
            $abbr = '';
            foreach ($words as $word) {
                if (! empty($word)) {
                    $abbr .= $word[0];
                }
            }
            if (strlen($abbr) >= 3) {
                $variants[] = $abbr;
            }
        }

        return array_unique(array_filter($variants, fn ($v) => strlen($v) >= 3));
    }

    /**
     * Générer des variantes étendues avec des nombres.
     */
    private function generateExtendedVariants(string $baseSlug, int $count): array
    {
        $variants = [];
        $attempt = 0;

        while (count($variants) < $count && $attempt < 50) {
            $attempt++;

            if ($attempt <= 5) {
                $variants[] = $baseSlug.'-'.rand(1, 99);
            } elseif ($attempt <= 15) {
                $variants[] = $baseSlug.'-'.Str::random(4);
            } else {
                $adjectives = ['pro', 'plus', 'max', 'best', 'top', 'elite', 'premium'];
                $variants[] = $adjectives[array_rand($adjectives)].'-'.$baseSlug;
            }
        }

        return array_unique($variants);
    }

    /**
     * Nettoyer un slug (enlever les caractères non autorisés).
     */
    private function cleanSlug(string $slug): string
    {
        // Normaliser (enlever les accents)
        $slug = Str::ascii($slug);

        // Ne garder que les caractères autorisés
        $slug = preg_replace('/[^a-z0-9-]/', '', Str::lower($slug));

        // Supprimer les tirets au début et à la fin
        $slug = trim($slug, '-');

        // Remplacer les tirets multiples par un seul
        $slug = preg_replace('/-+/', '-', $slug);

        return $slug;
    }

    /**
     * Supprimer les mots vides d'un slug.
     */
    private function removeStopWords(string $slug): string
    {
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with'];
        $parts = explode('-', $slug);
        $filtered = array_diff($parts, $stopWords);

        if (empty($filtered)) {
            return $slug;
        }

        return implode('-', $filtered);
    }
}
