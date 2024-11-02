<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Lấy thông tin VIP của người dùng
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVipStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'is_vip' => $user->isVip(),
            'vip_expires_at' => $user->vip_expires_at,
        ]);
    }

    /**
     * Lấy thông tin người dùng
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
