<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('fr_FR');

        // ==========================================
        // CATÉGORIES RACINES (niveau 1)
        // ==========================================

        $racines = [
            ['nom' => 'Actualités', 'description' => 'Toutes les actualités et nouveautés'],
            ['nom' => 'Guides & Conseils', 'description' => 'Guides pratiques et conseils d\'experts'],
            ['nom' => 'Tendances', 'description' => 'Les dernières tendances et inspirations'],
            ['nom' => 'Produits', 'description' => 'Focus sur nos produits et collections'],
            ['nom' => 'Événements', 'description' => 'Événements, salons et rencontres'],
            ['nom' => 'Tutoriels', 'description' => 'Tutoriels et DIY'],
            ['nom' => 'Interviews', 'description' => 'Rencontres avec des créateurs et experts'],
            ['nom' => 'Développement Durable', 'description' => 'Engagements et initiatives éco-responsables'],
        ];

        foreach ($racines as $index => $catData) {
            $slug = Str::slug($catData['nom']);

            PostCategory::firstOrCreate(
                ['slug' => $slug],
                [
                    'parent_id' => null,
                    'nom' => $catData['nom'],
                    'description' => $catData['description'],
                    'color' => $faker->hexColor(),
                    'metadata' => ['niveau' => 1, 'type' => 'racine'],
                    'ordre' => $index * 10,
                    'est_active' => true,
                    'est_visible_dans_menu' => true,
                    'meta_title' => $catData['nom'].' - Blog',
                    'meta_description' => 'Découvrez tous nos articles sur '.strtolower($catData['nom']),
                    'meta_keywords' => explode(' ', strtolower($catData['nom'])),
                ]
            );
        }

        $this->command->info('✅ 8 catégories racines créées.');

        // ==========================================
        // SOUS-CATÉGORIES (niveau 2)
        // ==========================================

        $sousCategoriesMap = [
            'Actualités' => ['Nouveautés', 'Annonces', 'Partenariats', 'Dans les médias'],
            'Guides & Conseils' => ['Guide d\'achat', 'Entretien', 'Utilisation', 'Comparatifs', 'Astuces'],
            'Tendances' => ['Mode', 'Décoration', 'Tech', 'Lifestyle', 'Couleurs'],
            'Produits' => ['Collections', 'Nouveautés', 'Best-sellers', 'Éditions limitées', 'Collaborations'],
            'Événements' => ['Salons', 'Pop-up stores', 'Webinaires', 'Ateliers'],
            'Tutoriels' => ['DIY', 'Vidéo', 'Débutants', 'Avancés'],
            'Interviews' => ['Créateurs', 'Artisans', 'Experts', 'Clients'],
            'Développement Durable' => ['Éco-conception', 'Recyclage', 'Circuit court', 'Labels & Certifications'],
        ];

        foreach ($sousCategoriesMap as $racineNom => $sousCategories) {
            $parent = PostCategory::where('nom', $racineNom)->first();
            if (! $parent) {
                continue;
            }

            foreach ($sousCategories as $index => $nomSousCat) {
                $slug = Str::slug($parent->slug.'-'.$nomSousCat);

                PostCategory::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'parent_id' => $parent->id,
                        'nom' => $nomSousCat,
                        'description' => $faker->sentence(10),
                        'color' => $faker->hexColor(),
                        'metadata' => ['niveau' => 2, 'parent' => $racineNom],
                        'ordre' => $index * 5,
                        'est_active' => true,
                        'est_visible_dans_menu' => $faker->boolean(80),
                        'meta_title' => $nomSousCat.' - '.$racineNom,
                        'meta_description' => $faker->text(150),
                        'meta_keywords' => array_merge(
                            explode(' ', strtolower($nomSousCat)),
                            explode(' ', strtolower($racineNom))
                        ),
                    ]
                );
            }
        }

        // ==========================================
        // SOUS-SOUS-CATÉGORIES (niveau 3)
        // ==========================================

        $niveau3Map = [
            'Mode' => ['Streetwear', 'Minimaliste', 'Bohème', 'Vintage', 'Business'],
            'Décoration' => ['Scandinave', 'Industriel', 'Japonais', 'Méditerranéen'],
            'Collections' => ['Printemps/Été', 'Automne/Hiver', 'Capsule', 'Permanente'],
            'Guide d\'achat' => ['Débutants', 'Experts', 'Cadeaux', 'Petit budget'],
        ];

        foreach ($niveau3Map as $parentNom => $sousCategories) {
            $parent = PostCategory::where('nom', $parentNom)->first();
            if (! $parent) {
                continue;
            }

            foreach ($sousCategories as $index => $nomSousCat) {
                PostCategory::firstOrCreate(
                    ['slug' => Str::slug($parent->slug.'-'.$nomSousCat)],
                    [
                        'parent_id' => $parent->id,
                        'nom' => $nomSousCat,
                        'description' => $faker->sentence(8),
                        'color' => $parent->color,
                        'metadata' => ['niveau' => 3],
                        'ordre' => $index * 3,
                        'est_active' => true,
                        'est_visible_dans_menu' => false,
                        'meta_title' => $nomSousCat.' - Blog',
                        'meta_description' => $faker->text(120),
                        'meta_keywords' => $faker->words(3),
                    ]
                );
            }
        }

        // ==========================================
        // CATÉGORIES SUPPLÉMENTAIRES POUR ATTEINDRE ~50
        // ==========================================

        $parentsExistants = PostCategory::whereNotNull('parent_id')->pluck('id')->toArray();
        $motsCles = ['Digital', 'Éthique', 'Premium', 'Local', 'Artisanal', 'Innovation', 'Tradition', 'Futur', 'Saison', 'Spécial'];
        $sujets = ['Focus', 'Dossier', 'Sélection', 'Découverte', 'Rencontre', 'Portrait', 'Analyse', 'Reportage'];

        $categoriesExistantes = PostCategory::count();
        $categoriesNeeded = 50 - $categoriesExistantes;

        for ($i = 0; $i < $categoriesNeeded; $i++) {
            $parentId = $faker->randomElement($parentsExistants);
            $parent = PostCategory::find($parentId);

            $nom = $faker->randomElement($motsCles).' '.$faker->randomElement($sujets);

            PostCategory::create([
                'parent_id' => $parentId,
                'nom' => $nom,
                'slug' => Str::slug($parent->slug.'-'.$nom.'-'.Str::random(4)),
                'description' => $faker->sentence(12),
                'color' => $faker->hexColor(),
                'metadata' => ['niveau' => 3, 'auto_generated' => true],
                'ordre' => $faker->numberBetween(0, 50),
                'est_active' => $faker->boolean(90),
                'est_visible_dans_menu' => $faker->boolean(30),
                'meta_title' => $nom.' - Articles et conseils',
                'meta_description' => $faker->text(140),
                'meta_keywords' => $faker->words(5),
            ]);
        }

        $totalCategories = PostCategory::count();
        $this->command->info("✅ {$totalCategories} catégories de blog créées avec succès !");
    }
}
