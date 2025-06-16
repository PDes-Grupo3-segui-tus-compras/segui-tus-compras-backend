<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;

    /**
    *
    * title="Metrics",
    * description="Endpoints involving metrics"
    */
class MetricsController extends Controller {


    /**
     * @OA\Get(
     *     path="/api/metrics",
     *     summary="Get general metrics",
     *     description="Returns the top 5 users with the most purchases, the top 5 most purchased products, and the top 5 most favorited products. Accessible only by administrator users.",
     *     tags={"Metrics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Metrics obtained correctly",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="top_five_users",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Juan Pérez"),
     *                     @OA\Property(property="total", type="integer", example=8)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="top_five_purchased",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="catalog_product_id", type="string", example="MLA123456"),
     *                     @OA\Property(property="name", type="string", example="Smart TV 55 pulgadas"),
     *                     @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                     @OA\Property(property="total", type="integer", example=15),
     *                     @OA\Property(property="times_purchased", type="integer", example=5)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="top_five_favourites",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="catalog_product_id", type="string", example="MLA654321"),
     *                     @OA\Property(property="name", type="string", example="Auriculares inalámbricos"),
     *                     @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                     @OA\Property(property="total", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error: Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado para acceder a este recurso",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorise to access this user data")
     *         )
     *     )
     * )
     */
    public function getMetrics(): JsonResponse {
        $topFiveUsers = User::topBuyers()
            ->get(['id', 'name'])
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'total' => $user->purchases_count,
            ]);

        $topFivePurchased = Purchase::topPurchasedProducts()
            ->get()
            ->map(fn($purchase) => [
                'catalog_product_id' => $purchase->product->catalog_product_id,
                'name' => $purchase->product->name,
                'image' => $purchase->product->image,
                'total' => $purchase->total_quantity,
                'times_purchased' => $purchase->times_purchased,
            ]);

        $topFiveFavourites = Product::topFavourites()
            ->get(['id', 'name', 'catalog_product_id', 'image'])
            ->map(fn($product) => [
                'catalog_product_id' => $product->catalog_product_id,
                'name' => $product->name,
                'image' => $product->image,
                'total' => $product->favourited_by_count
            ]);

        return response()->json([
            'top_five_users' => $topFiveUsers,
            'top_five_purchased' => $topFivePurchased,
            'top_five_favourites' => $topFiveFavourites,
        ]);
    }
}
