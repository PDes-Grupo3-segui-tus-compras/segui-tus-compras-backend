<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Product;

class PurchaseFactory extends Factory {
    protected $model = Purchase::class;

    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'purchase_date' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
