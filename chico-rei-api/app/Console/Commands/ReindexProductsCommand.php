<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductSearchService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('products:reindex')]
#[Description('Recria o índice do Elasticsearch com todos os produtos')]
class ReindexProductsCommand extends Command
{
    public function handle(ProductSearchService $search): int
    {
        $search->deleteIndex();
        $search->createIndex();


        Product::chunkById(200, function ($products) use ($search) {
            foreach ($products as $product) {
                $search->index($product);
            }
        });
        $this->info('Reindexing complete successfully.');

        return self::SUCCESS;
    }
}
