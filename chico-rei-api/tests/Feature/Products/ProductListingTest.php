<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_paginates_with_a_configurable_page_size(): void
    {
        Product::factory()->count(7)->create();

        $this->getJson('/api/products?per_page=3')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonPath('meta.total', 7)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_it_rejects_an_out_of_range_page_size(): void
    {
        $this->getJson('/api/products?per_page=1000')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_validation_messages_are_in_portuguese(): void
    {
        config(['app.locale' => 'pt_BR']);

        $this->getJson('/api/products?per_page=1000&min_price=100&max_price=10')
            ->assertUnprocessable()
            ->assertJsonPath('errors.max_price.0', 'O campo preço máximo deve ser maior ou igual a 100.')
            ->assertJsonPath('errors.per_page.0', 'O campo itens por página não pode conter um valor superior a 100.');
    }

    public function test_it_filters_by_category(): void
    {
        Product::factory()->create(['category' => 'Camisetas']);
        Product::factory()->create(['category' => 'Calças']);

        $this->getJson('/api/products?category=Camisetas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category', 'Camisetas');
    }

    public function test_it_filters_by_price_range(): void
    {
        Product::factory()->create(['name' => 'Barato', 'price' => 10]);
        Product::factory()->create(['name' => 'Médio', 'price' => 50]);
        Product::factory()->create(['name' => 'Caro', 'price' => 200]);

        $this->getJson('/api/products?min_price=20&max_price=100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Médio');
    }

    public function test_it_rejects_a_max_price_lower_than_min_price(): void
    {
        $this->getJson('/api/products?min_price=100&max_price=10')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_price');
    }

    public function test_it_filters_by_availability(): void
    {
        Product::factory()->create(['name' => 'Com estoque', 'stock' => 5]);
        Product::factory()->outOfStock()->create(['name' => 'Sem estoque']);

        $this->getJson('/api/products?in_stock=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Com estoque');

        $this->getJson('/api/products?in_stock=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Sem estoque');
    }

    public function test_it_sorts_by_an_allowed_column(): void
    {
        Product::factory()->create(['price' => 30]);
        Product::factory()->create(['price' => 10]);
        Product::factory()->create(['price' => 20]);

        $response = $this->getJson('/api/products?sort=price&direction=asc')->assertOk();

        $this->assertEquals([10, 20, 30], array_map('floatval', $response->json('data.*.price')));
    }

    public function test_it_rejects_sorting_by_a_disallowed_column(): void
    {
        $this->getJson('/api/products?sort=description')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_it_combines_filters(): void
    {
        Product::factory()->create(['category' => 'Camisetas', 'price' => 40, 'stock' => 3]);
        Product::factory()->create(['category' => 'Camisetas', 'price' => 40, 'stock' => 0]);
        Product::factory()->create(['category' => 'Calças', 'price' => 40, 'stock' => 3]);

        $this->getJson('/api/products?category=Camisetas&in_stock=1&max_price=50')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
