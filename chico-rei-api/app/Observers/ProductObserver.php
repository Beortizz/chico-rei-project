<?php

namespace App\Observers;

use App\Enums\ProductLogAction;
use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Support\Arr;

class ProductObserver
{
    /**
     * Evita registrar log de uma operação que sofreu rollback.
     */
    public bool $afterCommit = true;

    public function created(Product $product): void
    {
        $this->log($product, ProductLogAction::Created, null, $product->toArray());
    }

    public function updated(Product $product): void
    {
        $changes = Arr::except($product->getChanges(), ['updated_at']);

        $this->log(
            $product,
            ProductLogAction::Updated,
            Arr::only($product->getOriginal(), array_keys($changes)),
            $changes,
        );
    }

    public function deleted(Product $product): void
    {
        $this->log($product, ProductLogAction::Deleted, $product->toArray(), null);
    }

    private function log(Product $product, ProductLogAction $action, ?array $old, ?array $new): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
