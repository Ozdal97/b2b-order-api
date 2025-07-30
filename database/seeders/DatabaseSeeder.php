<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Kullanıcılar
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@demo.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $customer = User::create([
            'name'     => 'Customer User',
            'email'    => 'user@demo.com',
            'password' => Hash::make('password'),
            'role'     => 'customer',
        ]);

        // Ürünler
        $products = collect([
            ['name' => 'Ürün A', 'sku' => 'SKU-A', 'price' => 100, 'stock_quantity' => 50],
            ['name' => 'Ürün B', 'sku' => 'SKU-B', 'price' => 200, 'stock_quantity' => 30],
            ['name' => 'Ürün C', 'sku' => 'SKU-C', 'price' => 150, 'stock_quantity' => 20],
        ])->map(fn($data) => Product::create($data));

        // Sipariş (Customer için örnek)
        $order = Order::create([
            'user_id'     => $customer->id,
            'status'      => 'pending',
            'total_price' => 0,
        ]);

        // OrderItem pivot verisi
        $total = 0;
        foreach ($products->take(2) as $prod) {
            $quantity = rand(1, 3);
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $prod->id,
                'quantity'   => $quantity,
                'unit_price' => $prod->price,
            ]);
            $total += $prod->price * $quantity;
        }

        // Toplam fiyat güncelle
        $order->update(['total_price' => $total]);
    }
}
