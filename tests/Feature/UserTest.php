<?php
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;

beforeEach(function () {
    $this->existingUserIds = User::pluck('id')->toArray();
    $this->existingProductIds = Product::pluck('id')->toArray();
    $this->existingPurchaseIds = Purchase::pluck('id')->toArray();
    $this->user = User::factory()->create(['user_type' => 'user']);
    $this->admin = User::factory()->create(['user_type' => 'admin']);
    $this->products = Product::factory()->count(3)->create();
    $this->user->favouriteProducts()->sync($this->products->pluck('id')->toArray());
    foreach ($this->products as $product) {
        Purchase::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'purchase_date' => now()->subDay(),
        ]);
    }
});

afterEach(function () {
    Purchase::whereNotIn('id', $this->existingPurchaseIds)->delete();
    Product::whereNotIn('id', $this->existingProductIds)->delete();
    User::whereNotIn('id', $this->existingUserIds)->delete();
});

it('allows authenticated user to get their favourites', function () {
    $this->actingAs($this->user);

    $response = $this->getJson("/api/users/{$this->user->id}/favourites");

    $response->assertOk();
    $response->assertJsonCount(3);
    $response->assertJsonFragment([
        'id' => $this->products[0]->id,
        'catalog_product_id' => $this->products[0]->catalog_product_id,
    ]);
});

it('allows admin user to get any user favourites', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson("/api/users/{$this->user->id}/favourites");

    $response->assertOk();
    $response->assertJsonCount(3);
});

it('does not allow non-admin to get other users favourites', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser);

    $response = $this->getJson("/api/users/{$this->user->id}/favourites");

    $response->assertStatus(403)
        ->assertJson(['error' => 'Unauthorise to access this user data']);
});

it('allows authenticated user to get their purchases', function () {
    $this->actingAs($this->user);

    $response = $this->getJson("/api/users/{$this->user->id}/purchases");

    $response->assertOk();
    $response->assertJsonCount(3);
    $response->assertJsonFragment([
        'id' => $this->products[0]->id,
        'catalog_product_id' => $this->products[0]->catalog_product_id,
        'quantity' => 2,
    ]);
});

it('allows admin user to get any user purchases', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson("/api/users/{$this->user->id}/purchases");

    $response->assertOk();
    $response->assertJsonCount(3);
});

it('does not allow non-admin to get other users purchases', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser);

    $response = $this->getJson("/api/users/{$this->user->id}/purchases");

    $response->assertStatus(403)
        ->assertJson(['error' => 'Unauthorise to access this user data']);
});

it('returns 401 if unauthenticated', function () {
    $response = $this->getJson("/api/users/{$this->user->id}/favourites");
    $response->assertStatus(401);

    $response = $this->getJson("/api/users/{$this->user->id}/purchases");
    $response->assertStatus(401);
});
