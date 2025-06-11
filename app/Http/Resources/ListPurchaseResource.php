<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListPurchaseResource extends ListProductResource{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array{
        return array_merge(
            parent::toArray($request),
            [
                'quantity' => $this->quantity,
                'purchase_date' => $this->purchase_date,
            ]
        );
    }
}
