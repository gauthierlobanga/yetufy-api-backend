<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    private array $usedTitles = [];

    private array $usedSlugs = [];

    public function run(): void
    {
        if (User::count() === 0) {
            $this->command->error('Aucun utilisateur trouvé. Veuillez d\'abord créer des utilisateurs.');

            return;
        }

        $categoriesExistantes = PostCategory::count();
        if ($categoriesExistantes === 0) {
            $this->command->error('Aucune catégorie trouvée. Veuillez d\'abord exécuter BlogCategorySeeder.');

            return;
        }

        $faker = Factory::create('fr_FR');
        $userIds = User::pluck('id')->toArray();
        $categoryIds = PostCategory::pluck('id')->toArray();

        $this->command->info('IDs des catégories disponibles : '.implode(', ', array_slice($categoryIds, 0, 10)).'...');

        $statuts = ['draft', 'published', 'archived'];
        $poidsStatuts = [15, 75, 10];

        $getCategorySlice = function ($start, $length) use ($categoryIds) {
            return array_slice($categoryIds, $start, $length);
        };

        // ==========================================
        // POSTS DÉTAILLÉS - Thèmes uniques
        // ==========================================

        $themes = [
            'outdoor' => [
                'titles' => [
                    'Comment choisir sa veste outdoor éco-responsable',
                    'Randonnée : les indispensables pour débuter',
                    'Top 5 des sacs à dos pour le trekking',
                    'Camping sauvage : conseils et réglementation',
                    'Les meilleurs spots de randonnée en France',
                ],
                'tags' => ['outdoor', 'randonnée', 'nature', 'aventure'],
            ],
            'décoration' => [
                'titles' => [
                    'Les tendances déco 2026 : le retour du bois brut',
                    'Comment aménager un petit espace avec style',
                    'Couleurs tendance pour votre intérieur cette année',
                    'Le guide du minimalisme en décoration',
                    'DIY : créer une tête de lit originale',
                ],
                'tags' => ['décoration', 'design', 'maison', 'tendances'],
            ],
            'mode' => [
                'titles' => [
                    'Collection Capsule Automne 2026 : Inspiration Forêt Boréale',
                    'Comment composer une garde-robe éthique',
                    'Les matières éco-responsables à connaître',
                    'Le retour du vintage dans la mode contemporaine',
                    'Accessoires indispensables pour un look intemporel',
                ],
                'tags' => ['mode', 'style', 'éthique', 'tendances'],
            ],
            'cuisine' => [
                'titles' => [
                    '5 astuces pour entretenir vos couteaux de cuisine forgés',
                    'Recette : le gâteau au chocolat parfait',
                    'Les ustensiles indispensables pour cuisiner comme un chef',
                    'Comment choisir sa batterie de cuisine',
                    'Épices : comment bien les conserver',
                ],
                'tags' => ['cuisine', 'recette', 'gastronomie', 'ustensiles'],
            ],
            'café' => [
                'titles' => [
                    'Le guide ultime du café de spécialité pour débutants',
                    'Les secrets d\'un bon café filtre à la maison',
                    'Torréfaction : tout ce qu\'il faut savoir',
                    'Les différentes méthodes d\'extraction du café',
                    'Comment choisir son moulin à café',
                ],
                'tags' => ['café', 'torréfaction', 'guide', 'barista'],
            ],
            'tech' => [
                'titles' => [
                    'Pourquoi choisir des accessoires tech en bois plutôt qu\'en plastique',
                    'Les meilleurs casques audio pour le télétravail',
                    'Comment protéger sa vie privée en ligne',
                    'Guide d\'achat : quel ordinateur portable choisir',
                    'Les objets connectés indispensables pour la maison',
                ],
                'tags' => ['tech', 'high-tech', 'innovation', 'guide'],
            ],
            'bienêtre' => [
                'titles' => [
                    'Le yoga sur paddle : bienfaits et conseils pour débuter',
                    'Méditation : 10 minutes par jour pour changer votre vie',
                    'Les huiles essentielles pour se détendre',
                    'Comment créer une routine bien-être matinale',
                    'Sommeil : astuces pour mieux dormir naturellement',
                ],
                'tags' => ['bien-être', 'yoga', 'méditation', 'santé'],
            ],
            'écologie' => [
                'titles' => [
                    'Notre engagement pour une mode plus durable en 2026',
                    'Les labels à connaître pour consommer responsable',
                    'Zéro déchet : par où commencer',
                    'L\'impact environnemental du numérique',
                    'Comment réduire son empreinte carbone au quotidien',
                ],
                'tags' => ['écologie', 'développement durable', 'environnement', 'green'],
            ],
            'diy' => [
                'titles' => [
                    'Tutoriel : Fabriquez votre propre bougie parfumée naturelle',
                    'DIY : Créer un terrarium pour votre intérieur',
                    'Customiser ses vêtements : guide du débutant',
                    'Fabriquer ses produits ménagers écologiques',
                    'Atelier créatif : peinture sur céramique',
                ],
                'tags' => ['DIY', 'tutoriel', 'créatif', 'fait maison'],
            ],
            'interview' => [
                'titles' => [
                    'Interview : Rencontre avec Marie Dubois, créatrice de Lumina Atelier',
                    'Rencontre avec Thomas, artisan coutelier aux Forges de Mercure',
                    'Portrait : Julie Martin, céramiste passionnée',
                    'Dans les coulisses de l\'atelier de Sophie Lefèvre',
                    'Rencontre avec un designer engagé pour l\'environnement',
                ],
                'tags' => ['interview', 'portrait', 'artisan', 'créateur'],
            ],
            'événements' => [
                'titles' => [
                    'Retour sur le Salon Maison & Objet 2026',
                    'Les temps forts du Festival de Cannes 2026',
                    'Salon de l\'Agriculture : nos coups de cœur',
                    'Vivatech 2026 : les innovations à retenir',
                    'Paris Design Week : les expositions à ne pas manquer',
                ],
                'tags' => ['événement', 'salon', 'culture', 'tendances'],
            ],
            'business' => [
                'titles' => [
                    'Comment lancer sa boutique en ligne en 2026',
                    'Stratégies marketing pour les petites entreprises',
                    'L\'importance du personal branding',
                    'Gérer sa trésorerie : conseils pour entrepreneurs',
                    'Les erreurs à éviter quand on se lance en freelance',
                ],
                'tags' => ['business', 'entrepreneuriat', 'marketing', 'conseils'],
            ],
            'voyage' => [
                'titles' => [
                    'Les plus beaux road trips à faire en Europe',
                    'Voyager léger : l\'art du minimalisme en voyage',
                    'Destinations éco-responsables pour vos prochaines vacances',
                    'Comment voyager pas cher en 2026',
                    'Guide du voyageur solo : conseils et astuces',
                ],
                'tags' => ['voyage', 'tourisme', 'aventure', 'découverte'],
            ],
            'parentalité' => [
                'titles' => [
                    'Les essentiels pour accueillir bébé',
                    'Éducation positive : principes et bienfaits',
                    'Activités créatives à faire avec les enfants',
                    'Comment gérer les écrans avec les adolescents',
                    'Allaitement : conseils pour bien démarrer',
                ],
                'tags' => ['parentalité', 'famille', 'éducation', 'enfant'],
            ],
            'jardinage' => [
                'titles' => [
                    'Créer un potager sur son balcon',
                    'Les plantes d\'intérieur faciles d\'entretien',
                    'Calendrier du jardinier : que planter en avril',
                    'Permaculture : principes pour un jardin durable',
                    'Comment bouturer ses plantes correctement',
                ],
                'tags' => ['jardinage', 'plantes', 'potager', 'nature'],
            ],
        ];

        $postsDetail = [];
        $themeKeys = array_keys($themes);

        // Créer 10 posts détaillés avec des titres uniques
        for ($i = 0; $i < 10; $i++) {
            $themeKey = $themeKeys[$i % count($themeKeys)];
            $theme = $themes[$themeKey];

            // Prendre un titre non utilisé dans ce thème
            $availableTitles = $theme['titles'];
            $title = null;

            foreach ($availableTitles as $t) {
                if (! in_array($t, $this->usedTitles)) {
                    $title = $t;
                    $this->usedTitles[] = $t;
                    break;
                }
            }

            // Si tous les titres du thème sont utilisés, générer un titre unique
            if (! $title) {
                do {
                    $title = $this->generateUniqueTitle($faker, $themeKey);
                } while (in_array($title, $this->usedTitles));
                $this->usedTitles[] = $title;
            }

            $postsDetail[] = [
                'title' => $title,
                'excerpt' => ['text' => $this->generateUniqueExcerpt($faker, $title)],
                'content' => ['body' => $this->generateLongContent($faker)],
                'tags' => $theme['tags'],
                'categories' => $getCategorySlice($i % 7, 2 + ($i % 2)),
            ];
        }

        $postsCrees = 0;

        foreach ($postsDetail as $index => $postData) {
            $this->createPost($postData, $userIds, $faker, $categoryIds, $index < 2);
            $postsCrees++;
        }

        // ==========================================
        // CRÉATION DES POSTS RESTANTS (jusqu'à 30)
        // ==========================================

        $postsRestants = 30 - $postsCrees;

        $prefixes = [
            'Comment', 'Pourquoi', 'Guide', 'Les meilleurs', 'Top 10', 'Découvrez',
            'Focus sur', 'Tout savoir sur', 'Les secrets de', 'Notre sélection',
            'Analyse', 'Comprendre', 'Maîtriser', 'Explorer', 'Optimiser',
            'Les bases de', 'Introduction à', 'Perfectionner', 'L\'essentiel sur',
        ];

        $sujets = [
            'la mode éthique', 'le design scandinave', 'les matériaux durables',
            'la décoration minimaliste', 'les accessoires indispensables',
            'les tendances actuelles', 'l\'artisanat local', 'la consommation responsable',
            'les innovations tech', 'le bien-être au naturel', 'la cuisine végétarienne',
            'le développement personnel', 'la photographie mobile', 'le marketing digital',
            'la gestion du temps', 'la productivité', 'le leadership', 'la créativité',
            'l\'intelligence artificielle', 'la cybersécurité', 'le e-commerce',
            'les réseaux sociaux', 'le growth hacking', 'la data science',
        ];

        for ($i = 0; $i < $postsRestants; $i++) {
            $statut = $this->getRandomWeightedElement($statuts, $poidsStatuts);

            // Générer un titre unique
            do {
                $prefix = $faker->randomElement($prefixes);
                $sujet = $faker->randomElement($sujets);

                if ($faker->boolean(30)) {
                    $title = $faker->sentence(rand(4, 8));
                } else {
                    $title = $prefix.' '.$sujet;
                    if ($faker->boolean(20)) {
                        $title .= ' en '.date('Y');
                    }
                }
                $title = ucfirst(rtrim($title, '.'));
            } while (in_array($title, $this->usedTitles));

            $this->usedTitles[] = $title;

            $numCategories = $faker->numberBetween(1, 3);
            $categories = $faker->randomElements($categoryIds, $numCategories);

            $postData = [
                'title' => $title,
                'excerpt' => ['text' => $this->generateUniqueExcerpt($faker, $title)],
                'content' => ['body' => $this->generateRandomContent($faker)],
                'tags' => $faker->words(rand(3, 6)),
                'categories' => $categories,
            ];

            $this->createPost($postData, $userIds, $faker, $categoryIds, $statut === 'published' && $faker->boolean(15), $statut);
            $postsCrees++;
        }

        $this->command->info("✅ {$postsCrees} articles de blog créées avec succès !");
        $this->command->info('   - Titres uniques : '.count($this->usedTitles));

        $publies = Post::where('status', 'published')->count();
        $brouillons = Post::where('status', 'draft')->count();
        $archives = Post::where('status', 'archived')->count();

        $this->command->info("   - Articles publiés : {$publies}");
        $this->command->info("   - Articles en brouillon : {$brouillons}");
        $this->command->info("   - Articles archivés : {$archives}");
    }

    /**
     * Génère un titre unique
     */
    private function generateUniqueTitle($faker, string $theme): string
    {
        $patterns = [
            'outdoor' => ['Randonnée en {location}', 'Guide outdoor {year}', 'Équipement {activity}'],
            'décoration' => ['Déco {style} pour {room}', 'Tendances déco {year}', 'Inspiration {style}'],
            'mode' => ['Look {style} pour {season}', 'Tendances mode {year}', 'Comment porter {item}'],
            'cuisine' => ['Recette de {dish}', 'Astuces cuisine : {tip}', 'Ustensiles : {item}'],
            'café' => ['Café : {method} parfaite', 'Origine {origin} décryptée', 'Guide café {year}'],
        ];

        $pattern = $patterns[$theme][array_rand($patterns[$theme])] ?? $faker->sentence(5);

        $replacements = [
            '{location}' => $faker->country,
            '{year}' => date('Y'),
            '{activity}' => $faker->randomElement(['randonnée', 'trail', 'camping', 'escalade']),
            '{style}' => $faker->randomElement(['scandinave', 'minimaliste', 'industriel', 'bohème']),
            '{room}' => $faker->randomElement(['salon', 'chambre', 'cuisine', 'bureau']),
            '{season}' => $faker->randomElement(['printemps', 'été', 'automne', 'hiver']),
            '{item}' => $faker->randomElement(['jeans', 'robe', 'veste', 'chaussures']),
            '{dish}' => $faker->randomElement(['pâtes', 'gâteau', 'soupe', 'salade']),
            '{tip}' => $faker->randomElement(['découpe', 'cuisson', 'assaisonnement', 'conservation']),
            '{method}' => $faker->randomElement(['V60', 'Chemex', 'Aeropress', 'French Press']),
            '{origin}' => $faker->randomElement(['Éthiopie', 'Colombie', 'Kenya', 'Brésil']),
        ];

        return ucfirst(str_replace(array_keys($replacements), array_values($replacements), $pattern));
    }

    /**
     * Génère un extrait unique
     */
    private function generateUniqueExcerpt($faker, string $title): string
    {
        $intros = [
            'Découvrez dans cet article',
            'Guide complet sur',
            'Tout ce qu\'il faut savoir sur',
            'Analyse détaillée de',
            'Nos conseils d\'experts sur',
            'Plongez dans l\'univers de',
            'Explorez avec nous',
        ];

        $intro = $faker->randomElement($intros);
        $subject = str_replace(['Comment ', 'Pourquoi ', 'Guide ', 'Les '], '', $title);
        $subject = strtolower($subject);

        return $intro.' '.$subject.'. '.$faker->sentence(8);
    }

    /**
     * Crée un post avec ses relations
     */
    private function createPost(array $data, array $userIds, $faker, array $categoryIds, bool $isPinned = false, ?string $forcedStatus = null): void
    {
        $userId = $faker->randomElement($userIds);
        $status = $forcedStatus ?? $data['status'] ?? 'published';

        $publishedAt = null;
        $scheduledFor = null;
        $expiresAt = null;

        if ($status === 'published') {
            $publishedAt = Carbon::now()->subDays($faker->numberBetween(0, 90));
        } elseif ($status === 'draft' && $faker->boolean(20)) {
            $scheduledFor = Carbon::now()->addDays($faker->numberBetween(1, 30));
        } elseif ($status === 'archived') {
            $publishedAt = Carbon::now()->subMonths($faker->numberBetween(6, 24));
            $expiresAt = Carbon::now()->subMonths($faker->numberBetween(1, 3));
        }

        // Générer un slug unique
        do {
            $slug = Str::slug($data['title']);
            if (in_array($slug, $this->usedSlugs)) {
                $slug .= '-'.$faker->randomNumber(3);
            }
        } while (in_array($slug, $this->usedSlugs) || Post::where('slug', $slug)->exists());
        $this->usedSlugs[] = $slug;

        $post = Post::firstOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $userId,
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'metadata' => [
                    'author_bio' => $faker->sentence(10),
                    'difficulty' => $faker->randomElement(['Débutant', 'Intermédiaire', 'Avancé']),
                ],
                'status' => $status,
                'is_pinned' => $isPinned,
                'views_count' => $status === 'published' ? $faker->numberBetween(50, 50000) : 0,
                'likes_count' => $status === 'published' ? $faker->numberBetween(5, 200) : 0,
                'comments_count' => $status === 'published' ? $faker->numberBetween(0, 50) : 0,
                'meta_title' => $data['title'].' | Blog',
                'meta_description' => Str::limit($data['excerpt']['text'] ?? '', 160),
                'meta_keywords' => $data['tags'],
                'published_at' => $publishedAt,
                'scheduled_for' => $scheduledFor,
                'expires_at' => $expiresAt,
            ]
        );

        if (! empty($data['tags'])) {
            $post->attachTags($data['tags']);
        }

        if (! empty($data['categories'])) {
            $syncData = [];
            foreach ($data['categories'] as $index => $categoryId) {
                $syncData[$categoryId] = [
                    'est_principale' => ($index === 0),
                    'ordre' => $index,
                ];
            }
            $post->categories()->sync($syncData);
        }
    }

    private function generateLongContent($faker): string
    {
        $paragraphs = [];
        $numParagraphs = rand(8, 15);

        $paragraphs[] = '<p class="lead">'.$faker->paragraph(3).'</p>';

        for ($i = 0; $i < $numParagraphs; $i++) {
            if ($i % 3 === 0 && $i > 0) {
                $paragraphs[] = '<h2>'.$faker->sentence(rand(4, 7)).'</h2>';
            }
            if ($i % 4 === 0 && $i > 0) {
                $paragraphs[] = '<h3>'.$faker->sentence(rand(4, 6)).'</h3>';
            }

            $paragraphs[] = '<p>'.$faker->paragraph(rand(3, 8)).'</p>';

            if ($i % 5 === 0) {
                $listItems = [];
                for ($j = 0; $j < rand(3, 6); $j++) {
                    $listItems[] = '<li>'.$faker->sentence(rand(6, 12)).'</li>';
                }
                $paragraphs[] = '<ul>'.implode('', $listItems).'</ul>';
            }

            if ($i % 7 === 0) {
                $paragraphs[] = '<blockquote><p>'.$faker->sentence(rand(10, 20)).'</p><cite>'.$faker->name().'</cite></blockquote>';
            }
        }

        $paragraphs[] = '<h2>Conclusion</h2>';
        $paragraphs[] = '<p>'.$faker->paragraph(4).'</p>';

        return implode("\n\n", $paragraphs);
    }

    private function generateRandomContent($faker): string
    {
        $paragraphs = [];
        $numParagraphs = rand(5, 12);

        for ($i = 0; $i < $numParagraphs; $i++) {
            if ($i % 4 === 0 && $i > 0) {
                $paragraphs[] = '<h2>'.$faker->sentence(rand(4, 7)).'</h2>';
            }
            $paragraphs[] = '<p>'.$faker->paragraph(rand(3, 7)).'</p>';
        }

        return implode("\n\n", $paragraphs);
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
