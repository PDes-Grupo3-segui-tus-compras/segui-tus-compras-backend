<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\OpinionRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Opinion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductService {

    /*
        * Nota: Esta inyeccion de dependecias fue agregada para poder emular lo que seria un Unit Test puro dejando a la funcionalidad de Active records
        * de Eloquent fuera de la ecuacion. No se realizara en todos los lugares, pero queriamos al menos dejar un ejemplo de Unit Test Puro.
    */

    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected OpinionRepositoryInterface $opinionRepository,
        protected AuthServiceInterface $authService,
        protected MercadoLibreService $mercadoLibreService
    ) {}

    public function createPurchase(array $data): Purchase {
        return DB::transaction(function () use ($data) {

            $this->validateProductExists($data['catalog_product_id']);

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
                'user_id' => $this->authService->id(),
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'purchase_date' => now(),
            ]);
        });
    }

    public function favouriteProduct(array $data): JsonResponse {
        return DB::transaction(function () use ($data) {
            $this->validateProductExists($data['catalog_product_id']);

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
        });
    }

    public function createProductOpinion(array $data): Opinion {
        return DB::transaction(function () use ($data) {
            $this->validateProductExists($data['catalog_product_id']);

            $product = $this->productRepository->firstOrCreate(
                ['catalog_product_id' => $data['catalog_product_id']],
                [
                    'name' => $data['name'],
                    'image' => $data['image'],
                    'short_description' => $data['short_description'],
                    'price' => $data['price']
                ]
            );

            return $this->opinionRepository->create([
                'product_id' => $product->id,
                'user_id' => $this->authService->id(),
                'rating' => $data['rating'],
                'content' => $data['content']
            ]);
        });
    }

    /**
     * @param $catalogProductId
     * @return void
     */
    public function validateProductExists($catalogProductId): void {
        try {
            $productInfo = $this->mercadoLibreService->getProductInformation($catalogProductId);
            if (!$productInfo)  {
                throw new ModelNotFoundException("Product was not found");
            }

        } catch (\Throwable $e) {
            throw new ModelNotFoundException("Product was not found.");
        }
    }
}
