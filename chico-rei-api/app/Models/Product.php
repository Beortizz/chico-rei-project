<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'category',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProductLog::class);
    }
}
