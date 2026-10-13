<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private const string PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());
    }

    private function png(string $name = 'foto.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG_1X1));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $extra = []): array
    {
        return ['name' => 'Camiseta', 'price' => 10, 'stock' => 1, 'category' => 'Camisetas', ...$extra];
    }

    public function test_it_stores_an_uploaded_image_and_returns_its_public_url(): void
    {
        $response = $this->postJson('/api/products', $this->payload(['image' => $this->png()]))->assertCreated();

        $path = Product::sole()->image;

        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('products/', $path);
        $this->assertStringEndsWith('.webp', $path);
        $this->assertSame(Storage::disk('public')->url($path), $response->json('data.image'));
    }

    public function test_it_downscales_large_images_keeping_the_aspect_ratio_and_converts_to_webp(): void
    {
        $this->postJson('/api/products', $this->payload(['image' => UploadedFile::fake()->image('grande.jpg', 3000, 1500)]))->assertCreated();

        [$width, $height, , , ] = getimagesizefromstring(Storage::disk('public')->get(Product::sole()->image));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer(Storage::disk('public')->get(Product::sole()->image));

        $this->assertSame(1200, $width);
        $this->assertSame(600, $height);
        $this->assertSame('image/webp', $mime);
    }

    public function test_it_does_not_upscale_small_images(): void
    {
        $this->postJson('/api/products', $this->payload(['image' => UploadedFile::fake()->image('pequena.png', 300, 200)]))->assertCreated();

        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get(Product::sole()->image));

        $this->assertSame(300, $width);
        $this->assertSame(200, $height);
    }

    public function test_it_creates_a_product_without_image(): void
    {
        $this->postJson('/api/products', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.image', null);
    }

    public function test_it_rejects_files_that_are_not_allowed_images(): void
    {
        $this->postJson('/api/products', $this->payload(['image' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>')]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');

        $this->postJson('/api/products', $this->payload(['image' => UploadedFile::fake()->createWithContent('x.pdf', '%PDF-1.4')]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_it_rejects_images_larger_than_2mb(): void
    {
        $big = UploadedFile::fake()->createWithContent('big.png', base64_decode(self::PNG_1X1).str_repeat('0', 2 * 1024 * 1024));

        $this->postJson('/api/products', $this->payload(['image' => $big]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_replacing_the_image_deletes_the_previous_file(): void
    {
        $product = Product::factory()->create(['image' => Storage::disk('public')->putFile('products', $this->png('antiga.png'))]);
        $old = $product->image;

        $this->postJson("/api/products/{$product->id}", ['_method' => 'PUT', 'image' => $this->png('nova.png')])->assertOk();

        $new = $product->refresh()->image;

        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);
    }

    public function test_updating_other_fields_keeps_the_image(): void
    {
        $path = Storage::disk('public')->putFile('products', $this->png());
        $product = Product::factory()->create(['image' => $path]);

        $this->putJson("/api/products/{$product->id}", ['stock' => 3])->assertOk();

        $this->assertSame($path, $product->refresh()->image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_remove_image_clears_the_field_and_deletes_the_file(): void
    {
        $path = Storage::disk('public')->putFile('products', $this->png());
        $product = Product::factory()->create(['image' => $path]);

        $this->putJson("/api/products/{$product->id}", ['remove_image' => true])
            ->assertOk()
            ->assertJsonPath('data.image', null);

        $this->assertNull($product->refresh()->image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_a_product_deletes_its_image_file(): void
    {
        $path = Storage::disk('public')->putFile('products', $this->png());
        $product = Product::factory()->create(['image' => $path]);

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_external_image_urls_are_returned_as_is_and_never_touched_on_disk(): void
    {
        $url = 'https://picsum.photos/seed/x/600/600';
        $product = Product::factory()->create(['image' => $url]);

        $this->getJson("/api/products/{$product->id}")->assertJsonPath('data.image', $url);

        $this->putJson("/api/products/{$product->id}", ['remove_image' => true])->assertOk();
        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
