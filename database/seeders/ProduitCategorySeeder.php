<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProduitCategorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('fr_FR');

        // ==========================================
        // CATÉGORIES RACINES (niveau 1)
        // ==========================================
        $racines = [
            ['nom' => 'Vêtements', 'description' => 'Tous nos vêtements pour homme, femme et enfant'],
            ['nom' => 'Chaussures', 'description' => 'Chaussures de ville, sport, randonnée'],
            ['nom' => 'Accessoires', 'description' => 'Sacs, bijoux, ceintures et autres accessoires'],
            ['nom' => 'High-Tech', 'description' => 'Électronique, audio, informatique'],
            ['nom' => 'Maison & Déco', 'description' => 'Meubles, décoration, linge de maison'],
            ['nom' => 'Cuisine & Art de la table', 'description' => 'Ustensiles, vaisselle, électroménager'],
            ['nom' => 'Sport & Loisirs', 'description' => 'Équipement sportif, fitness, outdoor'],
            ['nom' => 'Beauté & Bien-être', 'description' => 'Cosmétiques, soins, parfums'],
            ['nom' => 'Alimentation & Épicerie', 'description' => 'Produits gourmands, bio, épicerie fine'],
            ['nom' => 'Bébé & Enfant', 'description' => 'Puériculture, jouets, vêtements enfant'],
            ['nom' => 'Animalerie', 'description' => 'Accessoires et alimentation pour animaux'],
            ['nom' => 'Jardin & Extérieur', 'description' => 'Mobilier de jardin, outils, plantes'],
            ['nom' => 'Auto & Moto', 'description' => 'Accessoires et entretien automobile'],
            ['nom' => 'Livres & Loisirs créatifs', 'description' => 'Livres, loisirs créatifs, papeterie'],
            ['nom' => 'Bagagerie', 'description' => 'Valises, sacs de voyage, bagages'],
        ];

        $categoriesCrees = [];

        // Créer les catégories racines
        foreach ($racines as $index => $catData) {
            $slug = Str::slug($catData['nom']);

            $categorie = ProductCategory::firstOrCreate(
                ['slug' => $slug],
                [
                    'nom' => $catData['nom'],
                    'description' => $catData['description'],
                    'short_description' => $faker->sentence(6),
                    'parente_id' => null,
                    'est_active' => true,
                    'is_featured' => $index < 5,
                    'show_in_menu' => true,
                    'order' => $index * 10,
                    'color' => $faker->hexColor(),
                    'seo_title' => $catData['nom'].' - Achetez en ligne',
                    'seo_description' => 'Découvrez notre sélection de '.strtolower($catData['nom']).'. Livraison rapide et gratuite.',
                    'seo_keywords' => explode(' ', strtolower($catData['nom'])),
                    'metadata' => ['niveau' => 1, 'type' => 'racine'],
                ]
            );

            $categoriesCrees[] = $categorie;
        }

        $this->command->info('15 catégories racines créées.');

        // ==========================================
        // SOUS-CATÉGORIES (niveau 2) - ~150 catégories
        // ==========================================
        $sousCategoriesMap = [
            'Vêtements' => [
                'Homme' => ['T-shirts', 'Chemises', 'Pulls', 'Pantalons', 'Jeans', 'Vestes', 'Manteaux', 'Costumes', 'Sous-vêtements', 'Pyjamas'],
                'Femme' => ['Robes', 'Jupes', 'Tops', 'Chemisiers', 'Pulls', 'Pantalons', 'Jeans', 'Vestes', 'Manteaux', 'Lingerie'],
                'Enfant' => ['Filles', 'Garçons', 'Bébés'],
                'Sportswear' => ['Running', 'Fitness', 'Yoga', 'Training'],
            ],
            'Chaussures' => [
                'Homme' => ['Baskets', 'Derbies', 'Bottes', 'Sandales', 'Chaussons', 'Chaussures de ville'],
                'Femme' => ['Escarpins', 'Baskets', 'Bottes', 'Sandales', 'Ballerines', 'Mocassins'],
                'Enfant' => ['Filles', 'Garçons', 'Bébés'],
                'Sport' => ['Running', 'Trail', 'Football', 'Basketball', 'Tennis'],
            ],
            'Accessoires' => [
                'Bijoux' => ['Colliers', 'Bagues', 'Bracelets', 'Boucles d\'oreilles', 'Montres'],
                'Sacs' => ['Sacs à main', 'Sacs à dos', 'Pochettes', 'Cabras'],
                'Ceintures', 'Lunettes', 'Chapeaux & Casquettes', 'Écharpes & Foulards', 'Gants',
            ],
            'High-Tech' => [
                'Audio' => ['Casques', 'Écouteurs', 'Enceintes', 'Amplificateurs'],
                'Informatique' => ['Ordinateurs', 'Tablettes', 'Composants', 'Périphériques', 'Stockage'],
                'Téléphonie' => ['Smartphones', 'Accessoires téléphone', 'Montres connectées'],
                'Photo & Vidéo' => ['Appareils photo', 'Objectifs', 'Accessoires photo'],
                'Gaming' => ['Consoles', 'Jeux vidéo', 'Accessoires gaming'],
            ],
            'Maison & Déco' => [
                'Meubles' => ['Salon', 'Chambre', 'Bureau', 'Salle à manger', 'Rangement'],
                'Décoration' => ['Luminaires', 'Miroirs', 'Horloges', 'Bougies', 'Vases', 'Cadres'],
                'Textile' => ['Coussins', 'Rideaux', 'Plaids', 'Tapis', 'Linge de lit'],
                'Salle de bain' => ['Serviettes', 'Tapis de bain', 'Accessoires salle de bain'],
            ],
            'Cuisine & Art de la table' => [
                'Ustensiles' => ['Couteaux', 'Planches', 'Batterie de cuisine'],
                'Vaisselle' => ['Assiettes', 'Bols', 'Verres', 'Tasses', 'Services complets'],
                'Électroménager' => ['Robot culinaire', 'Bouilloire', 'Cafetière', 'Grille-pain'],
                'Conservation' => ['Boîtes', 'Bocaux', 'Films alimentaires'],
            ],
            'Sport & Loisirs' => [
                'Fitness' => ['Haltères', 'Tapis de sol', 'Vélos d\'appartement'],
                'Sports collectifs' => ['Football', 'Basket', 'Rugby', 'Volley'],
                'Sports individuels' => ['Tennis', 'Badminton', 'Golf', 'Boxe'],
                'Outdoor' => ['Randonnée', 'Camping', 'Escalade', 'Pêche'],
                'Sports d\'hiver' => ['Ski', 'Snowboard', 'Raquettes'],
                'Nautisme' => ['Natation', 'Plongée', 'Surf', 'Paddle'],
            ],
            'Beauté & Bien-être' => [
                'Soins visage' => ['Crèmes', 'Nettoyants', 'Masques', 'Sérums'],
                'Soins corps' => ['Crèmes corps', 'Gommages', 'Huiles'],
                'Cheveux' => ['Shampoings', 'Après-shampoings', 'Masques', 'Coiffants'],
                'Maquillage' => ['Teint', 'Yeux', 'Lèvres', 'Ongles'],
                'Parfums' => ['Homme', 'Femme', 'Mixte'],
                'Bien-être' => ['Huiles essentielles', 'Diffuseurs', 'Bougies'],
            ],
            'Alimentation & Épicerie' => [
                'Épicerie salée' => ['Pâtes', 'Riz', 'Conserves', 'Condiments', 'Huiles'],
                'Épicerie sucrée' => ['Chocolats', 'Biscuits', 'Confitures', 'Miels'],
                'Boissons' => ['Café', 'Thé', 'Jus', 'Sodas', 'Alcools'],
                'Bio & Diététique' => ['Sans gluten', 'Vegan', 'Compléments alimentaires'],
            ],
            'Bébé & Enfant' => [
                'Puériculture' => ['Biberons', 'Tétines', 'Bavoirs'],
                'Éveil & Jouets' => ['Premier âge', 'Jeux d\'éveil', 'Peluches'],
                'Vêtements bébé' => ['Body', 'Pyjamas', 'Bonnets'],
                'Chambre bébé' => ['Mobiles', 'Veilleuses', 'Rangements'],
            ],
            'Animalerie' => [
                'Chiens' => ['Alimentation', 'Jouets', 'Accessoires'],
                'Chats' => ['Alimentation', 'Jouets', 'Litière', 'Arbres à chat'],
                'Petits animaux' => ['Rongeurs', 'Oiseaux', 'Poissons'],
            ],
            'Jardin & Extérieur' => [
                'Mobilier de jardin' => ['Tables', 'Chaises', 'Salons', 'Transats'],
                'Outils' => ['Tonte', 'Taille', 'Arrosage'],
                'Plantes & Semences' => ['Fleurs', 'Légumes', 'Arbustes'],
                'Décoration extérieure' => ['Fontaines', 'Statues', 'Éclairage'],
            ],
            'Auto & Moto' => [
                'Accessoires auto' => ['Tapis', 'Housses', 'Parfums'],
                'Entretien' => ['Lavage', 'Polish', 'Outils'],
                'Électronique' => ['GPS', 'Caméras', 'Autoradios'],
                'Moto' => ['Casques', 'Gants', 'Blousons'],
            ],
            'Livres & Loisirs créatifs' => [
                'Livres' => ['Romans', 'BD', 'Jeunesse', 'Cuisine', 'Développement personnel'],
                'Loisirs créatifs' => ['Peinture', 'Dessin', 'Scrapbooking', 'Couture'],
                'Papeterie' => ['Carnets', 'Stylos', 'Agendas'],
            ],
            'Bagagerie' => [
                'Valises' => ['Cabine', 'Moyennes', 'Grandes'],
                'Sacs de voyage' => ['Sacs week-end', 'Sacs de sport'],
                'Accessoires voyage' => ['Cadenas', 'Étiquettes', 'Oreillers'],
            ],
        ];

        $niveau2Categories = [];

        foreach ($sousCategoriesMap as $racineNom => $sousCategories) {
            $parent = ProductCategory::where('nom', $racineNom)->first();
            if (! $parent) {
                continue;
            }

            foreach ($sousCategories as $key => $value) {
                if (is_array($value)) {
                    // C'est un groupe avec des sous-sous-catégories
                    $nomGroupe = $key;
                    $sousSousCats = $value;

                    // Créer la catégorie de niveau 2 (groupe)
                    $slugNiveau2 = Str::slug($parent->slug.'-'.$nomGroupe);

                    $catNiveau2 = ProductCategory::firstOrCreate(
                        ['slug' => $slugNiveau2],
                        [
                            'nom' => $nomGroupe,
                            'description' => 'Découvrez notre collection '.strtolower($nomGroupe).' dans la catégorie '.$racineNom,
                            'short_description' => $faker->sentence(6),
                            'parente_id' => $parent->id,
                            'est_active' => true,
                            'is_featured' => $faker->boolean(30),
                            'show_in_menu' => true,
                            'order' => $faker->numberBetween(0, 100),
                            'color' => $faker->hexColor(),
                            'seo_title' => $nomGroupe.' '.$racineNom.' - Achetez en ligne',
                            'seo_description' => 'Large choix de '.strtolower($nomGroupe).'. '.$faker->sentence(10),
                            'seo_keywords' => [strtolower($nomGroupe), strtolower($racineNom)],
                            'metadata' => ['niveau' => 2, 'parent' => $racineNom],
                        ]
                    );

                    $niveau2Categories[] = $catNiveau2;

                    // Créer les catégories de niveau 3
                    foreach ($sousSousCats as $nomNiveau3) {
                        $slugNiveau3 = Str::slug($catNiveau2->slug.'-'.$nomNiveau3);

                        ProductCategory::firstOrCreate(
                            ['slug' => $slugNiveau3],
                            [
                                'nom' => $nomNiveau3,
                                'description' => $faker->sentence(15),
                                'short_description' => $faker->sentence(6),
                                'parente_id' => $catNiveau2->id,
                                'est_active' => $faker->boolean(90),
                                'is_featured' => $faker->boolean(20),
                            'show_in_menu' => $faker->boolean(80),
                            'order' => $faker->numberBetween(0, 100),
                            'color' => $faker->hexColor(),
                            'seo_title' => $nomNiveau3.' '.$nomGroupe.' - Meilleurs prix',
                            'seo_description' => $faker->text(150),
                            'seo_keywords' => [strtolower($nomNiveau3)],
                            'metadata' => ['niveau' => 3],
                        ]);
                    }
                } else {
                    // Catégorie simple de niveau 2
                    $nom = $value;
                    $slug = Str::slug($parent->slug.'-'.$nom);

                    ProductCategory::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'nom' => $nom,
                            'description' => $faker->sentence(15),
                            'short_description' => $faker->sentence(6),
                            'parente_id' => $parent->id,
                            'est_active' => true,
                            'is_featured' => $faker->boolean(30),
                            'show_in_menu' => true,
                            'order' => $faker->numberBetween(0, 100),
                            'color' => $faker->hexColor(),
                            'seo_title' => $nom.' '.$racineNom.' - Achetez en ligne',
                            'seo_description' => $faker->text(150),
                            'seo_keywords' => [strtolower($nom), strtolower($racineNom)],
                            'metadata' => ['niveau' => 2, 'parent' => $racineNom],
                        ]
                    );
                }
            }
        }

        $totalCategories = ProductCategory::count();
        $this->command->info("Total des catégories créées : {$totalCategories}");

        // ==========================================
        // COMPLÉTER JUSQU'À 500 CATÉGORIES
        // ==========================================
        // $categoriesNeeded = 500 - $totalCategories;

        // if ($categoriesNeeded > 0) {
        //     $this->command->info("Création de {$categoriesNeeded} catégories supplémentaires...");

        //     $niveaux = ['Tendance', 'Saisonnier', 'Promotions', 'Nouveautés', 'Best-sellers'];
        //     $marques = ['Nike', 'Adidas', 'Samsung', 'Apple', 'Sony', 'Dyson', 'Levis'];
        //     $styles = ['Vintage', 'Moderne', 'Classique', 'Design', 'Minimaliste', 'Bohème', 'Industriel'];

        //     $parentsExistants = ProductCategory::whereNotNull('parente_id')->pluck('id')->toArray();

        //     for ($i = 0; $i < $categoriesNeeded; $i++) {
        //         $type = $faker->randomElement(['niveau', 'marque', 'style', 'thematique']);

        //         if ($type === 'niveau' && ! empty($parentsExistants)) {
        //             $nom = $faker->randomElement($niveaux).' '.$faker->word();
        //             $parentId = $faker->randomElement($parentsExistants);
        //         } elseif ($type === 'marque') {
        //             $nom = $faker->randomElement($marques).' '.$faker->word();
        //             $parentId = $faker->randomElement($parentsExistants);
        //         } elseif ($type === 'style') {
        //             $nom = $faker->randomElement($styles).' '.$faker->word();
        //             $parentId = $faker->randomElement($parentsExistants);
        //         } else {
        //             $nom = ucfirst($faker->words(2, true));
        //             $parentId = $faker->randomElement($parentsExistants);
        //         }

        //         ProductCategory::create([

        //             'nom' => $nom,
        //             'slug' => Str::slug($nom.'-'.$faker->randomNumber(3)),
        //             'description' => $faker->paragraph(3),
        //             'short_description' => $faker->sentence(8),
        //             'parente_id' => $parentId,
        //             'est_active' => $faker->boolean(85),
        //             'is_featured' => $faker->boolean(15),
        //             'show_in_menu' => $faker->boolean(70),
        //             'order' => $faker->numberBetween(0, 200),
        //             'color' => $faker->hexColor(),
        //             'seo_title' => $nom.' - Qualité et prix bas',
        //             'seo_description' => $faker->text(150),
        //             'seo_keywords' => json_encode($faker->words(4)),
        //             'metadata' => json_encode([
        //                 'type' => $type,
        //                 'created_by' => 'seeder',
        //                 'timestamp' => now()->toDateTimeString(),
        //             ]),
        //         ]);
        //     }
        // }

        $finalCount = ProductCategory::count();
        $this->command->info("✅ {$finalCount} catégories créées avec succès !");
    }
}
