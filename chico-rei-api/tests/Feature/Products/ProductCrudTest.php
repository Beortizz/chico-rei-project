<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_it_creates_a_product(): void
    {
        $payload = ['name' => 'Camiseta Teste', 'description' => 'Descrição', 'price' => 59.9, 'stock' => 10, 'category' => 'Camisetas'];

        $this->postJson('/api/products', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Camiseta Teste')
            ->assertJsonPath('data.in_stock', true);

        $this->assertDatabaseHas('products', ['name' => 'Camiseta Teste', 'category' => 'Camisetas']);
    }

    public function test_it_rejects_invalid_data_with_the_standard_error_format(): void
    {
        $this->postJson('/api/products', ['price' => -1, 'stock' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['name', 'price', 'stock', 'category']]);
    }

    public function test_category_is_required(): void
    {
        $this->postJson('/api/products', ['name' => 'X', 'price' => 1, 'stock' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');
    }

    public function test_it_shows_a_product(): void
    {
        $product = Product::factory()->create(['name' => 'Jaqueta']);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'image', 'price', 'category', 'stock', 'in_stock', 'created_at', 'updated_at']])
            ->assertJsonPath('data.name', 'Jaqueta');
    }

    public function test_it_returns_the_standard_not_found_error(): void
    {
        $this->getJson('/api/products/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Resource not found.']);
    }

    public function test_it_updates_only_the_sent_fields(): void
    {
        $product = Product::factory()->create(['name' => 'Antigo', 'stock' => 5]);

        $this->putJson("/api/products/{$product->id}", ['stock' => 0])
            ->assertOk()
            ->assertJsonPath('data.stock', 0)
            ->assertJsonPath('data.in_stock', false)
            ->assertJsonPath('data.name', 'Antigo');
    }

    public function test_it_deletes_a_product(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
