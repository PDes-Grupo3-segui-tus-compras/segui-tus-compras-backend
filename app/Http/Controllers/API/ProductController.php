<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavouriteRequest;
use App\Http\Requests\PurchaseRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller {
    public function __construct(private ProductService $service) {}

    public function purchase(PurchaseRequest $request): JsonResponse {
        $purchase = $this->service->createPurchase($request->validated());

        return response()->json([
            'message' => 'Purchase was successful',
            'purchase' => $purchase,
        ], 201);
    }

    public function favourite(FavouriteRequest $request): JsonResponse {
        return $this->service->favouriteProduct($request->validated());
    }
}
