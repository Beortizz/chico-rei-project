<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ProductFilter
{
    /** Colunas permitidas na ordenação; evita ordenar por campos arbitrários vindos da requisição. */
    public const SORTABLE = ['name', 'price', 'stock', 'created_at'];

    /**
     * @param  array<string, mixed>  $filters  parâmetros já validados por ListProductsRequest
     */
    public function __construct(private readonly array $filters)
    {
    }

    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->filters['name'] ?? null, fn (Builder $q, string $name) => $q->where('name', 'like', "%{$name}%"))
            ->when($this->filters['category'] ?? null, fn (Builder $q, string $category) => $q->where('category', $category))
            ->when(isset($this->filters['min_price']), fn (Builder $q) => $q->where('price', '>=', $this->filters['min_price']))
            ->when(isset($this->filters['max_price']), fn (Builder $q) => $q->where('price', '<=', $this->filters['max_price']))
            ->when(isset($this->filters['in_stock']), fn (Builder $q) => $this->filters['in_stock']
                ? $q->where('stock', '>', 0)
                : $q->where('stock', 0))
            ->orderBy($this->filters['sort'] ?? 'created_at', $this->filters['direction'] ?? 'desc')
            // Desempate por id mantém a paginação estável quando o valor ordenado se repete.
            ->orderBy('id');
    }
}
