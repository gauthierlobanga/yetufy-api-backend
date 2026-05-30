<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\Produit;
use App\Models\VarianteProduit;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Brand::count() === 0) {
            $this->command->error('Aucune marque trouvée. Veuillez d\'abord exécuter BrandSeeder.');

            return;
        }
        if (ProductCategory::count() === 0) {
            $this->command->error('Aucune catégorie trouvée. Veuillez d\'abord exécuter ProductCategorySeeder.');

            return;
        }

        $brands = Brand::orderBy('created_at')->get();
        if ($brands->count() < 5) {
            $this->command->error('Il faut au moins 5 marques pour ce seeder.');

            return;
        }

        $brandIds = [
            1 => $brands[0]->id,
            2 => $brands[1]->id,
            3 => $brands[2]->id,
            4 => $brands[3]->id,
            5 => $brands[4]->id,
        ];

        $categoryIds = ProductCategory::pluck('id')->toArray();
        $this->command->info('IDs des catégories disponibles : '.implode(', ', array_slice($categoryIds, 0, 10)).'...');

        $faker = Factory::create('fr_FR');

        // ==========================================
        // Produits détaillés
        // ==========================================
        $produits = [
            [
                'nom' => 'Veste Polaire Boreal Trail',
                'slug' => 'veste-polaire-boreal-trail',
                'reference' => 'BRL-POL-TRL-01',
                'sku' => 'BRL-POL-M-NOIR',
                'ean' => '3701234567891',
                'brand_id' => $brandIds[1],
                'short_description' => 'Veste polaire technique respirante pour la randonnée rapide.',
                'description_longue' => 'La veste Boreal Trail est conçue pour les efforts intenses en montagne. Son tissu Polartec® Microgrid évacue l\'humidité tout en conservant la chaleur. Coupe ajustée, zip intégral et poche poitrine.',
                'statut' => 'publie',
                'published_at' => Carbon::now()->subDays(10),
                'scheduled_for' => null,
                'expires_at' => null,
                'is_featured' => true,
                'is_new' => true,
                'is_bestseller' => false,
                'prix_ht' => 79.99,
                'prix_ttc' => 96.00,
                'prix_promotion' => null,
                'quantite_stock' => 45,
                'seuil_alerte' => 10,
                'poids' => 0.45,
                'hauteur' => null,
                'largeur' => null,
                'profondeur' => null,
                'unite_mesure' => 'cm',
                'seo_title' => 'Veste Polaire Technique Boreal Trail | Randonnée',
                'seo_keywords' => ['polaire', 'randonnée', 'outdoor', 'écologique', 'respirant'],
                'seo_description' => 'Veste polaire légère Boreal Trail. Fabriquée en tissu recyclé.',
                'metadata' => [
                    'matiere' => 'Polartec® Microgrid 100% recyclé',
                    'coupe' => 'Ajustée / Athletic Fit',
                    'entretien' => 'Lavage 30°C',
                ],
                'attributes' => [
                    'Genre' => 'Homme',
                    'Saison' => 'Automne/Hiver',
                    'Manches' => 'Longues',
                ],
                'categories' => array_slice($categoryIds, 0, 2),
                'tags' => ['homme', 'polaire', 'recyclé', 'montagne'],
                'variantes' => [
                    ['nom' => 'Taille', 'valeur' => 'M', 'supplement_prix' => 0, 'stock' => 15, 'sku_variante' => 'BRL-POL-M-NOIR'],
                    ['nom' => 'Taille', 'valeur' => 'L', 'supplement_prix' => 0, 'stock' => 20, 'sku_variante' => 'BRL-POL-L-NOIR'],
                    ['nom' => 'Taille', 'valeur' => 'XL', 'supplement_prix' => 0, 'stock' => 10, 'sku_variante' => 'BRL-POL-XL-NOIR'],
                ],
            ],
            [
                'nom' => 'Bague Argent Minimaliste Lumen',
                'slug' => 'bague-argent-lumen',
                'reference' => 'LUM-AG-LUM-01',
                'sku' => 'LUM-BAGUE-ARG-54',
                'ean' => '3701234567907',
                'brand_id' => $brandIds[2],
                'short_description' => 'Bague fine en argent recyclé, martelée à la main.',
                'description_longue' => 'La bague Lumen capture la lumière avec sa finition martelée unique. Réalisée en argent 925 recyclé dans notre atelier lyonnais.',
                'statut' => 'publie',
                'published_at' => Carbon::now()->subDays(5),
                'scheduled_for' => null,
                'expires_at' => null,
                'is_featured' => true,
                'is_new' => false,
                'is_bestseller' => true,
                'prix_ht' => 49.00,
                'prix_ttc' => 58.80,
                'prix_promotion' => 45.00,
                'quantite_stock' => 8,
                'seuil_alerte' => 2,
                'poids' => 0.003,
                'hauteur' => null,
                'largeur' => null,
                'profondeur' => null,
                'unite_mesure' => 'cm',
                'seo_title' => 'Bague Argent Lumen | Bijoux Minimaliste Lyon',
                'seo_keywords' => ['bague', 'argent', 'minimaliste', 'fait main', 'éthique'],
                'seo_description' => 'Bague en argent recyclé martelé à la main. Livraison offerte.',
                'metadata' => ['matiere' => 'Argent 925 Recyclé', 'fabrication' => 'Lyon, France'],
                'attributes' => ['Genre' => 'Femme', 'Pierre' => 'Sans pierre'],
                'categories' => array_slice($categoryIds, 2, 2),
                'tags' => ['bague', 'argent', 'minimaliste', 'cadeau'],
                'variantes' => [
                    ['nom' => 'Taille', 'valeur' => '52', 'supplement_prix' => 0, 'stock' => 2, 'sku_variante' => 'LUM-BAGUE-ARG-52'],
                    ['nom' => 'Taille', 'valeur' => '54', 'supplement_prix' => 0, 'stock' => 3, 'sku_variante' => 'LUM-BAGUE-ARG-54'],
                    ['nom' => 'Taille', 'valeur' => '56', 'supplement_prix' => 0, 'stock' => 3, 'sku_variante' => 'LUM-BAGUE-ARG-56'],
                ],
            ],
            [
                'nom' => 'Casque Audio Aether H1',
                'slug' => 'casque-audio-aether-h1',
                'reference' => 'AETH-H1-NOIR',
                'sku' => 'AETH-H1-BLK',
                'ean' => '3701234567914',
                'brand_id' => $brandIds[3],
                'short_description' => 'Casque circum-auriculaire sans fil avec réduction de bruit active hybride.',
                'description_longue' => 'Le Aether H1 offre une expérience sonore immersive grâce à ses haut-parleurs de 40mm et à la réduction de bruit active.',
                'statut' => 'publie',
                'published_at' => Carbon::now()->subDays(15),
                'scheduled_for' => null,
                'expires_at' => null,
                'is_featured' => true,
                'is_new' => true,
                'is_bestseller' => false,
                'prix_ht' => 199.99,
                'prix_ttc' => 240.00,
                'prix_promotion' => null,
                'quantite_stock' => 30,
                'seuil_alerte' => 5,
                'poids' => 0.250,
                'hauteur' => 19.5,
                'largeur' => 17.0,
                'profondeur' => 8.0,
                'unite_mesure' => 'cm',
                'seo_title' => 'Casque Aether H1 | Réduction de Bruit Active',
                'seo_keywords' => ['casque', 'ANC', 'Bluetooth', 'audio', 'sans fil'],
                'seo_description' => 'Casque sans fil Aether H1 avec réduction de bruit. Son haute-fidélité, autonomie 35h.',
                'metadata' => ['connectivite' => 'Bluetooth 5.3, Jack 3.5mm', 'autonomie' => '35h (ANC on)'],
                'attributes' => ['Couleur' => 'Noir', 'Type' => 'Casque fermé'],
                'categories' => array_slice($categoryIds, 4, 2),
                'tags' => ['audio', 'bluetooth', 'anc', 'musique'],
                'variantes' => [],
            ],
            [
                'nom' => 'Table Basse Onda Chêne Massif',
                'slug' => 'table-basse-onda-chene',
                'reference' => 'COB-ONDA-CH-01',
                'sku' => 'COB-TB-CHN-120',
                'ean' => '3701234567921',
                'brand_id' => $brandIds[4],
                'short_description' => 'Table basse design en chêne massif et acier brossé.',
                'description_longue' => 'La table Onda allie la chaleur du bois à la rigueur de l\'acier. Son plateau en chêne massif certifié FSC est soutenu par une structure géométrique épurée.',
                'statut' => 'publie',
                'published_at' => Carbon::now()->subDays(20),
                'scheduled_for' => null,
                'expires_at' => null,
                'is_featured' => true,
                'is_new' => false,
                'is_bestseller' => true,
                'prix_ht' => 490.00,
                'prix_ttc' => 588.00,
                'prix_promotion' => null,
                'quantite_stock' => 4,
                'seuil_alerte' => 1,
                'poids' => 18.5,
                'hauteur' => 40.0,
                'largeur' => 120.0,
                'profondeur' => 60.0,
                'unite_mesure' => 'cm',
                'seo_title' => 'Table Basse Design Onda | Studio Cobalt',
                'seo_keywords' => ['table basse', 'design', 'chêne massif', 'meuble', 'scandinave'],
                'seo_description' => 'Table basse en chêne massif et acier brossé. Design contemporain pour salon moderne.',
                'metadata' => ['materiaux' => 'Chêne FSC, Acier thermolaqué'],
                'attributes' => ['Forme' => 'Rectangulaire', 'Pieds' => 'Acier'],
                'categories' => array_slice($categoryIds, 6, 2),
                'tags' => ['table', 'design', 'scandinave', 'bois'],
                'variantes' => [],
            ],
            [
                'nom' => 'Café Grains Éthiopie Sidamo Bio',
                'slug' => 'cafe-grains-ethiopie-sidamo',
                'reference' => 'ECUME-ETH-SID-250',
                'sku' => 'ECUME-ETH-250G',
                'ean' => '3701234567938',
                'brand_id' => $brandIds[5],
                'short_description' => 'Café d\'Éthiopie Sidamo, notes florales et agrumes.',
                'description_longue' => 'Découvrez la finesse de ce Grand Cru d\'Éthiopie. Cultivé entre 1800 et 2200m d\'altitude.',
                'statut' => 'publie',
                'published_at' => Carbon::now()->subDays(3),
                'scheduled_for' => null,
                'expires_at' => null,
                'is_featured' => false,
                'is_new' => true,
                'is_bestseller' => false,
                'prix_ht' => 11.90,
                'prix_ttc' => 14.28,
                'prix_promotion' => null,
                'quantite_stock' => 120,
                'seuil_alerte' => 20,
                'poids' => 0.250,
                'hauteur' => null,
                'largeur' => null,
                'profondeur' => null,
                'unite_mesure' => 'kg',
                'seo_title' => 'Café Éthiopie Sidamo Bio | Torréfaction L\'Écume',
                'seo_keywords' => ['café', 'éthiopie', 'sidamo', 'bio'],
                'seo_description' => 'Café de spécialité Éthiopie Sidamo. Notes de jasmin et agrumes.',
                'metadata' => ['origine' => 'Sidamo, Éthiopie', 'process' => 'Lavé'],
                'attributes' => ['Torréfaction' => 'Claire', 'Score SCA' => '87'],
                'categories' => array_slice($categoryIds, 8, 2),
                'tags' => ['café', 'ethiopie', 'bio'],
                'variantes' => [
                    ['nom' => 'Mouture', 'valeur' => 'Grains', 'supplement_prix' => 0, 'stock' => 80, 'sku_variante' => 'ECUME-ETH-250G'],
                    ['nom' => 'Mouture', 'valeur' => 'Expresso', 'supplement_prix' => 0, 'stock' => 20, 'sku_variante' => 'ECUME-ETH-250G-ESP'],
                    ['nom' => 'Mouture', 'valeur' => 'Filtre', 'supplement_prix' => 0, 'stock' => 20, 'sku_variante' => 'ECUME-ETH-250G-FIL'],
                ],
            ],
            [
                'nom' => 'Enceinte Bluetooth Portable Solaris',
                'slug' => 'enceinte-bluetooth-portable-solaris',
                'reference' => 'SOL-ENC-BT-01',
                'sku' => 'SOL-ENC-BT',
                'ean' => '3701234567990',
                'brand_id' => $brandIds[1],
                'short_description' => 'Enceinte Bluetooth portable avec panneau solaire intégré.',
                'description_longue' => 'L\'enceinte Solaris se recharge au soleil. Étanche IPX7, 20h d\'autonomie.',
                'statut' => 'brouillon',
                'published_at' => null,
                'scheduled_for' => null,
                'expires_at' => null,
                'is_featured' => false,
                'is_new' => false,
                'is_bestseller' => false,
                'prix_ht' => 89.99,
                'prix_ttc' => 107.99,
                'prix_promotion' => null,
                'quantite_stock' => 0,
                'seuil_alerte' => 5,
                'poids' => 0.550,
                'hauteur' => 8.0,
                'largeur' => 18.0,
                'profondeur' => 8.0,
                'unite_mesure' => 'cm',
                'seo_title' => 'Enceinte Solaire Portable Solaris',
                'seo_keywords' => ['enceinte', 'bluetooth', 'solaire', 'outdoor'],
                'seo_description' => 'Enceinte Bluetooth à recharge solaire. Étanche et robuste.',
                'metadata' => ['batterie' => '5000mAh', 'etancheite' => 'IPX7'],
                'attributes' => [],
                'categories' => [$categoryIds[4] ?? $categoryIds[0]],
                'tags' => ['audio', 'bluetooth', 'outdoor'],
                'variantes' => [],
            ],
            [
                'nom' => 'Lampe de Bureau LED Architecte',
                'slug' => 'lampe-bureau-led-architecte',
                'reference' => 'LUM-LED-ARC-01',
                'sku' => 'LUM-LED-ARC',
                'ean' => '3701234568003',
                'brand_id' => $brandIds[2],
                'short_description' => 'Lampe de bureau LED orientable design.',
                'description_longue' => 'Lampe d\'architecte moderne avec variateur d\'intensité.',
                'statut' => 'archive',
                'published_at' => Carbon::now()->subMonths(6),
                'scheduled_for' => null,
                'expires_at' => Carbon::now()->subMonths(1),
                'is_featured' => false,
                'is_new' => false,
                'is_bestseller' => false,
                'prix_ht' => 129.99,
                'prix_ttc' => 155.99,
                'prix_promotion' => null,
                'quantite_stock' => 0,
                'seuil_alerte' => 0,
                'poids' => 2.5,
                'hauteur' => 45.0,
                'largeur' => 20.0,
                'profondeur' => 20.0,
                'unite_mesure' => 'cm',
                'seo_title' => 'Lampe Bureau LED Architecte',
                'seo_keywords' => ['lampe', 'bureau', 'LED', 'design'],
                'seo_description' => 'Lampe de bureau LED orientable design moderne.',
                'metadata' => ['puissance' => '12W', 'luminosite' => '800lm'],
                'attributes' => ['Couleur' => 'Noir mat'],
                'categories' => array_slice($categoryIds, 6, 2),
                'tags' => ['lampe', 'bureau', 'led'],
                'variantes' => [],
            ],
        ];

        foreach ($produits as $produitData) {
            $this->createProduit($produitData);
        }

        // ==========================================
        // Produits aléatoires
        // ==========================================
        $allBrandIds = Brand::pluck('id')->toArray();
        $nomsProduits = [
            'T-shirt', 'Pantalon', 'Veste', 'Pull', 'Chemise', 'Robe', 'Jupe', 'Short',
            'Manteau', 'Blouson', 'Sweat', 'Débardeur', 'Chaussures', 'Bottes', 'Sandales',
            'Sac', 'Portefeuille', 'Ceinture', 'Lunettes', 'Montre', 'Bracelet', 'Collier',
            'Boucles d\'oreilles', 'Lampe', 'Vase', 'Miroir', 'Tapis', 'Coussin', 'Rideau',
            'Casserole', 'Poêle', 'Couteau', 'Planche à découper', 'Enceinte', 'Chargeur',
            'Câble', 'Support', 'Housse', 'Valise', 'Tente', 'Sac de couchage', 'Gourde',
            'Bouteille', 'Mug', 'Assiette', 'Bol', 'Verre', 'Couvert', 'Plat',
        ];
        $adjectifs = ['Premium', 'Deluxe', 'Classic', 'Modern', 'Vintage', 'Eco', 'Pro', 'Light', 'Max', 'Mini'];
        $statutsValides = ['brouillon', 'publie', 'archive'];
        $poidsStatuts = [15, 80, 5];

        $produitsRestants = 50 - count($produits);
        for ($i = 0; $i < $produitsRestants; $i++) {
            $nom = $faker->randomElement($adjectifs).' '.$faker->randomElement($nomsProduits);
            $brandId = $faker->randomElement($allBrandIds);
            $categories = $faker->randomElements($categoryIds, $faker->numberBetween(1, 3));
            $statut = $this->getRandomWeightedElement($statutsValides, $poidsStatuts);
            $prixHt = $faker->randomFloat(2, 5, 800);

            $produitData = [
                'nom' => ucfirst($nom),
                'slug' => Str::slug($nom.'-'.$faker->randomNumber(3)),
                'reference' => strtoupper(Str::random(8)),
                'sku' => strtoupper(Str::random(10)),
                'ean' => $faker->ean13(),
                'brand_id' => $brandId,
                'short_description' => $faker->sentence(10),
                'description_longue' => $faker->paragraph(4),
                'statut' => $statut,
                'published_at' => $statut === 'publie' ? Carbon::now()->subDays($faker->numberBetween(1, 60)) : null,
                'scheduled_for' => null,
                'expires_at' => $statut === 'archive' ? Carbon::now()->subDays($faker->numberBetween(1, 30)) : null,
                'is_featured' => $statut === 'publie' ? $faker->boolean(20) : false,
                'is_new' => $statut === 'publie' ? $faker->boolean(30) : false,
                'is_bestseller' => $statut === 'publie' ? $faker->boolean(10) : false,
                'prix_ht' => $prixHt,
                'prix_ttc' => round($prixHt * 1.20, 2),
                'prix_promotion' => $faker->optional(0.2)->randomFloat(2, $prixHt * 0.6, $prixHt * 0.9),
                'quantite_stock' => $statut === 'archive' ? 0 : $faker->numberBetween(0, 100),
                'seuil_alerte' => 5,
                'poids' => $faker->randomFloat(3, 0.05, 25),
                'hauteur' => $faker->optional(0.7)->randomFloat(1, 5, 200),
                'largeur' => $faker->optional(0.7)->randomFloat(1, 5, 200),
                'profondeur' => $faker->optional(0.7)->randomFloat(1, 5, 200),
                'unite_mesure' => 'cm',
                'seo_title' => ucfirst($nom).' | Achat en ligne',
                'seo_keywords' => $faker->words(5),
                'seo_description' => substr($faker->text(200), 0, 255),
                'metadata' => ['couleur' => $faker->colorName],
                'attributes' => [],
                'categories' => $categories,
                'tags' => $faker->words(3),
                'variantes' => [],
            ];
            $this->createProduit($produitData);
        }

        $totalProduits = Produit::count();
        $this->command->info("✅ {$totalProduits} produits créés avec succès !");
        $this->command->info('   - Produits publiés : '.Produit::where('statut', 'publie')->count());
        $this->command->info('   - Produits en brouillon : '.Produit::where('statut', 'brouillon')->count());
        $this->command->info('   - Produits archivés : '.Produit::where('statut', 'archive')->count());
    }

    /**
     * Crée un produit avec ses relations.
     */
    private function createProduit(array $data): Produit
    {
        $categories = $data['categories'] ?? [];
        $tags = $data['tags'] ?? [];
        $variantes = $data['variantes'] ?? [];

        unset($data['categories'], $data['tags'], $data['variantes']);

        // Valeurs par défaut
        $data['vues'] = 0;
        $data['views_count'] = 0;
        $data['sold_count'] = 0;
        $data['average_rating'] = 0;
        $data['reviews_count'] = 0;

        // Création du produit
        $produit = Produit::firstOrCreate(
            ['slug' => $data['slug']],
            $data
        );

        // Attacher les catégories via la table pivot
        if (! empty($categories)) {
            $pivotData = [];
            foreach ($categories as $index => $categoryId) {
                if (ProductCategory::where('id', $categoryId)->exists()) {
                    $pivotData[] = [
                        'produit_id' => $produit->id,
                        'category_id' => $categoryId,
                        'is_primary' => $index === 0,
                        'order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (! empty($pivotData)) {
                DB::table('produit_categorie_pivot')->insertOrIgnore($pivotData);
            }
        }

        // Attacher les tags (si le modèle utilise Spatie\Tags\HasTags)
        if (! empty($tags) && method_exists($produit, 'attachTags')) {
            $produit->attachTags($tags);
        }

        // Créer les variantes
        if (! empty($variantes) && method_exists($produit, 'variantes')) {
            foreach ($variantes as $varianteData) {
                $varianteData['produit_id'] = $produit->id;
                VarianteProduit::firstOrCreate(
                    ['sku_variante' => $varianteData['sku_variante']],
                    $varianteData
                );
            }
        }

        return $produit;
    }

    private function getRandomWeightedElement(array $elements, array $weights): mixed
    {
        $totalWeight = array_sum($weights);
        $rand = mt_rand(1, $totalWeight);
        $cumulativeWeight = 0;
        foreach ($elements as $index => $element) {
            $cumulativeWeight += $weights[$index];
            if ($rand <= $cumulativeWeight) {
                return $element;
            }
        }

        return $elements[0];
    }
}
