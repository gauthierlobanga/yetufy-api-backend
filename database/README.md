# 💾 Base de Données - `database/`

Ce dossier contient les migrations, seeders et factories pour la base de données.

## 📁 Structure

```
database/
├── migrations/           # Migrations (versioning BD)
├── seeders/             # Données d'initialisation
├── factories/           # Factories pour tests
└── .gitignore
```

## 📝 Migrations

Les migrations gèrent le versioning de la structure de base de données.

### Fichier de Migration Type

```bash
# Créer une migration
php artisan make:migration create_products_table

# Fichier généré: database/migrations/2026_06_01_120000_create_products_table.php
```

**Structure d'une Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->foreignUuid('shop_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### Commandes Migrations

```bash
# Exécuter toutes les migrations
php artisan migrate

# Migrer un fichier spécifique
php artisan migrate --path=database/migrations/2026_06_01_120000_create_products_table.php

# Annuler la dernière migration
php artisan migrate:rollback

# Annuler toutes les migrations
php artisan migrate:reset

# Rafraîchir (reset + migrate)
php artisan migrate:refresh

# Rafraîchir + seed
php artisan migrate:refresh --seed

# Voir le statut des migrations
php artisan migrate:status

# Migrer en production (force)
php artisan migrate --force
```

### Types de Colonnes Courants

```php
// Clés primaires
$table->id();                    // Auto-incrémentée
$table->uuid('id');              // UUID

// Types de base
$table->string('name');          // VARCHAR(255)
$table->string('email', 100);    // VARCHAR(100)
$table->text('description');     // TEXT
$table->integer('count');        // INT
$table->decimal('price', 8, 2);  // DECIMAL(8,2)
$table->boolean('is_active');    // BOOLEAN

// Dates
$table->timestamp('created_at');
$table->datetime('published_at');
$table->timestamps();            // created_at + updated_at
$table->softDeletes();           // deleted_at

// Relations
$table->foreignId('user_id')->constrained();
$table->foreignUuid('shop_id')->constrained('shops');
$table->foreign('category_id')->references('id')->on('categories');

// JSON
$table->json('metadata');
$table->jsonb('settings');       // PostgreSQL

// Indexation
$table->unique('email');
$table->index('status');
$table->fullText('content');
```

## 🌱 Seeders

Les seeders remplissent la base de données avec des données d'initialisation.

### Créer un Seeder

```bash
# Seeder simple
php artisan make:seeder ProductSeeder

# Seeder avec factory
php artisan make:seeder ProductSeeder --factory
```

**Exemple de Seeder:**

```php
<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Méthode 1: Créer en masse
        Product::factory(50)->create();

        // Méthode 2: Créer avec données spécifiques
        Product::create([
            'name' => 'Produit 1',
            'price' => 99.99,
        ]);

        // Méthode 3: Boucle
        foreach (range(1, 10) as $i) {
            Product::create([
                'name' => "Produit {$i}",
                'price' => rand(10, 1000),
            ]);
        }
    }
}
```

### Lancer les Seeders

```bash
# Tous les seeders
php artisan db:seed

# Seeder spécifique
php artisan db:seed --class=ProductSeeder

# Seeder + migrations
php artisan migrate:fresh --seed

# Sans interaction de confirmation
php artisan migrate:fresh --seed --force
```

### DatabaseSeeder Principal

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        ShopSeeder::class,
        ProductSeeder::class,
        CategorySeeder::class,
    ]);
}
```

## 🏭 Factories

Les factories génèrent des données fictives pour les tests.

### Créer une Factory

```bash
php artisan make:factory ProductFactory
```

**Exemple de Factory:**

```php
<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(10, 1000),
            'is_published' => true,
        ];
    }

    // État personnalisé
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->numberBetween(1000, 10000),
        ]);
    }
}
```

### Utiliser les Factories

```php
// Dans les tests
use App\Models\Product;

// Créer une instance
$product = Product::factory()->create();

// Créer plusieurs
$products = Product::factory(5)->create();

