<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_distinct_categories_alphabetically_for_guests(): void
    {
        Product::factory()->create(['category' => 'Camisetas']);
        Product::factory()->create(['category' => 'Camisetas']);
        Product::factory()->create(['category' => 'Acessórios']);

        $this->getJson('/api/products/categories')
            ->assertOk()
            ->assertExactJson(['data' => ['Acessórios', 'Camisetas']]);
    }

    public function test_it_returns_an_empty_list_without_products(): void
    {
        $this->getJson('/api/products/categories')->assertOk()->assertExactJson(['data' => []]);
    }
}
