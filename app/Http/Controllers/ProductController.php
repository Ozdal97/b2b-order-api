<?php

namespace App\Http\Controllers;

use App\Services\Permission\PermissionService;
use App\Services\Product\Service\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Ürün listesini döner.
     *
     * @param ProductService $productService
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ProductService $productService): \Illuminate\Http\JsonResponse
    {
        // Servisten tüm ürünleri çek ve JSON formatında geri gönder
        $products = $productService->getProducts();

        return response()->json([
            'products' => $products,
            'status'   => true,
        ]);
    }

    /**
     * Yeni bir ürün oluşturur.
     *
     * @param Request           $request
     * @param PermissionService $permissionService
     * @param ProductService    $productService
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(
        Request $request,
        PermissionService $permissionService,
        ProductService $productService
    ) {
        // Kullanıcının ürün oluşturma yetkisi var mı? Yoksa 401 döndür.
        if (!$permissionService->hasPermission('Product_create')) {
            return response()->json([
                'message' => 'Yetkisiz Erişim',
                'status'  => false,
            ], 401);
        }

        // Gelecek verileri doğrula
        $data = $request->validate([
            'name'           => 'required|string',
            'sku'            => 'required|unique:products,sku',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        // Servis yardımıyla ürünü oluştur
        $created = $productService->createProduct($data);

        // Oluşturma başarısızsa hata döndür
        if (! $created) {
            return response()->json([
                'message' => 'Ürün oluşturulamadı',
                'status'  => false,
            ], 400);
        }

        // Başarılıysa yeni ürünü ve durumu gönder
        return response()->json([
            'product' => $productService->product,
            'status'  => true,
        ], 201);
    }

    /**
     * Mevcut bir ürünü günceller.
     *
     * @param Request           $request
     * @param int               $productID
     * @param PermissionService $permissionService
     * @param ProductService    $productService
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(
        Request $request,
        int $productID,
        PermissionService $permissionService,
        ProductService $productService
    ) {
        // Güncelleme yetkisini kontrol et
        if (!$permissionService->hasPermission('Product_update')) {
            return response()->json([
                'message' => 'Yetkisiz Erişim',
                'status'  => false,
            ], 401);
        }

        // Sadece gönderilen alanları validate et
        $data = $request->validate([
            'name'           => 'sometimes|required|string',
            'sku'            => "sometimes|required|unique:products,sku,{$productID}",
            'price'          => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0',
        ]);

        // Servis ile güncelleme işlemini gerçekleştir
        $updated = $productService->updateProduct($data, $productID);

        if (! $updated) {
            return response()->json([
                'message' => 'Ürün güncellenemedi',
                'status'  => false,
            ], 400);
        }

        // Başarılıysa güncellenen veriyi geri gönder
        $data['id'] = $productID;
        return response()->json([
            'product' => $data,
            'status'  => true,
        ]);
    }

    /**
     * Bir ürünü siler.
     *
     * @param int $productID
     * @param PermissionService $permissionService
     * @param ProductService $productService
     * @return JsonResponse
     */
    public function destroy(
        int $productID,
        PermissionService $permissionService,
        ProductService $productService
    ) {
        // Silme yetkisini kontrol et
        if (!$permissionService->hasPermission('Product_delete')) {
            return response()->json([
                'message' => 'Yetkisiz Erişim',
                'status'  => false,
            ], 401);
        }

        // Ürünü servise sildir
        $productService->delete($productID);

        // Başarıyla silindi yanıtı
        return response()->json([
            'status' => true,
        ]);
    }
}
