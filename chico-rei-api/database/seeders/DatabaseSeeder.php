<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Throwable;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password'],
        );

        $this->call(ProductSeeder::class);

        $this->reindexProducts();
    }

    /**
     * Sem eventos do model, o observer não indexa os produtos semeados.
     * O Elasticsearch é opcional: se estiver fora do ar, a busca usa o fallback do MySQL
     * e o seed não deve falhar.
     */
    private function reindexProducts(): void
    {
        try {
            $this->command->call('products:reindex');
        } catch (Throwable) {
            $this->command->warn('Elasticsearch indisponível: produtos não indexados. Rode "php artisan products:reindex" quando ele estiver no ar.');
        }
    }
}
