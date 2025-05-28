<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavouriteRequest;
use App\Http\Requests\PurchaseRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

/**
  * 
  * title="Products",
  * description="Endpoints involving purchases, and adding and removing products from favourites"
  */
class ProductController extends Controller {
    public function __construct(private ProductService $service) {}

    /**
     * @OA\Post(
     *     path="/api/purchase",
     *     summary="Register a product purchase",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"catalog_product_id", "name", "image", "quantity", "price"},
     *             @OA\Property(property="catalog_product_id", type="string", example="MLA29815169"),
     *             @OA\Property(property="name", type="string", example="Luke Skywalker Star Wars Kenner Star Wars"),
     *             @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="short_description", type="string", nullable=true, example="Muñeco starwars Test"),
     *             @OA\Property(property="quantity", type="integer", example=1),
     *             @OA\Property(property="price", type="number", format="float", example=599.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Purchase registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Purchase was successful"),
     *             @OA\Property(property="purchase", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=5),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", format="float", example=599.99),
     *                 @OA\Property(property="purchase_date", type="string", format="date-time", example="2024-06-01T15:03:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="catalog_product_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The catalog product id field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function purchase(PurchaseRequest $request): JsonResponse {
        $purchase = $this->service->createPurchase($request->validated());

        return response()->json([
            'message' => 'Purchase was successful',
            'purchase' => $purchase,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/favourite",
     *     summary="Toggle product as favourite",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"catalog_product_id", "name", "image", "quantity", "price"},
     *             @OA\Property(property="catalog_product_id", type="string", example="MLA29815169"),
     *             @OA\Property(property="name", type="string", example="Luke Skywalker Star Wars Kenner Star Wars"),
     *             @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="short_description", type="string", nullable=true, example="Muñeco starwars Test"),
     *             @OA\Property(property="price", type="number", format="float", example=599.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Favourite toggled",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to favourites"),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="catalog_product_id", type="string", example="MLA29815169"),
     *                 @OA\Property(property="name", type="string", example="PlayStation 5"),
     *                 @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                 @OA\Property(property="short_description", type="string", example="Consola de videojuegos de última generación"),
     *                 @OA\Property(property="price", type="number", example=599.99)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                       property="catalog_product_id",
     *                       type="array",
     *                       @OA\Items(type="string", example="The catalog_product_id field is required.")
     *                  ),
     *             )
     *         )
     *     )
     * )
     */
    public function favourite(FavouriteRequest $request): JsonResponse {
        return $this->service->favouriteProduct($request->validated());
    }
}
