<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
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

    public function favouriteProduct(array $data): JsonResponse {
        $user = Auth::user();

        $product = Product::firstOrCreate(
            ['catalog_product_id' => $data['catalog_product_id']],
            [
                'name' => $data['name'],
                'image' => $data['image'],
                'short_description' => $data['short_description'],
                'price' => $data['price'],
            ]
        );

        if ($user->favouriteProducts()->where('product_id', $product->id)->exists()) {
            $user->favouriteProducts()->detach($product->id);
            return response()->json([
                'message' => 'Product removed from favourites',
                'product' => $product,
            ]);
        }

        $user->favouriteProducts()->attach($product->id);
        return response()->json([
            'message' => 'Product added to favourites',
            'product' => $product,
        ]);
    }
}
