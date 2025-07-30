<?php

namespace Tests\Unit;

use App\Services\Order\Service\OrderService;
use App\Services\Order\Repository\OrderRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_OrderCreate_returns_true_and_sets_orders()
    {
        $input = [
            'items' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ];

        // Repository mock’u oluştur
        $repoMock = Mockery::mock(OrderRepository::class);
        $repoMock
            ->shouldReceive('createOrder')
            ->once()
            ->with($input)
            ->andReturn(['id' => 5, 'items' => [['product_id'=>1,'quantity'=>2]]]);

        // Service’e mock’u enjekte et
        $service = new OrderService();
        $ref   = new \ReflectionObject($service);
        $prop  = $ref->getProperty('orderRepository');
        $prop->setAccessible(true);
        $prop->setValue($service, $repoMock);

        // Çağrıyı yap
        $result = $service->OrderCreate($input);

        $this->assertTrue($result);
        $this->assertEquals(5, $service->orders['id']);
    }

    public function test_OrderCreate_returns_false_on_empty()
    {
        $input = ['items' => []];

        $repoMock = Mockery::mock(OrderRepository::class);
        $repoMock
            ->shouldReceive('createOrder')
            ->once()
            ->with($input)
            ->andReturn([]);

        $service = new OrderService();
        $ref     = new \ReflectionObject($service);
        $prop    = $ref->getProperty('orderRepository');
        $prop->setAccessible(true);
        $prop->setValue($service, $repoMock);

        $this->assertFalse($service->OrderCreate($input));
    }

    public function test_getOrdersById_delegates_to_repository()
    {
        $orderID = 42;
        $expected = ['id'=>42,'items'=>[]];

        $repoMock = Mockery::mock(OrderRepository::class);
        $repoMock
            ->shouldReceive('getOrdersById')
            ->once()
            ->with($orderID)
            ->andReturn($expected);

        $service = new OrderService();
        $ref     = new \ReflectionObject($service);
        $prop    = $ref->getProperty('orderRepository');
        $prop->setAccessible(true);
        $prop->setValue($service, $repoMock);

        $actual = $service->getOrdersById($orderID);
        $this->assertSame($expected, $actual);
    }
}
