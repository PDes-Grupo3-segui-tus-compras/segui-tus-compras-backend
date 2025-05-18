<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory {
    protected $model = Product::class;

    public function definition(): array {
        return [
            'catalog_product_id' => $this->faker->unique()->ean8,
            'name' => $this->faker->word,
            'image' => $this->faker->imageUrl(),
            'short_description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
