<?php

namespace Tests\Feature\Database;

use App\Models\Product;
use App\Models\User;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_login_user_and_products_and_indexes_them(): void
    {
        $this->searchService->shouldReceive('deleteIndex')->once();
        $this->searchService->shouldReceive('createIndex')->once();
        $this->searchService->shouldReceive('index')->times(24);

        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertSame(24, Product::count());
    }

    public function test_it_still_seeds_when_elasticsearch_is_unavailable(): void
    {
        $this->searchService->shouldReceive('deleteIndex')->andThrow(new NoNodeAvailableException('down'));

        $this->seed();

        $this->assertSame(24, Product::count());
        $this->assertSame(1, User::count());
    }

    public function test_seeding_twice_does_not_duplicate_data(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(24, Product::count());
        $this->assertSame(1, User::count());
    }
}
