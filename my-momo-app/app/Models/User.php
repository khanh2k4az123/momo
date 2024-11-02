<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'vip_expires_at',
        'email_verified_at',
    ];

    protected $casts = [
        'vip_expires_at' => 'datetime', // Đảm bảo vip_expires_at được cast thành datetime
        'email_verified_at' => 'datetime',
    ];
    public function isVip()
    {
        return $this->vip_expires_at && $this->vip_expires_at->isFuture();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}