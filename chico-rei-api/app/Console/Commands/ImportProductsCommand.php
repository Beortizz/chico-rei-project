<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command as CommandAlias;

#[Signature('products:import {limit?}')]
#[Description('Importa produtos da Fake Store API')]
class ImportProductsCommand extends Command
{
    const string FAKE_STORE_URL = "https://fakestoreapi.com";
    protected int $defaultLimit = 20;
    protected int $maxLimit = 100;
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->argument('limit');

        if ($limit < 1 || $limit > $this->maxLimit) {
            $this->info("Limit is required and must be less than or equal to {$this->maxLimit}. Defaulting to {$this->defaultLimit}.");
            $limit = $this->defaultLimit;
        }

        $this->info("Importing products...");
        try {
            $response = Http::get(self::FAKE_STORE_URL . '/products', [
                'limit' => $limit
            ])->throw()->json();

            $this->info("Products imported successfully.");
            foreach ($response as $product) {
                Product::create([
                    'name' => trim($product['title']),
                    'description' => $product['description'],
                    'image' => $product['image'],
                    'price' => $product['price'],
                    'category' => $product['category'],
                    'stock' => rand(0, 100),
                ]);
            }
            return CommandAlias::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to fetch products: " . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
