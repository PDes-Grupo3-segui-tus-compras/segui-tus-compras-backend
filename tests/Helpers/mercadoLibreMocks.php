<?php

use App\Http\Resources\ProductResource;
use App\Services\MercadoLibreService;

function mockMercadoLibreServiceWithProduct(array $productData) {
    $mercadoLibreMock = Mockery::mock(MercadoLibreService::class);
    $mercadoLibreMock->shouldReceive('getProductInformation')
        ->andReturnUsing(function ($catalogProductId) use ($productData) {
            if ($catalogProductId === $productData['catalog_product_id']) {
                return new ProductResource($productData);
            }
            return null;
        });
    return $mercadoLibreMock;
}
