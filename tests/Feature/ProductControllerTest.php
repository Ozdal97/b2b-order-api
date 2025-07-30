<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seeder veya factory ile örnek admin/customer yarat
        User::factory()->create([
            'email' => 'admin@demo.com',
            'role'  => 'admin',
            'password' => bcrypt('password'),
        ]);
        User::factory()->create([
            'email' => 'user@demo.com',
            'role'  => 'customer',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_admin_can_create_product()
    {
        $admin = User::where('role','admin')->first();
        $token = $admin->createToken('t')->plainTextToken;

        $payload = [
            'name' => 'Yeni Ürün',
            'sku'  => 'NEW-SKU',
            'price'=> 99.99,
            'stock_quantity' => 5,
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['sku'=>'NEW-SKU']);

        // Veritabanında gerçekten var mı?
        $this->assertDatabaseHas('products', ['sku'=>'NEW-SKU']);
    }

    public function test_customer_cannot_create_product()
    {
        $user = User::where('role','customer')->first();
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/products', [
                'name'=>'X','sku'=>'X','price'=>1,'stock_quantity'=>1
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_products()
    {
        // Login olmadan istekte bulun
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_products()
    {
        // 3 ürün ekle
        Product::factory()->count(3)->create();

        $user = User::where('role','customer')->first();
        $token = $user->createToken('t')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'products');
    }
}
