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
        $productData = (new ListProductResource($this->product))->toArray($request);

        return array_merge($productData, [
            'quantity' => $this->quantity,
            'purchase_date' => $this->purchase_date,
        ]);
    }
}
