<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListProductResource extends JsonResource{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array{
        return [
            'id' => $this->id,
            'catalog_product_id' => $this->catalog_product_id,
            'name' => $this->name,
            'image' => $this->image,
            'short_description' => $this->short_description,
            'price' => $this->price,
        ];
    }
}
