<?php

namespace App\Services\Auth\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthRepository
{
    /**
     * Yeni bir kullanıcı kaydı oluşturur.
     *
     * @param  array  $data  Kullanıcı oluşturmak için gerekli alanlar (name, email, password, role vs.)
     * @return User|null     Başarılıysa User nesnesi, başarısızsa null döner
     */
    public function createUser(array $data): ?User
    {
        try {
            // Mass assignment ile kullanıcı kaydını oluştur
            return User::create($data);
        } catch (\Exception $exception) {
            // Oluşturma sırasında hata oluşursa log kaydı al
            Log::error('AuthRepository@createUser failed', [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ]);
        }

        // Hata durumunda null döndür
        return null;
    }
}
