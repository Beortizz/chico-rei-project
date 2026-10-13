<?php

namespace App\Filters;

use App\Services\ProductSearchService;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter
{
    /** Colunas permitidas na ordenação; evita ordenar por campos arbitrários vindos da requisição. */
    public const SORTABLE = ['name', 'price', 'stock', 'created_at'];

    /**
     * @param  array<string, mixed>  $filters  parâmetros já validados por ListProductsRequest
     */
    public function __construct(private readonly array $filters, private readonly ProductSearchService $productSearchService)
    {
    }

    public function apply(Builder $query): Builder
    {
        $relevantIds = $this->applyNameSearch($query);

        $query
            ->when($this->filters['category'] ?? null, fn (Builder $q, string $category) => $q->where('category', $category))
            ->when(isset($this->filters['min_price']), fn (Builder $q) => $q->where('price', '>=', $this->filters['min_price']))
            ->when(isset($this->filters['max_price']), fn (Builder $q) => $q->where('price', '<=', $this->filters['max_price']))
            ->when(isset($this->filters['in_stock']), fn (Builder $q) => $this->filters['in_stock']
                ? $q->where('stock', '>', 0)
                : $q->where('stock', 0));

        if ($relevantIds && !isset($this->filters['sort'])) {
            $query->orderByRaw('FIELD(id, '.implode(',', $relevantIds).')');
        }

        return $query
            ->orderBy($this->filters['sort'] ?? 'created_at', $this->filters['direction'] ?? 'desc')
            ->orderBy('id');
    }

    /**
     * Usa o Elasticsearch para o nome e cai para LIKE se ele estiver indisponível.
     *
     * @return int[]|null IDs relevantes, ou null se não houve busca ou foi usado o fallback
     */
    private function applyNameSearch(Builder $query): ?array
    {
        $name = $this->filters['name'] ?? null;

        if (! $name) {
            return null;
        }

        $ids = $this->productSearchService->search($name);

        if ($ids === null) {
            $query->where('name', 'like', "%{$name}%");

            return null;
        }

        $query->whereIn('id', $ids);

        return $ids;
    }
}
