<?php

namespace Database\Factories;

use App\Models\Opinion;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpinionFactory extends Factory {
    protected $model = Opinion::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'content' => $this->faker->paragraph,
        ];
    }
}
