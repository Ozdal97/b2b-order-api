<?php

namespace App\Services\Permission;

use Illuminate\Support\Facades\Auth;

class PermissionService
{
    public function hasPermission(string $permission): bool
    {
        //todo daha detaylı Permission gerekli gecici sadece
        return Auth::user()->role == 'admin';
    }
}
