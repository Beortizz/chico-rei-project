<?php

namespace Tests\Feature\Products;

use App\Enums\ProductLogAction;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductLogListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_logs(): void
    {
        $this->getJson('/api/product-logs')->assertUnauthorized();
    }

    public function test_it_lists_logs_newest_first_with_the_responsible_user(): void
    {
        $user = User::factory()->create(['name' => 'Maria']);
        Sanctum::actingAs($user);

        $first = Product::factory()->create(['name' => 'Primeiro']);
        $second = Product::factory()->create(['name' => 'Segundo']);

        $this->getJson('/api/product-logs')
            ->assertOk()
            ->assertJsonPath('data.0.product_id', $second->id)
            ->assertJsonPath('data.1.product_id', $first->id)
            ->assertJsonPath('data.0.action', 'created')
            ->assertJsonPath('data.0.user.name', 'Maria')
            ->assertJsonStructure(['data' => [['id', 'product_id', 'product_name', 'action', 'old_values', 'new_values', 'user', 'created_at']], 'meta', 'links']);
    }

    public function test_it_filters_by_action_product_and_name(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $camiseta = Product::factory()->create(['name' => 'Camiseta']);
        $calca = Product::factory()->create(['name' => 'Calça']);
        $camiseta->update(['stock' => 99]);

        $this->getJson('/api/product-logs?action=updated')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/product-logs?product_id={$calca->id}")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/product-logs?name=camis')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_it_rejects_an_unknown_action(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/product-logs?action=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('action');
    }

    public function test_it_paginates_and_shows_logs_without_a_user(): void
    {
        Sanctum::actingAs(User::factory()->create());
        ProductLog::create(['product_id' => 1, 'product_name' => 'Antigo', 'user_id' => null, 'action' => ProductLogAction::Deleted]);
        Product::factory()->count(2)->create();

        $this->getJson('/api/product-logs?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3);

        $this->getJson('/api/product-logs?action=deleted')->assertJsonPath('data.0.user', null);
    }
}
