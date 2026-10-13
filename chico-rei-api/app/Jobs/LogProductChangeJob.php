<?php

namespace App\Jobs;

use App\Enums\ProductLogAction;
use App\Models\ProductLog;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Grava a auditoria fora da requisição. Recebe só dados primitivos porque,
 * quando roda, o produto pode já ter sido excluído e não há usuário autenticado.
 */
class LogProductChangeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var int[] segundos entre as tentativas */
    public array $backoff = [10, 60];

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function __construct(
        public int $productId,
        public string $productName,
        public ?int $userId,
        public ProductLogAction $action,
        public ?array $oldValues,
        public ?array $newValues,
        public CarbonImmutable $occurredAt,
    ) {
    }

    public function handle(): void
    {
        $log = new ProductLog([
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'user_id' => $this->userId,
            'action' => $this->action,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
        ]);

        $log->created_at = $this->occurredAt;
        $log->save();
    }
}
