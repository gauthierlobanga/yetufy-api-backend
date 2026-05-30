<?php

namespace App\Console\Commands;

use App\Models\Produit;
use App\Support\Search\ProductIntelligentSearch;
use Illuminate\Console\Command;

class SyncProductSearchIndex extends Command
{
    protected $signature = 'shop:sync-product-search-index
                            {--tenant= : Limiter la synchronisation a un tenant}
                            {--without-embeddings : Regenerer uniquement le document de recherche}';

    protected $description = 'Synchronise les documents et embeddings de recherche intelligente des produits.';

    public function handle(ProductIntelligentSearch $search): int
    {
        $query = Produit::query()->with(['brand', 'categories']);

        if ($tenantId = $this->option('tenant')) {
            $query->where('tenant_id', $tenantId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->components->warn('Aucun produit a synchroniser.');

            return self::SUCCESS;
        }

        $this->components->info("Synchronisation de {$total} produit(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $withEmbeddings = ! $this->option('without-embeddings');

        $query->chunk(25, function ($products) use ($bar, $search, $withEmbeddings) {
            foreach ($products as $product) {
                $search->syncProductIndex($product, $withEmbeddings);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->components->info('Index produit synchronise.');

        return self::SUCCESS;
    }
}
