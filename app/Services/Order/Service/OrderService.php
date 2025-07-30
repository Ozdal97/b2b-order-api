<?php

namespace App\Services\Order\Service;

use App\Services\Order\Repository\OrderRepository;
use App\Services\Permission\PermissionService;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Order işlemleri için repository nesnesi.
     *
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * İşlem sonucunda dönen sipariş verisi.
     *
     * @var array
     */
    public array $orders = [];

    /**
     * Repository örneğini hazırlar.
     */
    public function __construct()
    {
        // Servis katmanında kullanılacak repository’i oluştur
        $this->orderRepository = new OrderRepository();
    }

    /**
     * Kullanıcının veya adminin sipariş listesini getirir.
     *
     * @return array
     */
    public function getOrders(): array
    {
        // Yetki servisini başlat
        $permissionService = new PermissionService();

        // Eğer All_Orders yetkisi varsa tüm siparişleri al, yoksa sadece kendi siparişlerini al
        if ($permissionService->hasPermission('All_Orders')) {
            $data = $this->orderRepository->getAllOrders();
        } else {
            $data = $this->orderRepository->getUserOrders(Auth::id());
        }

        return $data;
    }

    /**
     * Yeni bir sipariş oluşturur.
     *
     * @param  array  $data  Sipariş kalemlerini içeren dizi
     * @return bool          Başarılıysa true, başarısızsa false
     */
    public function OrderCreate(array $data): bool
    {
        // Repository aracılığıyla siparişi oluştur ve sonucu sakla
        $this->orders = $this->orderRepository->createOrder($data);

        // Eğer boş döndüyse bir hata oluşmuş demektir
        if (empty($this->orders)) {
            return false;
        }

        return true;
    }

    /**
     * Belirli bir siparişi ID ile getirir.
     *
     * @param  int    $orderID  Sipariş ID’si
     * @return mixed            Sipariş verisi (dizi veya null)
     */
    public function getOrdersById(int $orderID)
    {
        // Repository’den belirtilen siparişi al
        return $this->orderRepository->getOrdersById($orderID);
    }
}
