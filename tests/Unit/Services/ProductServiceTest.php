<?php

use App\Models\Product;
use App\Services\ProductService;
use App\Contracts\ProductRepositoryInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\OpinionRepositoryInterface;
use App\Models\Opinion;

/**
 * @param array $inputData
 * @return array
 */


/**
 * @param mixed $mockProduct
 * @param array $inputData
 * @return array
 */

it('creates a product opinion associated with the correct product and user', function () {
    $inputData = [
        'catalog_product_id' => 'SKU123',
        'name' => 'Product Name',
        'image' => 'img.jpg',
        'short_description' => 'Short description',
        'price' => 100.0,
        'rating' => 5,
        'content' => 'Very high quality',
    ];
    list($mockProduct, $productRepo) = getProductAndProductRepositoryMocks($inputData);

    $authService = Mockery::mock(AuthServiceInterface::class);
    $authService->shouldReceive('id')->once()->andReturn(42);

    list($mockOpinion, $opinionRepo) = getOpinionAndOpinionRepositoryMocks($mockProduct, $inputData);

    $service = new ProductService($productRepo, $opinionRepo, $authService);

    $result = $service->createProductOpinion($inputData);

    expect($result)->toBe($mockOpinion);
});



function getProductAndProductRepositoryMocks(array $inputData): array {
    $mockProduct = Mockery::mock(Product::class);
    $mockProduct->shouldReceive('getAttribute')->with('id')->andReturn(1);

    $productRepo = Mockery::mock(ProductRepositoryInterface::class);
    $productRepo->shouldReceive('firstOrCreate')
        ->once()
        ->with(
            ['catalog_product_id' => $inputData['catalog_product_id']],
            [
                'name' => $inputData['name'],
                'image' => $inputData['image'],
                'short_description' => $inputData['short_description'],
                'price' => $inputData['price'],
            ]
        )
        ->andReturn($mockProduct);
    return array($mockProduct, $productRepo);
}

function getOpinionAndOpinionRepositoryMocks(mixed $mockProduct, array $inputData): array
{
    $mockOpinion = Mockery::mock(Opinion::class);

    $opinionRepo = Mockery::mock(OpinionRepositoryInterface::class);
    $opinionRepo->shouldReceive('create')
        ->once()
        ->with([
            'product_id' => $mockProduct->id,
            'user_id' => 42,
            'rating' => $inputData['rating'],
            'content' => $inputData['content'],
        ])
        ->andReturn($mockOpinion);
    return array($mockOpinion, $opinionRepo);
}
