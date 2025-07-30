<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'total_price',
    ];

    // Siparişi oluşturan kullanıcı
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Sipariş kalemleri
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
