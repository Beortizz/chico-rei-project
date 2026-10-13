<?php

namespace App\Jobs;

use App\Services\ProductSearchService;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveProductFromIndexJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60];

    public function __construct(public int $productId)
    {
    }

    /**
     * @throws ClientResponseException
     */
    public function handle(ProductSearchService $search): void
    {
       $search->remove($this->productId);
    }
}
