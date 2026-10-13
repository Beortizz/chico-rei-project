<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_and_view_products(): void
    {
        $product = Product::factory()->create();

        $this->getJson('/api/products')->assertOk();
        $this->getJson("/api/products/{$product->id}")->assertOk()->assertJsonPath('data.id', $product->id);
    }

    public function test_guest_cannot_create_update_or_delete_products(): void
    {
        $product = Product::factory()->create();

        $this->postJson('/api/products', [])->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
        $this->putJson("/api/products/{$product->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/products/{$product->id}")->assertUnauthorized();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_authenticated_user_can_reach_write_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();
    }
}
