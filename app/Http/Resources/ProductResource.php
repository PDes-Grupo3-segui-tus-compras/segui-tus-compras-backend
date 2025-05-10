<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductResource extends JsonResource {

    public function toArray(Request $request): array {

        $attributes = [];
        foreach ($this->attributes ?? [] as $attr) {
            $attributes[$attr['id']] = $attr['value_name'];
        }
        $product = Product::where('catalog_product_id', $this['catalog_product_id'])->first();
        $user = Auth::user();

        return [
            'id' => $this['id'],
            'catalog_product_id' => $this['catalog_product_id'],
            'name' => $this['name'],
            'price' => $this->getPrice(),
            /* If price is Null it means the product it's unavailable, and we have to do something about it :(
             */
            'warranty' => $this['buy_box_winner']['warranty'] ?? null,
            'seller_address' => $this['buy_box_winner']['seller_address'] ?? null,
            /*{
            "city": {
                "name": ""
            },
            "state": {
                "id": "",
                "name": ""
            }} */
            'pictures' => $this['pictures'],
            /*{"id": ,
            "url": ,
            "suggested_for_picker": ,
            "max_width": ,
            "max_height": ,
            "source_metadata": ,
            "tags":[]} */
            'main_features' => $this['main_features'],
            /*{
                text:
                type:
                metadata:
            */
            'attributes' => $this['attributes'],
            /*{
            id:
            value_name:
            }*/
            'short_description' => $this['short_description']['content'],
            'is_favourite' => $product && $user
                ? $user->favouriteProducts()->where('product_id', $product->id)->exists()
                : false,
        ];
    }

    /**
     * @return float
     */
    private function getPrice(): float {
        $existingProduct = Product::where('catalog_product_id', $this['catalog_product_id'])->first();

        return $existingProduct?->price
            ?? $this['buy_box_winner']['price']
            ?? $this->generateRandomPrice();
    }

    private function generateRandomPrice(): float {
        return round(mt_rand(1000, 5000) + mt_rand(0, 99) / 100, 2);
    }
}
