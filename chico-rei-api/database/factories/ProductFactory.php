<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'image' => fake()->imageUrl(600, 600),
            'price' => fake()->randomFloat(2, 5, 500),
            'category' => fake()->randomElement(['Camisetas', 'Calças', 'Acessórios']),
            'stock' => fake()->numberBetween(1, 100),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
