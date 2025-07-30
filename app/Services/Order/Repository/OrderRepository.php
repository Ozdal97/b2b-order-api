<?php

namespace App\Services\Order\Repository;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderRepository
{
    /**
     * Tüm siparişleri getirir.
     *
     * @return array
     */
    public function getAllOrders(): array
    {
        try {
            // Tüm sipariş kayıtlarını al ve diziye çevir
            return Order::all()->toArray();
        } catch (\Exception $exception) {
            // Hata durumunda log kaydı tut
            Log::error('OrderRepository@getAllOrders failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        // Hata durumunda boş dizi dön
        return [];
    }

    /**
     * Belirli bir kullanıcıya ait siparişleri getirir.
     *
     * @param  int   $userId
     * @return array
     */
    public function getUserOrders(int $userId): array
    {
        try {
            // user_id eşleşen kayıtları al
            return Order::where('user_id', $userId)->get()->toArray();
        } catch (\Exception $exception) {
            Log::error('OrderRepository@getUserOrders failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        return [];
    }

    /**
     * Yeni bir sipariş oluşturur ve tamamını ilişkili kalemleriyle beraber döner.
     *
     * @param  array  $data  'items' dizisi: her eleman ['product_id', 'quantity']
     * @return array        Oluşturulan siparişin ilişkili verileri (order + items + products)
     */
    public function createOrder(array $data): array
    {
        try {
            // Transaction ile atomik işlemi sağla
            $order = DB::transaction(function () use ($data) {
                // Yeni sipariş kaydı
                $order = Order::create([
                    'user_id'     => Auth::id(),
                    'status'      => 'pending',
                    'total_price' => 0,
                ]);

                $total = 0;

                // Her bir kalem için pivot kaydı oluştur
                foreach ($data['items'] as $item) {
                    $prod = Product::findOrFail($item['product_id']);

                    $order->items()->create([
                        'product_id' => $prod->id,
                        'quantity'   => $item['quantity'],
                        'unit_price' => $prod->price,
                    ]);

                    // Toplam tutarı hesapla
                    $total += $prod->price * $item['quantity'];
                }

                // Toplam fiyat bilgisini güncelle
                $order->update(['total_price' => $total]);

                // İlişkili kalemleri yükleyerek geri dön
                return $order->load('items.product');
            });

            // Order nesnesini diziye çevir ve döndür
            return $order->toArray();
        } catch (\Exception $exception) {
            Log::error('OrderRepository@createOrder failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        return [];
    }

    /**
     * Belirli bir siparişi ID ile detaylı getirir.
     *
     * @param  int   $orderID
     * @return array
     */
    public function getOrdersById(int $orderID): array
    {
        try {
            // İlişkili kalemleriyle birlikte siparişi al
            return Order::with('items.product')
                ->findOrFail($orderID)
                ->toArray();
        } catch (\Exception $exception) {
            Log::error('OrderRepository@getOrdersById failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        return [];
    }
}
