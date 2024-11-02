<?php

// app/Models/Package.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration', // Thêm trường này
    ];

    /**
     * Mối quan hệ với Order
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
