<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ProductLog */
class ProductLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'action' => $this->action,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'user' => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null,
            'created_at' => $this->created_at,
        ];
    }
}
