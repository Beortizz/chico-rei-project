<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductNameSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_restricts_results_to_the_ids_returned_by_elasticsearch(): void
    {
        $match = Product::factory()->create(['name' => 'Camiseta Azul']);
        Product::factory()->create(['name' => 'Camiseta Verde']);

        $this->searchService->shouldReceive('search')->with('camiseta')->once()->andReturn([$match->id]);

        $this->getJson('/api/products?name=camiseta')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_it_orders_by_relevance_when_no_sort_is_given(): void
    {
        $first = Product::factory()->create();
        $second = Product::factory()->create();
        $third = Product::factory()->create();

        $this->searchService->shouldReceive('search')->andReturn([$third->id, $first->id, $second->id]);

        $this->getJson('/api/products?name=qualquer')
            ->assertOk()
            ->assertJsonPath('data.*.id', [$third->id, $first->id, $second->id]);
    }

    public function test_explicit_sort_takes_precedence_over_relevance(): void
    {
        $cheap = Product::factory()->create(['price' => 10]);
        $expensive = Product::factory()->create(['price' => 90]);

        $this->searchService->shouldReceive('search')->andReturn([$expensive->id, $cheap->id]);

        $this->getJson('/api/products?name=qualquer&sort=price&direction=asc')
            ->assertOk()
            ->assertJsonPath('data.*.id', [$cheap->id, $expensive->id]);
    }

    public function test_it_returns_nothing_when_elasticsearch_finds_nothing(): void
    {
        Product::factory()->create(['name' => 'Camiseta']);

        $this->searchService->shouldReceive('search')->andReturn([]);

        $this->getJson('/api/products?name=inexistente')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_it_falls_back_to_like_when_elasticsearch_is_unavailable(): void
    {
        Product::factory()->create(['name' => 'Camiseta Azul']);
        Product::factory()->create(['name' => 'Calça Jeans']);

        $this->searchService->shouldReceive('search')->andReturn(null);

        $this->getJson('/api/products?name=camiseta')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Camiseta Azul');
    }

    public function test_elasticsearch_is_not_queried_without_a_name_filter(): void
    {
        Product::factory()->create();

        $this->searchService->shouldNotReceive('search');

        $this->getJson('/api/products')->assertOk();
    }
}
