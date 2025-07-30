<?php

namespace App\Services\Product\Service;

use App\Models\Product;
use App\Services\Product\Repository\ProductRepository;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * Cache anahtarı için sabit tanım.
     */
    private const PRODUCTS_PREFIX = 'products';

    /**
     * Başarılı oluşturma/güncelleme sonrası erişilebilecek son ürün nesnesi.
     *
     * @var Product|null
     */
    public ?Product $product = null;

    /**
     * Repository bağımlılığını hazırlar.
     */
    public function __construct()
    {
        // Service katmanında kullanılacak repository örneğini al
        $this->productRepository = new ProductRepository();
    }

    /**
     * Tüm ürünleri cache üzerinden veya veritabanından döner.
     *
     * @return array
     */
    public function getProducts(): array
    {
        // Önce cache’e bak, eğer yoksa DB’den çek ve cache’e yaz
        $products = Cache::get(self::PRODUCTS_PREFIX);

        if (is_null($products)) {
            // TODO: İleride pagination/limit ekleyerek DB yükünü azalt
            $products = $this->productRepository->getAllProducts();
            // 600 saniye (10 dakika) boyunca cache’te tut
            Cache::put(self::PRODUCTS_PREFIX, $products, 600);
        }

        return $products;
    }

    /**
     * Yeni ürün oluşturur ve cache’i temizler.
     *
     * @param  array  $data
     * @return bool
     */
    public function createProduct(array $data): bool
    {
        // Repository aracılığıyla DB’ye kaydet
        $this->product = $this->productRepository->createProduct($data);

        if (is_null($this->product)) {
            return false;
        }

        // Yeni veri geldi, eskimiş cache’i sil
        $this->deleteProductCache();

        return true;
    }

    /**
     * Varolan ürünü günceller ve cache’i temizler.
     *
     * @param  array  $data
     * @param  int    $id
     * @return bool
     */
    public function updateProduct(array $data, int $id): bool
    {
        $response = $this->productRepository->updateProduct($data, $id);

        if ($response) {
            // Başarılı güncelleme sonrası cache’i temizle
            $this->deleteProductCache();
        }

        return $response;
    }

    /**
     * Belirtilen ID’li ürünü siler ve cache’i temizler.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $response = $this->productRepository->deleteProduct($id);

        if ($response) {
            // Silme işleminden sonra cache’i sıfırla
            $this->deleteProductCache();
        }

        return $response;
    }

    /**
     * Ürün listesinin cache’ini siler.
     *
     * @return void
     */
    private function deleteProductCache(): void
    {
        Cache::forget(self::PRODUCTS_PREFIX);
    }
}
