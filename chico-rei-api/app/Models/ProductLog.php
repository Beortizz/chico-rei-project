<?php

namespace App\Models;

use App\Enums\ProductLogAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'product_name',
        'user_id',
        'action',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'action' => ProductLogAction::class,
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Nulo quando o produto já foi excluído; use product_name nesse caso.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
