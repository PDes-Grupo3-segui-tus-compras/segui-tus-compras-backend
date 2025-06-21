<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;


const METRICS_URL = '/api/metrics';

beforeEach(function () {
    $this->existingUserIds = User::pluck('id')->toArray();
    $this->existingProductIds = Product::pluck('id')->toArray();
    $this->existingPurchaseIds = Purchase::pluck('id')->toArray();

    $this->admin = User::factory()->create(['user_type' => 'admin']);
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();

    $this->products = Product::factory()->count(3)->create();

    $this->user1->favouriteProducts()->sync($this->products->pluck('id')->toArray());
    $this->user2->favouriteProducts()->sync([$this->products[0]->id]);

    Purchase::factory()->create([
        'user_id' => $this->user1->id,
        'product_id' => $this->products[0]->id,
        'quantity' => 5,
    ]);

    Purchase::factory()->create([
        'user_id' => $this->user1->id,
        'product_id' => $this->products[1]->id,
        'quantity' => 3,
    ]);

    Purchase::factory()->create([
        'user_id' => $this->user2->id,
        'product_id' => $this->products[0]->id,
        'quantity' => 2,
    ]);
});

afterEach(function () {
    Purchase::whereNotIn('id', $this->existingPurchaseIds)->delete();
    Product::whereNotIn('id', $this->existingProductIds)->delete();
    User::whereNotIn('id', $this->existingUserIds)->delete();
});

it('allows admin to access metrics endpoint and returns expected structure', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson(METRICS_URL);

    $response->assertOk()
        ->assertJsonStructure([
            'top_five_users' => [['id', 'name', 'total']],
            'top_five_purchased' => [[
                'catalog_product_id',
                'name',
                'image',
                'total',
                'times_purchased'
            ]],
            'top_five_favourites' => [[
                'catalog_product_id',
                'name',
                'image',
                'total'
            ]],
        ]);
});

it('returns the correct top buyer in the metrics', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson(METRICS_URL);

    $topUser = User::withCount('purchases')
        ->orderByDesc('purchases_count')
        ->first();

    expect($response['top_five_users'][0]['id'])->toBe($topUser->id);
});

it('returns the correct top purchased product in the metrics', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson(METRICS_URL);

    $topPurchase = Purchase::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
        ->groupBy('product_id')
        ->orderByDesc('total_quantity')
        ->first();

    $topPurchaseProduct = Product::find($topPurchase->product_id);

    expect($response['top_five_purchased'][0]['catalog_product_id'])->toBe($topPurchaseProduct->catalog_product_id);
});

it('returns the correct top liked product in the metrics', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson(METRICS_URL);

    $topLiked = Product::withCount('favouritedBy')
        ->orderByDesc('favourited_by_count')
        ->first();

    expect($response['top_five_favourites'][0]['catalog_product_id'])->toBe($topLiked->catalog_product_id);
});

it('does not allow non-admin user to access metrics', function () {
    $this->actingAs($this->user1);

    $response = $this->getJson(METRICS_URL);

    $response->assertStatus(403);
});

it('does not allow unauthenticated access to metrics', function () {
    $response = $this->getJson(METRICS_URL);

    $response->assertStatus(401);
});
