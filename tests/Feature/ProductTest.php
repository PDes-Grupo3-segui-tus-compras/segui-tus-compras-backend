<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Purchase;
use App\Services\MercadoLibreService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/../Helpers/mercadoLibreMocks.php';
const PURCHASE_ENDPOINT = '/api/purchase';
const PRODUCT_NOT_FOUND = 'Product was not found.';
const EXAMPLE_IMAGE = 'https://example.com/image.jpg';

beforeEach(function () {
    $this->existingUserIds = User::pluck('id')->toArray();
    $this->existingProductIds = Product::pluck('id')->toArray();
    $this->existingPurchaseIds = Purchase::pluck('id')->toArray();
});

afterEach(function () {
    Purchase::whereNotIn('id', $this->existingPurchaseIds)->delete();
    Product::whereNotIn('id', $this->existingProductIds)->delete();
    User::whereNotIn('id', $this->existingUserIds)->delete();
});

it('creates a purchase and product if not exist', function () {
    $user = actingAsUser();

    $this->app->instance(MercadoLibreService::class, mockMercadoLibreServiceWithProduct([
        'catalog_product_id' => 'abc1234',
        'name' => 'Mock Product',
        'buy_box_winner' => ['price' => 99.99],
        'pictures' => [],
        'main_features' => [],
        'attributes' => [],
        'short_description' => ['content' => 'Mock description'],
    ]));

    $response = $this->postJson(PURCHASE_ENDPOINT, purchasePayload(
        [
            'catalog_product_id' => 'abc1234',
            'quantity' => 3,
            'price' => 200
        ]
    ));

    $response->assertStatus(201);
    $this->assertDatabaseHas('products', ['catalog_product_id' => 'abc1234']);
    $this->assertDatabaseHas('purchases', [
        'user_id' => $user->id,
        'quantity' => 3,
        'price' => 200
    ]);
});

it('does not duplicate product if it already exists', function () {
    $user = actingAsUser();
    $product = createExistingProduct(
        [
            'catalog_product_id' => 'abc1234',
            'price' => 200
        ]
    );

    $this->app->instance(MercadoLibreService::class, mockMercadoLibreServiceWithProduct([
        'catalog_product_id' => 'abc1234',
        'name' => 'Mock Product',
        'buy_box_winner' => ['price' => 99.99],
        'pictures' => [],
        'main_features' => [],
        'attributes' => [],
        'short_description' => ['content' => 'Mock description'],
    ]));

    $response = $this->postJson(PURCHASE_ENDPOINT, purchasePayload([
        'catalog_product_id' => 'abc1234',
        'quantity' => 2,
        'price' => 200
    ]));

    $response->assertStatus(201);

    $this->assertEquals(1, Product::where('catalog_product_id', 'abc1234')->count());

    $this->assertDatabaseHas('purchases', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 200.00,
    ]);
});

it('fails if product exists but price does not match', function () {
    createExistingProduct(['price' => 100.00]);
    actingAsUser();

    $response = $this->postJson(PURCHASE_ENDPOINT, purchasePayload([
        'price' => 200.00,
    ]));

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['price']);
});

//Mocked mercado libre fails tests:

it('does not create purchase or product if MercadoLibreService fails', function () {
    $user = actingAsUser();

    $mockMLService = \Mockery::mock(MercadoLibreService::class);
    $mockMLService->shouldReceive('getProductInformation')
        ->andThrow(new ModelNotFoundException(PRODUCT_NOT_FOUND));

    App::instance(MercadoLibreService::class, $mockMLService);

    $payload = purchasePayload([
        'catalog_product_id' => 'invalid123',
        'quantity' => 1,
        'price' => 100,
    ]);

    $response = $this->postJson(PURCHASE_ENDPOINT, $payload);

    $response->assertStatus(404);

    $this->assertDatabaseMissing('products', ['catalog_product_id' => 'invalid123']);

    $this->assertDatabaseMissing('purchases', ['user_id' => $user->id]);
});

it('does not create product or add to favourites if MercadoLibreService fails', function () {
    actingAsUser();

    $mockMLService = \Mockery::mock(MercadoLibreService::class);
    $mockMLService->shouldReceive('getProductInformation')
        ->andThrow(new ModelNotFoundException(PRODUCT_NOT_FOUND));

    App::instance(MercadoLibreService::class, $mockMLService);

    $payload = [
        'catalog_product_id' => 'invalid123',
        'name' => 'Invalid Product',
        'image' => EXAMPLE_IMAGE,
        'short_description' => 'Invalid description',
        'price' => 999.99
    ];

    $initialCount = DB::table('product_user')->count();

    $response = $this->putJson('/api/products/favourite', $payload);

    $response->assertStatus(404);

    $this->assertDatabaseMissing('products', ['catalog_product_id' => 'invalid123']);
    $this->assertDatabaseCount('product_user', $initialCount);
});

it('does not create product or opinion if MercadoLibreService fails', function () {
    $user = actingAsUser();

    $mockMLService = \Mockery::mock(MercadoLibreService::class);
    $mockMLService->shouldReceive('getProductInformation')
        ->andThrow(new ModelNotFoundException(PRODUCT_NOT_FOUND));

    App::instance(MercadoLibreService::class, $mockMLService);

    $payload = [
        'catalog_product_id' => 'invalid123',
        'name' => 'Invalid Product',
        'image' => EXAMPLE_IMAGE,
        'short_description' => 'Invalid description',
        'price' => 999.99,
        'rating' => 4,
        'content' => 'This is an invalid opinion'
    ];

    $response = $this->postJson('/api/opinions', $payload);

    $response->assertStatus(404);

    $this->assertDatabaseMissing('products', ['catalog_product_id' => 'invalid123']);
    $this->assertDatabaseMissing('opinions', ['user_id' => $user->id]);
});

function actingAsUser(): User {
    $user = User::factory()->create();
    test()->actingAs($user);
    return $user;
}

function purchasePayload(array $overrides = []): array {
    return array_merge([
        'catalog_product_id' => 'abc123',
        'name' => 'Test Product',
        'image' => EXAMPLE_IMAGE,
        'short_description' => 'Short desc',
        'quantity' => 2,
        'price' => 150
    ], $overrides);
}

function createExistingProduct(array $overrides = []): Product {
    return Product::create(array_merge([
        'catalog_product_id' => 'abc123',
        'name' => 'Producto existente',
        'image' => 'https://example.com/existing.jpg',
        'short_description' => 'Already created',
        'price' => 150
    ], $overrides));
}
