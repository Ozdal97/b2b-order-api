<?php

namespace Tests\Unit;

use App\Services\Product\Service\ProductService;
use App\Services\Product\Repository\ProductRepository;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getProducts_returns_cached_if_exists()
    {
        // Cache’de hazır veri varmış gibi davran
        Cache::shouldReceive('get')
            ->once()
            ->with('products')
            ->andReturn([['id'=>1,'name'=>'A']]);

        // Repository hiç çağrılmamalı
        $repoMock = Mockery::mock(ProductRepository::class);
        $repoMock->shouldNotReceive('getAllProducts');

        // Service’e mock’ları enjeksiyon yapacağız via reflection
        $service = new ProductService();
        $this->setProtectedProperty($service, 'productRepository', $repoMock);

        $result = $service->getProducts();
        $this->assertEquals([['id'=>1,'name'=>'A']], $result);
    }

    public function test_getProducts_fetches_and_caches_if_missing()
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('products')
            ->andReturnNull();

        $dbList = [['id'=>2,'name'=>'B']];
        $repoMock = Mockery::mock(ProductRepository::class);
        $repoMock->shouldReceive('getAllProducts')
            ->once()
            ->andReturn($dbList);

        Cache::shouldReceive('put')
            ->once()
            ->with('products', $dbList, 600);

        $service = new ProductService();
        $this->setProtectedProperty($service, 'productRepository', $repoMock);

        $result = $service->getProducts();
        $this->assertEquals($dbList, $result);
    }

    /**
     * Protected property setter helper
     */
    private function setProtectedProperty($object, $property, $value)
    {
        $ref = new \ReflectionObject($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }
}
