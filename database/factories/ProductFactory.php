<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name'           => $this->faker->word(),
            'sku'            => strtoupper($this->faker->unique()->bothify('SKU-###')),
            'price'          => $this->faker->randomFloat(2, 1, 1000),
            'stock_quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
