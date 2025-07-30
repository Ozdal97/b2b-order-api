<?php

namespace App\Services\Product\Repository;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductRepository
{
    /**
     * Veritabanındaki tüm ürünleri dizi halinde döner.
     *
     * @return array
     */
    public function getAllProducts(): array
    {
        try {
            // Eloquent ile tüm ürünleri çek ve diziye çevir
            return Product::all()->toArray();
        } catch (\Exception $exception) {
            // Hata durumunda log kaydı al, boş dizi dön
            Log::error('ProductRepository@getAllProducts failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        return [];
    }

    /**
     * Yeni bir ürün kaydı oluşturur.
     *
     * @param  array       $data
     * @return Product|null
     */
    public function createProduct(array $data): ?Product
    {
        try {
            // Mass assignment ile yeni ürünü kaydet
            return Product::create($data);
        } catch (\Exception $exception) {
            // Oluşturma hatasında log kaydı tut
            Log::error('ProductRepository@createProduct failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        return null;
    }

    /**
     * Varolan bir ürünü günceller.
     *
     * @param  array  $data
     * @param  int    $id
     * @return bool
     */
    public function updateProduct(array $data, int $id): bool
    {
        try {
            // ID ile ürünü bul ve verilen alanları update et
            $updated = Product::where('id', $id)->update($data);

            return (bool) $updated;
        } catch (\Exception $exception) {
            // Hata durumunda log
            Log::error('ProductRepository@updateProduct failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        return false;
    }

    /**
     * Belirtilen ID’li ürünü siler.
     *
     * @param  int   $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        try {
            // Şimdilik hard delete; ileride soft delete tercih edilebilir
            $deleted = Product::where('id', $id)->delete();

            return (bool) $deleted;
        } catch (\Exception $exception) {
            // Silme işlemi hata verirse log kaydı
            Log::error('ProductRepository@deleteProduct failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        return false;
    }
}
