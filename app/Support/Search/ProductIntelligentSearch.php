<?php

namespace App\Support\Search;

use App\Models\Produit;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Files\Image;
use thiagoalessio\TesseractOCR\TesseractOCR;

use function Laravel\Ai\agent;

class ProductIntelligentSearch
{
    private const int EMBEDDING_DIMENSIONS = 1536;

    public function search(Builder $query, string $term, int $limit = 120): array
    {
        $normalizedTerm = Str::squish($term);

        if ($normalizedTerm === '') {
            return [
                'ids' => [],
                'semantic' => false,
            ];
        }

        $textIds = (clone $query)
            ->search($normalizedTerm)
            ->limit($limit)
            ->pluck('produits.id')
            ->all();

        $embedding = $this->embed($normalizedTerm);

        if (! $embedding || ! $this->supportsVectorSearch()) {
            return [
                'ids' => $textIds,
                'semantic' => false,
            ];
        }

        try {
            $vectorIds = (clone $query)
                ->whereNotNull('search_embedding')
                ->orderByRaw('search_embedding <=> ?::vector asc', [$this->toPgVector($embedding)])
                ->limit($limit)
                ->pluck('produits.id')
                ->all();

            return [
                'ids' => $this->mergeRankedIds($textIds, $vectorIds),
                'semantic' => ! empty($vectorIds),
            ];
        } catch (\Throwable $e) {
            Log::warning('Recherche vectorielle produit indisponible, fallback texte activé.', [
                'message' => $e->getMessage(),
            ]);

            return [
                'ids' => $textIds,
                'semantic' => false,
            ];
        }
    }

    public function searchByImage(Builder $query, string $fullPath, int $limit = 120): array
    {
        $analysis = $this->describeImage($fullPath);
        $ocrText = $this->extractTextFromImage($fullPath);

        $queryText = Str::squish(collect([
            $analysis['optimized_query'] ?? null,
            $analysis['description'] ?? null,
            $analysis['detected_category'] ?? null,
            $analysis['detected_colors'] ?? null,
            $ocrText,
        ])->filter()->implode(' '));

        $result = $this->search($query, $queryText, $limit);

        return [
            'query' => $queryText,
            'analysis' => $analysis,
            'ocr_text' => $ocrText,
            'ids' => $result['ids'],
            'semantic' => $result['semantic'],
        ];
    }

    public function syncProductIndex(Produit $product, bool $withEmbedding = true): void
    {
        $product->loadMissing(['brand', 'categories']);

        $document = $product->buildSearchDocument();
        $payload = [
            'search_document' => $document,
            'search_embedding_synced_at' => null,
        ];

        if (! $withEmbedding || ! $this->supportsVectorSearch()) {
            $product->forceFill($payload)->saveQuietly();

            return;
        }

        $embedding = $this->embed($document);

        if (! $embedding) {
            $product->forceFill($payload)->saveQuietly();

            return;
        }

        DB::table('produits')
            ->where('id', $product->id)
            ->update([
                'search_document' => $document,
                'search_embedding' => DB::raw("'".$this->toPgVector($embedding)."'::vector"),
                'search_embedding_synced_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function supportsVectorSearch(): bool
    {
        static $supported;

        if (! is_null($supported)) {
            return $supported;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return $supported = false;
        }

        try {
            $extensionInstalled = DB::scalar("select exists (select 1 from pg_extension where extname = 'vector')");
            $columnExists = DB::table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', 'produits')
                ->where('column_name', 'search_embedding')
                ->exists();

            return $supported = (bool) $extensionInstalled && $columnExists;
        } catch (\Throwable $e) {
            Log::warning('Impossible de valider pgvector.', [
                'message' => $e->getMessage(),
            ]);

            return $supported = false;
        }
    }

    protected function embed(string $text): ?array
    {
        $text = Str::squish($text);

        if ($text === '' || blank(config('ai.default_for_embeddings'))) {
            return null;
        }

        try {
            return Embeddings::for([$text])
                ->dimensions(self::EMBEDDING_DIMENSIONS)
                ->timeout(15)
                ->generate()
                ->first();
        } catch (\Throwable $e) {
            Log::warning('Génération d’embedding impossible, fallback texte activé.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function describeImage(string $fullPath): array
    {
        if (blank(config('ai.default_for_images'))) {
            return [];
        }

        try {
            $response = agent(
                instructions: 'Tu analyses une image de produit e-commerce et retournes un objet JSON concis pour lancer une recherche catalogue fiable.',
                schema: fn (JsonSchema $schema): array => [
                    'optimized_query' => $schema->string()->required(),
                    'description' => $schema->string(),
                    'detected_category' => $schema->string(),
                    'detected_colors' => $schema->string(),
                ],
            )->prompt(
                prompt: 'Décris le produit principal visible, sa catégorie probable, ses caractéristiques distinctives et reformule une requête de recherche e-commerce courte en français.',
                attachments: [Image::fromPath($fullPath)],
                provider: config('ai.default_for_images'),
            );

            return [
                'optimized_query' => (string) ($response['optimized_query'] ?? ''),
                'description' => (string) ($response['description'] ?? ''),
                'detected_category' => (string) ($response['detected_category'] ?? ''),
                'detected_colors' => (string) ($response['detected_colors'] ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::warning('Analyse IA d’image indisponible, fallback OCR activé.', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    protected function extractTextFromImage(string $fullPath): string
    {
        try {
            $text = (new TesseractOCR($fullPath))
                ->lang('fra', 'eng')
                ->run();

            return Str::words(Str::squish($text), 10, '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    protected function mergeRankedIds(array $textIds, array $vectorIds): array
    {
        $scores = [];

        foreach ($vectorIds as $index => $id) {
            $scores[$id] = ($scores[$id] ?? 0) + max(1, 100 - $index);
        }

        foreach ($textIds as $index => $id) {
            $scores[$id] = ($scores[$id] ?? 0) + max(1, 150 - $index);
        }

        arsort($scores);

        return array_keys($scores);
    }

    protected function toPgVector(array $embedding): string
    {
        return '['.collect($embedding)
            ->map(fn ($value) => number_format((float) $value, 8, '.', ''))
            ->implode(',').']';
    }
}
