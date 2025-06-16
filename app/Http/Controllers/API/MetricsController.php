<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;

class MetricsController extends Controller {

    public function getMetrics(): JsonResponse {
        $topFiveUsers = User::topBuyers()
            ->get(['id', 'name'])
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'total_purchases' => $user->purchases_count,
            ]);

        $topFivePurchased = Purchase::topPurchasedProducts()
            ->get()
            ->map(fn($purchase) => [
                'catalog_product_id' => $purchase->product->catalog_product_id,
                'name' => $purchase->product->name,
                'image' => $purchase->product->image,
                'total_purchased_quantity' => $purchase->total_quantity,
                'times_purchased' => $purchase->times_purchased,
            ]);

        $topFiveFavourites = Product::topFavourites()
            ->get(['id', 'name', 'catalog_product_id', 'image'])
            ->map(fn($product) => [
                'catalog_product_id' => $product->catalog_product_id,
                'name' => $product->name,
                'image' => $product->image,
                'total_favourites' => $product->users_count,
            ]);

        return response()->json([
            'top_five_users' => $topFiveUsers,
            'top_five_purchased' => $topFivePurchased,
            'top_five_favourites' => $topFiveFavourites,
        ]);
    }
}
