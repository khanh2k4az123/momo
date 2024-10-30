<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Package;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Gửi yêu cầu POST đến MoMo.
     *
     * @param string $url
     * @param array $data
     * @return array|null
     */
    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        // Thực thi yêu cầu POST
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('CURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        // Đóng kết nối
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * Tạo đơn hàng và trả về URL thanh toán MoMo.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'order_info' => 'required|string|max:255',
            'extra_data' => 'nullable|string|max:255',
        ]);

        // Lấy thông tin gói dịch vụ
        $package = Package::find($request->package_id);

        // Tạo đơn hàng mới
        $order = Order::create([
            'user_id' => $request->user()->id,
            'package_id' => $package->id,
            'order_id' => Str::uuid()->toString(),
            'amount' => (int) ($package->price * 1000), // Chuyển đổi từ USD sang VND nếu cần
            'status' => 'pending',
        ]);

        // Hardcode các biến MoMo
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderId = $order->order_id;
        $requestId = Str::uuid()->toString();
        $redirectUrl = 'http://localhost:5173/payment-return'; // Thay bằng URL frontend của bạn
        $ipnUrl = 'http://127.0.0.1:8000/api/payment/notify'; // Thay bằng URL backend của bạn
        $amount = (int)$order->amount; // Đảm bảo là số nguyên VND
        $orderInfo = $request->order_info;
        $requestType = "payWithATM"; // Hoặc "captureWallet" tùy vào phương thức thanh toán bạn muốn
        $extraData = $request->extra_data ?? "";

        // Tạo chuỗi chữ ký
        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        // Tạo payload gửi đến MoMo
        $data = [
            "partnerCode" => $partnerCode,
            "accessKey" => $accessKey, // Thêm accessKey vào payload
            "partnerName" => "YourPartnerName", // Bạn có thể thay đổi theo yêu cầu
            "storeId" => "MomoTestStore", // Bạn có thể thay đổi theo yêu cầu
            "requestId" => $requestId,
            "amount" => $amount, // Số nguyên VND
            "orderId" => $orderId,
            "orderInfo" => $orderInfo,
            "redirectUrl" => $redirectUrl,
            "ipnUrl" => $ipnUrl,
            "lang" => 'vi',
            "extraData" => $extraData,
            "requestType" => $requestType,
            "signature" => $signature
        ];

        // Log dữ liệu gửi đến MoMo để kiểm tra
        Log::info('Sending data to MoMo: ' . json_encode($data));

        // Gửi yêu cầu đến MoMo
        $response = $this->execPostRequest('https://test-payment.momo.vn/v2/gateway/api/create', $data);

        // Log phản hồi từ MoMo để kiểm tra
        Log::info('Response from MoMo: ' . json_encode($response));

        if ($response && isset($response['resultCode']) && $response['resultCode'] == 0) {
            // Thành công, trả về URL thanh toán
            return response()->json([
                'status' => 'success',
                'payment_url' => $response['payUrl']
            ]);
        } else {
            // Xử lý lỗi
            Log::error('MoMo Payment Error: ' . json_encode($response));
            return response()->json([
                'status' => 'error',
                'message' => $response['message'] ?? 'Có lỗi xảy ra khi tạo đơn hàng.'
            ], 400);
        }
    }

    /**
     * Xử lý trả về từ MoMo sau khi thanh toán.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentReturn(Request $request)
    {
        // Lấy thông tin từ URL trả về
        $resultCode = $request->input('resultCode');
        $message = $request->input('message');
        $orderId = $request->input('orderId');
        $requestId = $request->input('requestId');

        if ($resultCode == 0) {
            // Thanh toán thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Thanh toán thành công!',
                'orderId' => $orderId,
                'requestId' => $requestId,
            ]);
        } else {
            // Thanh toán thất bại
            return response()->json([
                'status' => 'failed',
                'message' => 'Thanh toán thất bại: ' . $message,
                'orderId' => $orderId,
                'requestId' => $requestId,
            ], 400);
        }
    }

    /**
     * Xử lý thông báo từ MoMo (IPN).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentNotify(Request $request)
    {
        // Lấy chữ ký từ MoMo
        $signature = $request->input('signature');
        $rawHash = "";
    
        // Tạo chuỗi rawHash để xác thực chữ ký
        foreach ($request->all() as $key => $value) {
            if ($key != 'signature') {
                $rawHash .= "$key=$value&";
            }
        }
        $rawHash = rtrim($rawHash, "&");
    
        // Tạo chữ ký đã mã hóa
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; // Hardcode secretKey
        $generatedSignature = hash_hmac("sha256", $rawHash, $secretKey);
    
        // So sánh chữ ký
        if ($signature === $generatedSignature) {
            // Xác thực thành công
            $orderId = $request->input('orderId');
            $resultCode = $request->input('resultCode');
    
            $order = Order::where('order_id', $orderId)->first();
    
            if ($order) {
                if ($resultCode == 0) {
                    $order->status = 'completed';
                    // Cập nhật VIP cho user
                    $user = $order->user;
                    if ($user) {
                        // Kiểm tra nếu người dùng đã có VIP, kéo dài thêm 1 tháng
                        if ($user->vip_expires_at && $user->vip_expires_at->isFuture()) {
                            $oldVip = $user->vip_expires_at;
                            $user->vip_expires_at = $user->vip_expires_at->addMonth();
                            Log::info("User ID {$user->id} VIP extended from {$oldVip} to {$user->vip_expires_at}");
                        } else {
                            $user->vip_expires_at = Carbon::now()->addMonth();
                            Log::info("User ID {$user->id} VIP set to {$user->vip_expires_at}");
                        }
                        $user->save();
                    }
                } else {
                    $order->status = 'failed';
                    Log::info("Order ID {$order->id} status set to failed due to resultCode {$resultCode}");
                }
                $order->save();
            } else {
                Log::warning("Order not found with orderId {$orderId}");
            }
    
            return response()->json(['message' => 'success']);
        } else {
            // Xác thực thất bại
            Log::warning('Invalid signature for MoMo IPN', ['signature' => $signature, 'generated' => $generatedSignature]);
            return response()->json(['message' => 'invalid signature'], 400);
        }
    }
    
}
