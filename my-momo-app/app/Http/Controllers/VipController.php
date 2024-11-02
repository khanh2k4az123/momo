<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VipController extends Controller
{
    /**
     * Hiển thị nội dung dành cho VIP
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Ví dụ: Trả về nội dung VIP
        return response()->json([
            'message' => 'Chào mừng bạn đến với nội dung VIP!',
            'content' => 'Đây là nội dung đặc biệt dành cho người dùng VIP.'
        ]);
    }
}