// Utiliser un état personnalisé
$draft = Product::factory()->draft()->create();

// Combiner états
$expensive = Product::factory()->expensive()->create();

// Avec relations
$shop = Shop::factory()
    ->has(Product::factory(5))
    ->create();

// Instance sans sauvegarder
$product = Product::factory()->make();
```

## 📊 Modèles de Base de Données

### Utilisateurs

```sql
CREATE TABLE users (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    email_verified_at TIMESTAMP,
    provider VARCHAR(50),        -- google, facebook, etc
    provider_id VARCHAR(255),    -- ID from provider
    avatar VARCHAR(255),         -- URL avatar
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### Boutiques (Tenants)

```sql
CREATE TABLE shops (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    domain VARCHAR(255) UNIQUE,
    owner_id UUID REFERENCES users(id),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Produits

```sql
CREATE TABLE products (
    id UUID PRIMARY KEY,
    shop_id UUID REFERENCES shops(id),
    category_id UUID REFERENCES categories(id),
    name VARCHAR(255),
    slug VARCHAR(255),
    description TEXT,
    price DECIMAL(8,2),
    is_published BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### Commandes

```sql
CREATE TABLE orders (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES users(id),
    shop_id UUID REFERENCES shops(id),
    status VARCHAR(50),           -- pending, processing, completed
    total DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🔄 Stratégies de Migration

### Forward-Only Migrations (Production)

```php
// ✅ Bon - compatible
public function up(): void {
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable();
    });
}

public function down(): void {
    // Pas de down() en production
}
```

### Migrations avec Relations

```php
// ✅ Considérer l'ordre d'exécution
// 1. Créer la table parent
Schema::create('shops', ...);

// 2. Puis la table enfant
Schema::create('products', function (Blueprint $table) {
    $table->foreignUuid('shop_id')->constrained();
});
```

### Migrations avec Données Existantes

```php
public function up(): void {
    // Ajouter colonne
    Schema::table('products', function (Blueprint $table) {
        $table->string('sku')->nullable();
    });

    // Remplir les données
    foreach (Product::all() as $product) {
        $product->sku = strtoupper(str_slug($product->name)) . '-' . $product->id;
        $product->save();
    }

    // Rendre la colonne NOT NULL
    Schema::table('products', function (Blueprint $table) {
        $table->string('sku')->nullable(false)->change();
    });
}
```

## 📈 Performances

### Indexation

```php
// Index simple
$table->index('status');

// Index unique
$table->unique('email');

// Index composite
$table->index(['shop_id', 'created_at']);

// Full-text (MySQL/PostgreSQL)
$table->fullText('description');
```

### Pagination

```php
// Dans les migrations, penser à la pagination
$table->index('created_at');  // Pour les listes triées

// Dans les queries
Product::orderBy('created_at', 'desc')->paginate(15);
```

## 🧹 Maintenance

### Nettoyer les Données

```bash
# Dans un seeder ou migration
Schema::table('products', function (Blueprint $table) {
    $table->dropColumn('old_column');
});

# Renommer
Schema::table('products', function (Blueprint $table) {
    $table->renameColumn('old_name', 'new_name');
});

# Modifier le type
Schema::table('products', function (Blueprint $table) {
    $table->string('price')->change();  // De decimal à string
});
```

## 📝 Conventions

### Nommage des Tables

```
users              # Pluriel
products           # Pluriel
product_reviews    # Relation many-to-many
```

### Nommage des Colonnes

```
id                      # Clé primaire
{model}_id             # Clé étrangère (user_id)
created_at, updated_at # Timestamps
deleted_at             # Soft deletes
is_{adjective}         # Boolean (is_active)
{noun}_count           # Dénombrements (review_count)
```

## 🔗 Ressources

- [Laravel Docs - Migrations](https://laravel.com/docs/migrations)
- [Laravel Docs - Seeders](https://laravel.com/docs/seeding)
- [Laravel Docs - Factories](https://laravel.com/docs/eloquent-factories)

---

**Besoin d'aide?** Consultez la [documentation principale](../README.md)
