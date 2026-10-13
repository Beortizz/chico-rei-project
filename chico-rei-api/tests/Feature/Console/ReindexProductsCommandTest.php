<?php

namespace Tests\Feature\Console;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReindexProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_recreates_the_index_and_indexes_every_product(): void
    {
        Product::factory()->count(3)->create();

        $this->searchService->shouldReceive('deleteIndex')->once()->ordered();
        $this->searchService->shouldReceive('createIndex')->once()->ordered();
        $this->searchService->shouldReceive('index')->times(3)->with(Product::class);

        $this->artisan('products:reindex')->assertSuccessful();
    }
}
