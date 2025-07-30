<?php

namespace App\Http\Controllers;

use App\Services\Order\Service\OrderService;
use App\Services\Permission\PermissionService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Tüm siparişleri listeler.
     *
     * @param  OrderService  $orderService
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(OrderService $orderService)
    {
        // Servisten siparişleri al ve JSON formatında döndür
        $orders = $orderService->getOrders();

        return response()->json([
            'order'  => $orders,
            'status' => true,
        ]);
    }

    /**
     * Yeni bir sipariş oluşturur.
     *
     * @param  Request            $request
     * @param  PermissionService  $permissionService
     * @param  OrderService       $orderService
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(
        Request $request,
        PermissionService $permissionService,
        OrderService $orderService
    ) {
        // Yalnızca müşteri sipariş oluşturmalı; admin yetkisi varsa engelle
        if ($permissionService->hasPermission('order_create')) {
            return response()->json([
                'message' => 'Admin sipariş oluşturamaz',
                'status'  => false,
            ], 401);
        }

        // Gelecek verileri doğrula
        $data = $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        // Servis ile siparişi yarat
        $created = $orderService->OrderCreate($data);

        if (! $created) {
            // Oluşturma başarısızsa hata döndür
            return response()->json([
                'message' => 'Sipariş oluşturulamadı',
                'status'  => false,
            ], 400);
        }

        // Başarılıysa servisteki son siparişi dön
        return response()->json([
            'order'  => $orderService->orders,
            'status' => true,
        ]);
    }

    /**
     * Belirli bir siparişi getirir.
     *
     * @param  int                $orderID
     * @param  PermissionService  $permissionService
     * @param  OrderService       $orderService
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(
        int $orderID,
        PermissionService $permissionService,
        OrderService $orderService
    ) {
        // Yalnızca admin veya sipariş sahibi erişebilmeli
        if (! $permissionService->hasPermission('order_update')) {
            return response()->json([
                'message' => 'Yetkisiz Erişim',
                'status'  => false,
            ], 401);
        }

        // Servis aracılığıyla siparişi al
        $order = $orderService->getOrdersById($orderID);

        return response()->json([
            'order'  => $order,
            'status' => true,
        ]);
    }
}
