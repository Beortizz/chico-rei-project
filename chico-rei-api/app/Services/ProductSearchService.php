<?php

namespace App\Services;

use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductSearchService
{
    /**
     * O Elasticsearch limita a 10 hits por padrão; a paginação real fica no MySQL.
     */
    private const int MAX_RESULTS = 1000;

    private Client $client;

    private string $index;

    /**
     * Após uma falha, a busca pula o Elasticsearch por este tempo para não pagar timeout a cada requisição.
     */
    private const int UNAVAILABLE_TTL_SECONDS = 30;

    private const string UNAVAILABLE_CACHE_KEY = 'elasticsearch.unavailable';

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([config('services.elasticsearch.host')])
            ->setHttpClientOptions(['timeout' => 2, 'connect_timeout' => 1])
            ->build();

        $this->index = config('services.elasticsearch.index');
    }

    /**
     * Cria o índice com o mapping (analyzer em português, asciifolding).
     */
    public function createIndex(): void
    {
        /** @var array<string, mixed> $body */
        $body = [
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'default' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase', 'asciifolding', 'portuguese_stemmer'],
                        ],
                    ],
                    'filter' => [
                        'portuguese_stemmer' => [
                            'type' => 'stemmer',
                            'language' => 'light_portuguese',
                        ],
                    ],
                ],
            ],
            'mappings' => [
                'properties' => [
                    'name' => ['type' => 'text'],
                    'description' => ['type' => 'text'],
                    'category' => ['type' => 'keyword'],
                    'price' => ['type' => 'scaled_float', 'scaling_factor' => 100],
                    'stock' => ['type' => 'integer'],
                ],
            ],
        ];

        $this->client->indices()->create([
            'index' => $this->index,
            'body' => $body,
        ]);
    }

    public function deleteIndex(): void
    {
        $this->client->indices()->delete([
            'index' => $this->index,
            'ignore_unavailable' => true,
        ]);
    }

    public function index(Product $product): void
    {
        /** @var array<string, mixed> $body */
        $body = [
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category,
            'price' => (float) $product->price,
            'stock' => $product->stock,
        ];

        $this->client->index([
            'index' => $this->index,
            'id' => (string) $product->id,
            'body' => $body,
        ]);
    }

    public function remove(int $productId): void
    {
        try {
            $this->client->delete([
                'index' => $this->index,
                'id' => (string) $productId,
            ]);
        } catch (ClientResponseException $e) {
            if ($e->getResponse()->getStatusCode() !== 404) {
                throw $e;
            }
        }
    }

    /**
     * @return int[]|null IDs dos produtos, ordenados por relevância; null se o Elasticsearch estiver indisponível.
     */
    public function search(string $term): ?array
    {
        if (Cache::has(self::UNAVAILABLE_CACHE_KEY)) {
            return null;
        }

        try {
            return $this->querySearch($term);
        } catch (NoNodeAvailableException|ServerResponseException $e) {
            return $this->markUnavailable($e);
        } catch (ClientResponseException $e) {
            if ($e->getResponse()->getStatusCode() !== 404) {
                throw $e;
            }

            return $this->markUnavailable($e);
        }
    }

    private function markUnavailable(\Throwable $e): null
    {
        Cache::put(self::UNAVAILABLE_CACHE_KEY, true, self::UNAVAILABLE_TTL_SECONDS);
        Log::warning('Elasticsearch indisponível; busca usando fallback do MySQL.', ['error' => $e->getMessage()]);

        return null;
    }

    /**
     * @return int[]
     */
    private function querySearch(string $term): array
    {
        /** @var array<string, mixed> $body */
        $body = [
            'size' => self::MAX_RESULTS,
            '_source' => false,
            'query' => [
                'multi_match' => [
                    'query' => $term,
                    'fields' => ['name^3', 'description'],
                    'fuzziness' => 'AUTO',
                ],
            ],
        ];

        $response = $this->client->search([
            'index' => $this->index,
            'body' => $body,
        ]);

        return array_map(
            fn (array $hit) => (int) $hit['_id'],
            $response['hits']['hits'],
        );
    }
}
