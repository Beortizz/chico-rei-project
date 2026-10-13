<?php

namespace Tests\Feature\Products;

use App\Enums\ProductLogAction;
use App\Jobs\IndexProductJob;
use App\Jobs\LogProductChangeJob;
use App\Jobs\RemoveProductFromIndexJob;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_product_logs_it_with_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $id = $this->postJson('/api/products', [
            'name' => 'Camiseta', 'price' => 10, 'stock' => 1, 'category' => 'Camisetas',
        ])->assertCreated()->json('data.id');

        $log = ProductLog::where('product_id', $id)->sole();

        $this->assertSame(ProductLogAction::Created, $log->action);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('Camiseta', $log->product_name);
        $this->assertNull($log->old_values);
        $this->assertSame('Camiseta', $log->new_values['name']);
    }

    public function test_updating_a_product_logs_only_the_changed_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create(['stock' => 5, 'name' => 'Igual']);
        ProductLog::query()->delete();

        $this->putJson("/api/products/{$product->id}", ['stock' => 9])->assertOk();

        $log = ProductLog::where('product_id', $product->id)->sole();

        $this->assertSame(ProductLogAction::Updated, $log->action);
        $this->assertSame(['stock' => 5], $log->old_values);
        $this->assertSame(['stock' => 9], $log->new_values);
    }

    public function test_updating_without_changes_does_not_log(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create(['name' => 'Igual']);
        ProductLog::query()->delete();

        $this->putJson("/api/products/{$product->id}", ['name' => 'Igual'])->assertOk();

        $this->assertDatabaseCount('product_logs', 0);
    }

    public function test_deleting_a_product_keeps_its_log_with_the_snapshot(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create(['name' => 'Removido']);

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

        $log = ProductLog::where('product_id', $product->id)->where('action', ProductLogAction::Deleted)->sole();

        $this->assertSame('Removido', $log->product_name);
        $this->assertSame('Removido', $log->old_values['name']);
        $this->assertNull($log->new_values);
        $this->assertNull($log->product);
    }

    public function test_changes_made_without_a_user_are_logged_with_a_null_user(): void
    {
        $product = Product::factory()->create();

        $this->assertNull(ProductLog::where('product_id', $product->id)->sole()->user_id);
    }

    public function test_observer_dispatches_the_audit_and_index_jobs(): void
    {
        Bus::fake();

        $product = Product::factory()->create();
        Bus::assertDispatched(LogProductChangeJob::class, fn ($job) => $job->action === ProductLogAction::Created && $job->productId === $product->id);
        Bus::assertDispatched(IndexProductJob::class, fn ($job) => $job->productId === $product->id);

        $product->update(['stock' => 77]);
        Bus::assertDispatched(LogProductChangeJob::class, fn ($job) => $job->action === ProductLogAction::Updated);

        $product->delete();
        Bus::assertDispatched(LogProductChangeJob::class, fn ($job) => $job->action === ProductLogAction::Deleted);
        Bus::assertDispatched(RemoveProductFromIndexJob::class, fn ($job) => $job->productId === $product->id);
    }

    public function test_the_log_keeps_the_time_of_the_event_not_of_the_job(): void
    {
        $this->travelTo(now()->subDay());
        $product = Product::factory()->create();
        $this->travelBack();

        $this->assertTrue(ProductLog::where('product_id', $product->id)->sole()->created_at->isYesterday());
    }
}
