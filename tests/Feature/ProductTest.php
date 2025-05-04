<?php

namespace Tests\Feature;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a purchase and product if not exist', function () {
    $user = actingAsUser();

    $response = $this->postJson('/api/purchase', purchasePayload(
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

    $response = $this->postJson('/api/purchase', purchasePayload([
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

    $response = $this->postJson('/api/purchase', purchasePayload([
        'price' => 200.00,
    ]));

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['price']);
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
        'image' => 'https://example.com/image.jpg',
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
