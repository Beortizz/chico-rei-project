<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\ProductSearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IndexProductJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var int[] segundos entre as tentativas */
    public array $backoff = [10, 60];

    public function __construct(public int $productId)
    {
    }

    public function handle(ProductSearchService $search): void
    {
        $product = Product::find($this->productId);

        if ($product) {
            $search->index($product);
        }
    }
}
