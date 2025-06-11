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
        $product = $this instanceof \App\Models\Product ? $this : $this->product;
        return [
            'id' => $product->id,
            'catalog_product_id' => $product->catalog_product_id,
            'name' => $product->name,
            'image' => $product->image,
            'short_description' => $product->short_description,
            'price' => $product->price,
        ];
    }
}
