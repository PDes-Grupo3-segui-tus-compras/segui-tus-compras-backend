<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

class ProductService {
    public function createPurchase(array $data): Purchase {
        $product = Product::firstOrCreate(
            ['catalog_product_id' => $data['catalog_product_id']],
            [
                'name' => $data['name'],
                'image' => $data['image'],
                'short_description' => $data['short_description'],
                'price' => $data['price'],
            ]
        );

        return Purchase::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'quantity' => $data['quantity'],
            'price' => $data['price'],
            'purchase_date' => now(),
        ]);
    }
}
