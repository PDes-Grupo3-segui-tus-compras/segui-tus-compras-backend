<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Opinion;
use App\Services\MercadoLibreService;
require_once __DIR__ . '/../Helpers/mercadoLibreMocks.php';




beforeEach(function () {
    $this->existingUserIds = User::pluck('id')->toArray();
    $this->existingProductIds = Product::pluck('id')->toArray();
    $this->existingOpinionIds = Opinion::pluck('id')->toArray();
});

afterEach(function () {
    Mockery::close();
    Opinion::whereNotIn('id', $this->existingOpinionIds)->delete();
    Product::whereNotIn('id', $this->existingProductIds)->delete();
    User::whereNotIn('id', $this->existingUserIds)->delete();
});

// INDEX

it('returns all opinions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $opinions = Opinion::factory()->count(3)->create();
    $opinionsQty = Opinion::all()->count();

    $response = $this->getJson(route('opinions.index'));

    $response->assertOk()
        ->assertJsonCount($opinionsQty);
});

// SHOW

it('returns a single opinion', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $opinion = Opinion::factory()->create();

    $response = $this->getJson(route('opinions.show', $opinion));

    $response->assertOk()
        ->assertJsonPath('data.id', $opinion->id);
});

// STORE

it('fails to store opinion with missing fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson(route('opinions.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'catalog_product_id',
            'name',
            'image',
            'price',
            'rating',
            'content',
        ]);
});

it('allows posting a new opinion if none exists for product', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->app->instance(MercadoLibreService::class, mockMercadoLibreServiceWithProduct([
        'catalog_product_id' => 'SKU999',
        'name' => 'Mock Product',
        'buy_box_winner' => ['price' => 99.99],
        'pictures' => [],
        'main_features' => [],
        'attributes' => [],
        'short_description' => ['content' => 'Mock description'],
    ]));

    $payload = [
        'catalog_product_id' => 'SKU999',
        'name' => 'Test Product',
        'image' => 'https://example.com/image.jpg',
        'short_description' => 'Test description',
        'price' => 99.99,
        'rating' => 4,
        'content' => 'Buena calidad'
    ];

    $response = $this->postJson(route('opinions.store'), $payload);

    $response->assertCreated();

    $this->assertDatabaseHas('opinions', [
        'user_id' => $user->id,
        'rating' => 4,
        'content' => 'Buena calidad',
    ]);
});

it('rejects a second opinion for the same product by the same user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['catalog_product_id' => 'SKU456']);

    $opinion = Opinion::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $payload = [
        'catalog_product_id' => 'SKU456',
        'name' => 'Producto Ignorado',
        'image' => 'https://example.com/image.jpg',
        'short_description' => 'Ignorado',
        'price' => 123.45,
        'rating' => 3,
        'content' => 'Intento duplicado'
    ];

    $response = $this->postJson(route('opinions.store'), $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['catalog_product_id']);
});

// UPDATE

it('updates an opinion if owned by the authenticated user', function () {
    $user = User::factory()->create();

    $opinion = Opinion::factory()->for($user)->create([
        'rating' => 3,
        'content' => 'Old content',
    ]);

    $this->actingAs($user);

    $response = $this->putJson(route('opinions.update', $opinion), [
        'rating' => 5,
        'content' => 'Updated content',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.rating', 5)
        ->assertJsonPath('data.content', 'Updated content');
});

it('prevents unauthorized user from updating another user\'s opinion', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $opinion = Opinion::factory()->for($otherUser)->create();

    $this->actingAs($user);

    $response = $this->putJson(route('opinions.update', $opinion), [
        'rating' => 1,
        'content' => 'Malísimo',
    ]);

    $response->assertForbidden();
});

// DELETE

it('deletes an opinion if user is owner and admin', function () {
    $user = User::factory()->create(['user_type' => 'admin']);

    $opinion = Opinion::factory()->for($user)->create();

    $this->actingAs($user);

    $response = $this->deleteJson(route('opinions.destroy', $opinion));

    $response->assertOk()
        ->assertJson(['message' => 'Opinion successfully deleted']);
});

it('prevents deleting if user is not owner or not admin', function () {
    $owner = User::factory()->create(['user_type' => 'regular']);
    $other = User::factory()->create(['user_type' => 'regular']);

    $opinion = Opinion::factory()->for($owner)->create();

    $this->actingAs($other);

    $response = $this->deleteJson(route('opinions.destroy', $opinion));

    $response->assertForbidden();
});
