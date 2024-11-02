<?php

// app/Http/Controllers/PackageController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class PackageController extends Controller
{
    /**
     * Lấy danh sách gói dịch vụ
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Lấy tất cả các gói VIP với các trường cần thiết
        $packages = Package::all(['id', 'name', 'price', 'duration']);

        return response()->json($packages);
    }
}
