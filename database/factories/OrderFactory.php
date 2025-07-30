<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id'     => User::factory(), // ya da spesifik ID verin
            'status'      => $this->faker->randomElement(['pending','approved','shipped']),
            'total_price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
