<?php

namespace App\Http\Controllers;

use App\Services\Auth\Service\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Yeni kullanıcı kaydı yapar ve token döner.
     *
     * @param  Request     $request
     * @param  AuthService $authService
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request, AuthService $authService): \Illuminate\Http\JsonResponse
    {
        // İstek verilerini doğrula
        $data = $request->validate([
            'name'                  => 'required|string',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|confirmed|min:6',
            'role'                  => 'required|in:admin,customer',
        ]);

        // Servis aracılığıyla kullanıcı oluştur
        if (! $authService->create($data)) {
            // Oluşturma başarısızsa hata dön
            return response()->json([
                'message' => 'Kullanıcı oluşturma hatası',
                'status'  => false,
            ], 401);
        }

        // Başarılıysa user ve token bilgilerini döndür
        return response()->json([
            'user'   => $authService->user,
            'token'  => $authService->token,
            'status' => true,
        ], 201);
    }

    /**
     * Mevcut kullanıcıyı login eder ve yeni token üretir.
     *
     * @param  Request     $request
     * @param  AuthService $authService
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request, AuthService $authService): \Illuminate\Http\JsonResponse
    {
        // Giriş için gerekli alanları doğrula
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Kimlik bilgilerini servis ile dene
        if (! $authService->attempt($credentials)) {
            // Başarısızsa Unauthorized
            return response()->json([
                'message' => 'Unauthorized',
                'status'  => false,
            ], 401);
        }

        // Başarılı ise yeni bir token oluştur
        $authService->createToken();

        // Kullanıcı ve token bilgisini döndür
        return response()->json([
            'user'   => $authService->user,
            'token'  => $authService->token,
            'status' => true,
        ]);
    }

    /**
     * Geçerli token’ı iptal ederek logout yapar.
     *
     * @param  Request     $request
     * @param  AuthService $authService
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request, AuthService $authService): \Illuminate\Http\JsonResponse
    {
        // Servis üzerinden token’ı sil
        $authService->logout($request);

        // Başarı yanıtı dön
        return response()->json([
            'message' => 'Logged out',
            'status'  => true,
        ]);
    }
}
