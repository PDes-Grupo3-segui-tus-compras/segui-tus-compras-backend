<?php
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Hash;

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

it('allows any authenticated user to view any user profile', function () {
    $this->actingAs($this->user);

    $response = $this->getJson("/api/profile/{$this->admin->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'user' => [
                'id' => $this->admin->id,
                'email' => $this->admin->email,
                'user_type' => 'admin',
            ],
        ])
        ->assertJsonStructure([
            'success',
            'user' => [
                'id',
                'name',
                'email',
                'user_type',
                'created_at',
                'purchases_count',
                'favourites_count',
                'opinions_count',
            ],
        ]);
});

it('returns 401 if unauthenticated when accessing profile', function () {
    $response = $this->getJson("/api/profile/{$this->user->id}");
    $response->assertStatus(401);
});

it('allows authenticated user to change password', function () {
    $this->actingAs($this->user);

    $payload = [
        'current_password' => 'password',
        'new_password' => 'new_secure_pass123',
        'new_password_confirmation' => 'new_secure_pass123',
    ];

    $response = $this->postJson('/api/change-password', $payload);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    $this->user->refresh();
    expect(Hash::check('new_secure_pass123', $this->user->password))->toBeTrue();
});

it('fails to change password if current password is incorrect', function () {
    $this->actingAs($this->user);

    $payload = [
        'current_password' => 'wrong_password',
        'new_password' => 'new_secure_pass123',
        'new_password_confirmation' => 'new_secure_pass123',
    ];

    $response = $this->postJson('/api/change-password', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('current_password');
});

it('fails to change password if new password confirmation does not match', function () {
    $this->actingAs($this->user);

    $payload = [
        'current_password' => 'password',
        'new_password' => 'new_secure_pass123',
        'new_password_confirmation' => 'wrong_confirmation',
    ];

    $response = $this->postJson('/api/change-password', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('new_password');
});

it('returns 401 if unauthenticated when changing password', function () {
    $payload = [
        'current_password' => 'password',
        'new_password' => 'new_secure_pass123',
        'new_password_confirmation' => 'new_secure_pass123',
    ];

    $response = $this->postJson('/api/change-password', $payload);

    $response->assertStatus(401);
});
