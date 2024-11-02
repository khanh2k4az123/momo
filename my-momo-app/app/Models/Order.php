<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'order_id',
        'partner_code',
        'access_key',
        'amount',
        'order_info',
        'status',
        'pay_url',
        'signature',
        'trans_id',
        'message',
        'local_message',
        'error_code',
    ];

    /**
     * Liên kết đến người dùng.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Liên kết đến gói mua.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
