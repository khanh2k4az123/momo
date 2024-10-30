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
        // Các cột bổ sung nếu có
        'vip_expires_at',
         'email_verified_at',
        // 'avatar',
        // 'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'vip_expires_at' => 'datetime', // Cast vip_expires_at thành datetime
    ];

    /**
     * Kiểm tra xem người dùng có VIP hay không.
     *
     * @return bool
     */
    public function isVip()
    {
        return $this->vip_expires_at && Carbon::now()->lessThan($this->vip_expires_at);
    }

    /**
     * Quan hệ với bảng Orders (nếu có).
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}