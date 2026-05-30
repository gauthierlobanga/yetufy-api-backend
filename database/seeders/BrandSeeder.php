<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            // Mode & Accessoires
            [

                'name' => 'Boreal',
                'slug' => 'boreal',
                'website' => 'boreal-nature.com',
                'email' => 'contact@boreal-nature.com',
                'phone' => '+33140506070',
                'color' => '#2E5D3A', // Vert forêt
                'sort_order' => 10,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Marque française de vêtements outdoor éco-responsables. Matériaux recyclés et production locale.',
                'seo' => ['title' => 'Boreal - Vêtements Outdoor Écologiques', 'description' => 'Découvrez Boreal, la marque de vêtements techniques et durables pour la randonnée et le trail.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/borealoutdoor'],
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/borealnature'],
                ],
                'metadata' => ['fondation' => 2018, 'origine' => 'Annecy, France'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Lumina Atelier',
                'slug' => 'lumina-atelier',
                'website' => 'lumina-atelier.fr',
                'email' => 'bonjour@lumina-atelier.fr',
                'phone' => '+33478910112',
                'color' => '#D4AF37', // Or
                'sort_order' => 20,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Bijoux minimalistes et intemporels fabriqués à la main à Lyon. Or recyclé et pierres éthiques.',
                'seo' => ['title' => 'Lumina Atelier - Bijoux Minimalistes Français', 'description' => 'Créations uniques en or recyclé. Colliers, bagues et bracelets dessinés et fabriqués en France.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/lumina.atelier'],
                    ['platform' => 'pinterest', 'url' => 'https://pinterest.com/luminaatelier'],
                ],
                'metadata' => ['artisan' => 'Marie Dubois', 'materiaux' => 'Or 18k recyclé'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Studio Cobalt',
                'slug' => 'studio-cobalt',
                'website' => 'studiocobalt.design',
                'email' => 'hello@studiocobalt.design',
                'phone' => '+33142865544',
                'color' => '#0047AB', // Bleu Cobalt
                'sort_order' => 15,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Éditeur de mobilier contemporain. Design sobre, bois massif et acier. Pièces modulables pour espaces de vie modernes.',
                'seo' => ['title' => 'Studio Cobalt - Mobilier Design Contemporain', 'description' => 'Tables, chaises et rangements au design épuré. Fabrication européenne et matériaux nobles.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/studiocobalt'],
                    ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/studio-cobalt'],
                ],
                'metadata' => ['designer' => 'Thomas Meyer', 'style' => 'Scandinave Moderne'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Le Chanvrier',
                'slug' => 'le-chanvrier',
                'website' => 'lechanvrier-bio.com',
                'email' => 'service@lechanvrier-bio.com',
                'phone' => '+33231445566',
                'color' => '#A9CBB7', // Vert sauge
                'sort_order' => 30,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Textiles en chanvre bio cultivé en Normandie. Linge de maison, sacs et accessoires durables et hypoallergéniques.',
                'seo' => ['title' => 'Le Chanvrier - Textiles Bio en Chanvre Français', 'description' => 'Linge de maison écologique et résistant fabriqué à partir de chanvre cultivé sans pesticides en France.'],
                'social_links' => [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/lechanvrier'],
                ],
                'metadata' => ['culture' => 'Normandie', 'label' => 'Bio Cohérence'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Velvet Racer',
                'slug' => 'velvet-racer',
                'website' => 'velvetracer.shop',
                'email' => 'support@velvetracer.shop',
                'phone' => '+44161234567',
                'color' => '#8B0000', // Rouge foncé
                'sort_order' => 40,
                'is_active' => false, // Inactif pour tester
                'is_featured' => false,
                'description' => 'Accessoires et vêtements inspirés de l\'âge d\'or de l\'automobile. Cuir, casques vintage et lunettes rétro.',
                'seo' => ['title' => 'Velvet Racer - Style Automobile Vintage', 'description' => 'Équipement et vêtements pour passionnés de voitures anciennes. Blousons en cuir, gants de pilotage et accessoires.'],
                'social_links' => [],
                'metadata' => ['univers' => 'Café Racer / Vintage Auto'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Tech & Électronique
            [

                'name' => 'Aether Lab',
                'slug' => 'aether-lab',
                'website' => 'aether-lab.tech',
                'email' => 'contact@aether-lab.tech',
                'phone' => '+33750102030',
                'color' => '#0A0A0A', // Noir profond
                'sort_order' => 5,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Start-up française spécialisée dans les accessoires audio haute-fidélité et la domotique minimaliste.',
                'seo' => ['title' => 'Aether Lab - Audio Haute-Fidélité et Domotique', 'description' => 'Casques nomades, enceintes connectées et objets connectés au design sobre et à la technologie avancée.'],
                'social_links' => [
                    ['platform' => 'twitter', 'url' => 'https://twitter.com/aether_lab'],
                    ['platform' => 'youtube', 'url' => 'https://youtube.com/@aetherlab'],
                ],
                'metadata' => ['technologies' => 'Bluetooth 5.3, ANC, LDAC'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Pixel & Kraft',
                'slug' => 'pixel-and-kraft',
                'website' => 'pixelandkraft.com',
                'email' => 'hello@pixelandkraft.com',
                'phone' => '+493012345678',
                'color' => '#F5A623', // Orange
                'sort_order' => 25,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Coques d\'iPhone, coques de MacBook et accessoires de bureau en bois véritable et cuir pleine fleur. Mariage du numérique et de l\'artisanat.',
                'seo' => ['title' => 'Pixel & Kraft - Accessoires Tech en Bois et Cuir', 'description' => 'Protection élégante pour vos appareils Apple. Housses en cuir, coques en bois gravé et supports d\'ordinateur.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/pixelandkraft'],
                ],
                'metadata' => ['materiaux' => 'Noyer, Chêne, Cuir pleine fleur'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Solaris Charge',
                'slug' => 'solaris-charge',
                'website' => 'solaris-charge.com',
                'email' => 'info@solaris-charge.com',
                'phone' => '+33170605040',
                'color' => '#FFD700', // Jaune Solaire
                'sort_order' => 50,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Solutions de recharge solaire nomades. Batteries externes, panneaux pliables et sacs à dos photovoltaïques pour l\'aventure.',
                'seo' => ['title' => 'Solaris Charge - Énergie Solaire Nomade', 'description' => 'Rechargez vos appareils électroniques partout grâce à nos panneaux solaires haute performance. Énergie propre et mobilité.'],
                'social_links' => [
                    ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/solaris-charge'],
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/solaris.charge'],
                ],
                'metadata' => ['puissance' => '21W, 40W, 100W', 'certifications' => 'IP65, CE, RoHS'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Maison & Déco
            [

                'name' => 'Argile & Mer',
                'slug' => 'argile-et-mer',
                'website' => 'argile-et-mer.fr',
                'email' => 'contact@argile-et-mer.fr',
                'phone' => '+33298451236',
                'color' => '#4A6FA5', // Bleu océan
                'sort_order' => 35,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Céramiques artisanales de Bretagne. Vaisselle en grès et porcelaine aux teintes inspirées du littoral atlantique.',
                'seo' => ['title' => 'Argile & Mer - Céramique Artisanale Bretonne', 'description' => 'Assiettes, bols et vases tournés à la main dans notre atelier du Finistère. Pièces uniques ou petites séries.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/argileetmer'],
                    ['platform' => 'tiktok', 'url' => 'https://tiktok.com/@argileetmer'],
                ],
                'metadata' => ['atelier' => 'Douarnenez', 'cuisson' => 'Haute température 1280°C'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Papier Mûrier',
                'slug' => 'papier-murier',
                'website' => 'papiermurier-paris.com',
                'email' => 'bonjour@papiermurier-paris.com',
                'phone' => '+33143210987',
                'color' => '#E8D5B7', // Beige Papier
                'sort_order' => 45,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Papiers peints d\'art et tentures murales fabriqués à la main. Motifs botaniques, géométriques et fresques panoramiques.',
                'seo' => ['title' => 'Papier Mûrier - Papiers Peints d\'Art et Tentures', 'description' => 'Sublimez vos murs avec nos papiers peints haut de gamme. Fabrication artisanale et encres écologiques.'],
                'social_links' => [
                    ['platform' => 'pinterest', 'url' => 'https://pinterest.com/papiermurier'],
                ],
                'metadata' => ['type' => 'Intissé, Vinyle, Panoramique'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'La Savonnerie des Alpes',
                'slug' => 'savonnerie-des-alpes',
                'website' => 'savonnerie-alpes.fr',
                'email' => 'savon@savonnerie-alpes.fr',
                'phone' => '+33450010203',
                'color' => '#9B59B6', // Violet Lavande
                'sort_order' => 60,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Savons saponifiés à froid, shampoings solides et cosmétiques bio. Ingrédients locaux des Alpes : miel, lavande, génépi.',
                'seo' => ['title' => 'Savonnerie des Alpes - Cosmétiques Bio et Savons Artisanaux', 'description' => 'Savons surgras naturels fabriqués en Haute-Savoie. Zéro déchet, vegan et cruelty-free.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/savonnerie.alpes'],
                ],
                'metadata' => ['label' => 'Slow Cosmétique', 'poids' => '100g'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sport & Plein Air
            [

                'name' => 'Altitude',
                'slug' => 'altitude-equipment',
                'website' => 'altitude-equipment.com',
                'email' => 'expedition@altitude-equipment.com',
                'phone' => '+33479253647',
                'color' => '#E67E22', // Orange Montagne
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Équipement technique pour l\'alpinisme et la haute montagne. Vêtements, sacs à dos et tentes conçus pour conditions extrêmes.',
                'seo' => ['title' => 'Altitude - Équipement d\'Alpinisme Haute Montagne', 'description' => 'Matériel technique pour alpinistes exigeants. Duvets, vestes Gore-Tex Pro et tentes 4 saisons.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/altitude_equipment'],
                    ['platform' => 'youtube', 'url' => 'https://youtube.com/@altitudeteam'],
                ],
                'metadata' => ['activites' => 'Alpinisme, Cascade de glace, Expédition'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Rivage Paddle',
                'slug' => 'rivage-paddle',
                'website' => 'rivage-paddle.co',
                'email' => 'aloha@rivage-paddle.co',
                'phone' => '+33240998877',
                'color' => '#3498DB', // Bleu océan
                'sort_order' => 70,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Paddles gonflables, kayaks et accessoires pour les sports de pagaie. Loisir, randonnée et yoga sur l\'eau.',
                'seo' => ['title' => 'Rivage Paddle - Stand Up Paddle et Kayak Gonflable', 'description' => 'Explorez les côtes et les rivières avec nos planches de SUP gonflables haut de gamme. Stables, légères et durables.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/rivage.paddle'],
                    ['platform' => 'tiktok', 'url' => 'https://tiktok.com/@rivagepaddle'],
                ],
                'metadata' => ['technologie' => 'Drop Stitch Double Couche'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Alimentation & Épicerie Fine
            [

                'name' => 'Torréfaction L\'Écume',
                'slug' => 'torrefaction-lecume',
                'website' => 'cafe-lecume.fr',
                'email' => 'grains@cafe-lecume.fr',
                'phone' => '+33297554433',
                'color' => '#3E2723', // Brun Café
                'sort_order' => 80,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Cafés de spécialité torréfiés artisanalement à Saint-Malo. Sélection de grands crus et cafés de plantation.',
                'seo' => ['title' => 'Torréfaction L\'Écume - Café de Spécialité Breton', 'description' => 'Découvrez nos cafés fraîchement torréfiés. Expresso, filtres et méthodes douces. Livraison en grains ou moulu.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/lecume.cafe'],
                ],
                'metadata' => ['origine' => 'Éthiopie, Colombie, Guatemala', 'score_sca' => '85+'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Le Chai Nomade',
                'slug' => 'le-chai-nomade',
                'website' => 'lechainomade.com',
                'email' => 'cave@lechainomade.com',
                'phone' => '+33556451234',
                'color' => '#800020', // Bordeaux
                'sort_order' => 90,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Caviste en ligne spécialisé dans les vins nature, biodynamiques et les pépites de vignerons indépendants.',
                'seo' => ['title' => 'Le Chai Nomade - Vins Nature et Biodynamie', 'description' => 'Sélection pointue de vins vivants. Livraison de cartons personnalisés et conseils d\'accords mets et vins.'],
                'social_links' => [
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/lechainomade'],
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/lechainomade'],
                ],
                'metadata' => ['vignerons' => 'Partenaire de 45 domaines'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Grands noms fictifs pour compléter
            [

                'name' => 'Nexus Gear',
                'slug' => 'nexus-gear',
                'website' => 'nexus-gear.io',
                'email' => 'support@nexus-gear.io',
                'phone' => '+41445067890',
                'color' => '#2C3E50',
                'sort_order' => 100,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Accessoires gaming premium. Claviers mécaniques, souris ultra-légères et tapis de souris XXL.',
                'seo' => ['title' => 'Nexus Gear - Périphériques Gaming Premium', 'description' => 'Équipez-vous comme un pro. Matériel e-sport performant et personnalisable.'],
                'social_links' => [['platform' => 'twitch', 'url' => 'https://twitch.tv/nexusgear']],
                'metadata' => ['switches' => 'Cherry MX, Gateron, Kailh'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Mimosa',
                'slug' => 'mimosa-parfums',
                'website' => 'mimosa-parfums.fr',
                'email' => 'client@mimosa-parfums.fr',
                'phone' => '+33493123456',
                'color' => '#FADADD',
                'sort_order' => 110,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Parfumerie de niche grasse. Bougies parfumées et fragrances d\'ambiance inspirées du Sud de la France.',
                'seo' => ['title' => 'Mimosa - Parfums d\'Ambiance de Grasse', 'description' => 'Voyagez en Provence avec nos bougies artisanales aux senteurs de fleur d\'oranger, figuier et lavande.'],
                'social_links' => [['platform' => 'instagram', 'url' => 'https://instagram.com/mimosa.parfums']],
                'metadata' => ['cire' => 'Végétale (Coco/Colza)', 'duree' => '50h'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Atelier Guimauve',
                'slug' => 'atelier-guimauve',
                'website' => 'atelier-guimauve.fr',
                'email' => 'gourmandises@atelier-guimauve.fr',
                'phone' => '+33147852369',
                'color' => '#FFB6C1',
                'sort_order' => 120,
                'is_active' => false, // Inactif
                'is_featured' => false,
                'description' => 'Confiserie artisanale spécialisée dans la guimauve naturelle. Parfums originaux sans colorants artificiels.',
                'seo' => ['title' => 'Atelier Guimauve - Guimauves Artisanales Naturelles', 'description' => 'Guimauves moelleuses à la vanille, framboise, citron vert ou chocolat. Fabrication française et ingrédients bio.'],
                'social_links' => [],
                'metadata' => ['conservation' => '3 mois'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Les Forges de Mercure',
                'slug' => 'forges-de-mercure',
                'website' => 'forgesdemercure.com',
                'email' => 'outils@forgesdemercure.com',
                'phone' => '+33254987456',
                'color' => '#7F8C8D',
                'sort_order' => 130,
                'is_active' => true,
                'is_featured' => false,
                'description' => 'Fabrication française de couteaux de cuisine forgés. Lames en acier damas et manches en bois précieux.',
                'seo' => ['title' => 'Forges de Mercure - Couteaux de Cuisine Forgés Français', 'description' => 'Couteaux de chef, office et santoku fabriqués à la main dans la tradition coutelière française.'],
                'social_links' => [['platform' => 'youtube', 'url' => 'https://youtube.com/@forgesdemercure']],
                'metadata' => ['acier' => 'XC75, Damas 67 couches'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'name' => 'Komorebi',
                'slug' => 'komorebi-lifestyle',
                'website' => 'komorebi-lifestyle.com',
                'email' => 'hello@komorebi-lifestyle.com',
                'phone' => '+81312345678',
                'color' => '#6B8E23',
                'sort_order' => 140,
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Inspirations japonaises pour la maison. Vaisselle, théières en fonte et objets déco zen. Wabi-sabi et minimalisme.',
                'seo' => ['title' => 'Komorebi - Art de Vivre et Déco Japonaise', 'description' => 'Importez la sérénité chez vous. Bols matcha, baguettes, futons et lampes en papier washi.'],
                'social_links' => [['platform' => 'instagram', 'url' => 'https://instagram.com/komorebi.life']],
                'metadata' => ['style' => 'Japonisme, Wabi-sabi'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($brands as $brandData) {
            // Utiliser firstOrCreate pour éviter les duplications
            Brand::firstOrCreate(
                ['slug' => $brandData['slug']],
                $brandData
            );
        }
    }
}
