<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected string $customerToken;
    protected string $adminToken;
    protected int $productA;

    protected function setUp(): void
    {
        parent::setUp();

        // Kullanıcıları oluştur
        $admin = User::factory()->admin()->create();
        $cust  = User::factory()->create();

        $this->adminToken    = $admin->createToken('t')->plainTextToken;
        $this->customerToken = $cust->createToken('t')->plainTextToken;

        // Test içinde kullanılacak ürün
        $this->productA = Product::factory()->create()->id;
    }

    public function test_customer_can_create_order()
    {
        $payload = [
            'items' => [
                ['product_id' => $this->productA, 'quantity' => 3],
            ]
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->postJson('/api/orders', $payload);

        $res->assertStatus(201)
            ->assertJsonStructure([
                'order' => ['id','user_id','status','total_price','items'],
                'status'
            ]);

        // Veritabanında sipariş ve item kontrolü
        $this->assertDatabaseHas('orders', [
            'user_id'     => User::where('role','customer')->first()->id,
            'status'      => 'pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->productA,
            'quantity'   => 3,
        ]);
    }

    public function test_admin_cannot_create_order()
    {
        $payload = [
            'items' => [
                ['product_id' => $this->productA, 'quantity' => 1],
            ]
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/orders', $payload);

        // Kodunuzda 401 veya 403 dönüyorsa buna göre ayarlayın
        $res->assertStatus(401);
    }

    public function test_unauthenticated_cannot_create_order()
    {
        $res = $this->postJson('/api/orders', [
            'items' => [['product_id' => $this->productA, 'quantity' => 1]],
        ]);

        $res->assertStatus(401);
    }

    public function test_customer_can_list_own_orders_only()
    {
        $cust = User::where('role','customer')->first();
        $other = User::factory()->create();

        // Bir müşteri A için sipariş
        $orderA = Order::factory()->create(['user_id'=>$cust->id]);
        OrderItem::factory()->create([
            'order_id'   => $orderA->id,
            'product_id' => $this->productA,
            'quantity'   => 2,
            'unit_price' => 10,
        ]);

        // Başka müşteri B için sipariş
        $orderB = Order::factory()->create(['user_id'=>$other->id]);
        OrderItem::factory()->create([
            'order_id'   => $orderB->id,
            'product_id' => $this->productA,
            'quantity'   => 1,
            'unit_price' => 10,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->getJson('/api/orders');

        $res->assertStatus(200)
            ->assertJsonCount(1, 'order')
            ->assertJsonPath('order.0.user_id', $cust->id);
    }

    public function test_admin_can_list_all_orders()
    {
        // Toplamda 3 sipariş
        Order::factory()->count(3)->create();
        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/orders');

        $res->assertStatus(200)
            ->assertJsonCount(3, 'order');
    }
}
