<?php

namespace App\Services;

use App\Models\ProductLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductLogService
{
    /**
     * @param  array<string, mixed>  $filters  parâmetros já validados por ListProductLogsRequest
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return ProductLog::query()
            ->with('user:id,name')
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['product_id'] ?? null, fn ($query, int $productId) => $query->where('product_id', $productId))
            ->when($filters['name'] ?? null, fn ($query, string $name) => $query->where('product_name', 'like', "%{$name}%"))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
