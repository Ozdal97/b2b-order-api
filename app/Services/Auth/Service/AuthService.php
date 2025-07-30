<?php

namespace App\Services\Auth\Service;

use App\Models\User;
use App\Services\Auth\Repository\AuthRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * @var AuthRepository
     */
    private AuthRepository $repository;

    /**
     * Son işlemde kullanılan kullanıcı nesnesi.
     *
     * @var User|null
     */
    public ?User $user = null;

    /**
     * Oluşturulan veya aktifleştirilen token değeri.
     *
     * @var string
     */
    public string $token;

    /**
     * Repository bağımlılığını ayarlar.
     */
    public function __construct()
    {
        $this->repository = new AuthRepository();
    }

    /**
     * Yeni kullanıcı kaydı oluşturur ve token üretir.
     *
     * @param  array  $data
     * @return bool
     */
    public function create(array $data): bool
    {
        // Parolayı hash’le
        $data['password'] = Hash::make($data['password']);

        // Repository’den user kaydını al
        $this->user = $this->repository->createUser($data);

        // Oluşturma başarısızsa false dön
        if (is_null($this->user)) {
            return false;
        }

        // Başarılıysa token üret
        $this->token = $this->user->createToken('api-token')->plainTextToken;

        return true;
    }

    /**
     * Kullanıcı girişi (login) denemesini yapar.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function attempt(array $credentials): bool
    {
        // Auth facade ile credential kontrolü
        return Auth::attempt($credentials);
    }

    /**
     * Mevcut kullanıcı için yeni bir token oluşturur,
     * önceki token’ları iptal eder.
     *
     * @return void
     */
    public function createToken(): void
    {
        // Auth facade’den user’ı al
        $this->user = Auth::user();

        // Önceki tüm token’ları sil
        $this->user->tokens()->delete();

        // Yeni token oluştur ve sakla
        $this->token = $this->user->createToken('api-token')->plainTextToken;
    }

    /**
     * Geçerli erişim token’ını silerek logout yapar.
     *
     * @param  Request  $request
     * @return void
     */
    public function logout(Request $request): void
    {
        // İstekten gelen token’ı iptal et
        $request->user()->currentAccessToken()->delete();
    }
}
