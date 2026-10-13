<?php

namespace App\Observers;

use App\Enums\ProductLogAction;
use App\Jobs\IndexProductJob;
use App\Jobs\LogProductChangeJob;
use App\Jobs\RemoveProductFromIndexJob;
use App\Models\Product;
use Carbon\CarbonImmutable;
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

        IndexProductJob::dispatch($product->id);
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

        IndexProductJob::dispatch($product->id);
    }

    public function deleted(Product $product): void
    {
        $this->log($product, ProductLogAction::Deleted, $product->toArray(), null);

        RemoveProductFromIndexJob::dispatch($product->id);
    }

    /**
     * O usuário é capturado aqui porque o worker não tem sessão autenticada.
     *
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    private function log(Product $product, ProductLogAction $action, ?array $old, ?array $new): void
    {
        LogProductChangeJob::dispatch(
            $product->id,
            $product->name,
            auth()->id(),
            $action,
            $old,
            $new,
            CarbonImmutable::now(),
        );
    }
}
