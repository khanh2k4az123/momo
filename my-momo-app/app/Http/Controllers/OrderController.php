<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Package;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // MoMo credentials (should be stored in .env for security)
    private $partnerCode;
    private $accessKey;
    private $secretKey;

    public function __construct()
    {
        // Initialize MoMo credentials from environment variables
        $this->partnerCode = env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529');
        $this->accessKey = env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j');
        $this->secretKey = env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');
    }

    /**
     * Send POST request to MoMo API.
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

        // Execute the POST request
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('CURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        // Close the connection
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * Create an order and return MoMo payment URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(Request $request)
    {
        // Validate input data
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'order_info' => 'required|string|max:255',
            'extra_data' => 'nullable|string|max:255',
        ]);

        // Get package information
        $package = Package::find($request->package_id);

        // Check if package exists
        if (!$package) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gói VIP không tồn tại.'
            ], 404);
        }

        // Generate order_id
        $orderId = Str::uuid()->toString();

        // MoMo parameters
        $partnerCode = $this->partnerCode;
        $accessKey = $this->accessKey;
        $secretKey = $this->secretKey;
        $requestId = Str::uuid()->toString();
        $redirectUrl = 'http://localhost:8000/api/payment/return'; // Backend URL for payment return
        $ipnUrl = 'http://localhost:8000/api/payment/notify'; // Replace with your Ngrok URL or public URL
        $amount = (int) $package->price; // Assuming price is in VND
        $orderInfo = $request->order_info;
        $requestType = "payWithATM"; // or "captureWallet"
        $extraData = $request->extra_data ?? "";

        // Create signature string
        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        // Create payload to send to MoMo
        $data = [
            "partnerCode" => $partnerCode,
            "accessKey" => $accessKey,
            "partnerName" => "YourPartnerName",
            "storeId" => "MomoTestStore",
            "requestId" => $requestId,
            "amount" => $amount,
            "orderId" => $orderId,
            "orderInfo" => $orderInfo,
            "redirectUrl" => $redirectUrl,
            "ipnUrl" => $ipnUrl,
            "lang" => 'vi',
            "extraData" => $extraData,
            "requestType" => $requestType,
            "signature" => $signature
        ];

        // Log data sent to MoMo for debugging
        Log::info('Sending data to MoMo: ' . json_encode($data));

        // Create a new order with all fields from migration, including signature
        $order = Order::create([
            'user_id' => $request->user()->id ?? null,
            'package_id' => $package->id,
            'order_id' => $orderId,
            'partner_code' => $partnerCode,
            'access_key' => $accessKey,
            'amount' => $amount,
            'order_info' => $orderInfo,
            'status' => 'pending',
            'signature' => $signature,
            'pay_url' => null, // Will be updated after receiving response from MoMo
            'trans_id' => null,
            'message' => null,
            'local_message' => null,
            'error_code' => null,
        ]);

        // Send request to MoMo
        $response = $this->execPostRequest('https://test-payment.momo.vn/v2/gateway/api/create', $data);

        // Log response from MoMo for debugging
        Log::info('Response from MoMo: ' . json_encode($response));

        if ($response && isset($response['resultCode']) && $response['resultCode'] == 0) {
            // Success, update pay_url
            $order->pay_url = $response['payUrl'];
            $order->save();

            // Return payment URL
            return response()->json([
                'status' => 'success',
                'payment_url' => $response['payUrl']
            ]);
        } else {
            // Handle error
            $order->message = $response['message'] ?? null;
            $order->local_message = $response['localMessage'] ?? null;
            $order->error_code = $response['resultCode'] ?? null;
            $order->status = 'failed';
            $order->save();

            Log::error('MoMo Payment Error: ' . json_encode($response));

            return response()->json([
                'status' => 'error',
                'message' => $response['message'] ?? 'Có lỗi xảy ra khi tạo đơn hàng.'
            ], 400);
        }
    }

    /**
     * Handle return from MoMo after payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentReturn(Request $request)
    {
        // Get data from return URL
        $resultCode = $request->input('resultCode');
        $message = $request->input('message');
        $orderId = $request->input('orderId');
        $transId = $request->input('transId');
        $errorCode = $request->input('errorCode');
        $localMessage = $request->input('localMessage');
    
        // Log data received for debugging
        Log::info('Payment return data: ', $request->all());
    
        $order = Order::where('order_id', $orderId)->first();
    
        if (!$order) {
            Log::error('Order not found: ' . $orderId);
            return redirect('http://localhost:5173/vip-status?status=error&message=Đơn hàng không tồn tại.');
        }
    
        // Update order information
        $order->trans_id = $transId;
        $order->message = $message;
        $order->local_message = $localMessage;
        $order->error_code = $errorCode;
    
        if ($resultCode == 0) {
            // Payment successful
            $order->status = 'successful';
            $order->save();
    
            // Update user's VIP status
            $user = $order->user;
            $package = $order->package;
    
            if ($user && $package) {
                if ($user->vip_expires_at && $user->vip_expires_at->isFuture()) {
                    // Extend current VIP
                    $user->vip_expires_at = $user->vip_expires_at->addMonths($package->duration);
                } else {
                    // Set new VIP expiration
                    $user->vip_expires_at = Carbon::now()->addMonths($package->duration);
                }
                $user->save();
    
                Log::info("User ID {$user->id} VIP updated to {$user->vip_expires_at}");
            } else {
                Log::warning('User or package not found for order: ' . $orderId);
            }
    
            // Redirect tới frontend với thông tin thành công
            return redirect('http://localhost:5173/vip-status?status=success&orderId=' . $order->id);
        } else {
            // Payment failed
            $order->status = 'failed';
            $order->save();
    
            Log::warning('Payment failed for order: ' . $orderId);
    
            // Redirect tới frontend với thông tin lỗi
            return redirect('http://localhost:5173/vip-status?status=error&message=' . urlencode($message));
        }
    }
    

    /**
     * Handle MoMo IPN (Instant Payment Notification).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentNotify(Request $request)
    {
        // Log notification received from MoMo
        Log::info('Received payment notification:', $request->all());

        // Get signature from MoMo
        $signature = $request->input('signature');

        // Create rawHash string for signature verification
        $rawHash = "";
        foreach ($request->all() as $key => $value) {
            if ($key !== 'signature') {
                $rawHash .= "$key=$value&";
            }
        }
        $rawHash = rtrim($rawHash, "&");

        // Generate signature
        $generatedSignature = hash_hmac("sha256", $rawHash, $this->secretKey);

        // Compare signatures
        if ($signature === $generatedSignature) {
            // Signature valid
            $orderId = $request->input('orderId');
            $resultCode = $request->input('resultCode');
            $transId = $request->input('transId');
            $errorCode = $request->input('errorCode');
            $message = $request->input('message');
            $localMessage = $request->input('localMessage');

            $order = Order::where('order_id', $orderId)->first();

            if ($order) {
                // Update order information
                $order->trans_id = $transId;
                $order->message = $message;
                $order->local_message = $localMessage;
                $order->error_code = $errorCode;

                if ($resultCode == 0) {
                    // Payment successful
                    $order->status = 'successful';
                    $order->save();

                    // Update user's VIP status
                    $user = $order->user;
                    $package = $order->package;

                    if ($user && $package) {
                        if ($user->vip_expires_at && $user->vip_expires_at->isFuture()) {
                            // Extend current VIP
                            $user->vip_expires_at = $user->vip_expires_at->addMonths($package->duration);
                        } else {
                            // Set new VIP expiration
                            $user->vip_expires_at = Carbon::now()->addMonths($package->duration);
                        }
                        $user->save();

                        Log::info("User ID {$user->id} VIP updated via IPN to {$user->vip_expires_at}");
                    } else {
                        Log::warning("User or package not found for Order ID {$order->id}");
                    }
                } else {
                    // Payment failed
                    $order->status = 'failed';
                    $order->save();

                    Log::warning("Payment failed in IPN for Order ID {$order->id}, resultCode: {$resultCode}");
                }
            } else {
                Log::warning("Order not found with orderId {$orderId}");
            }

            return response()->json(['message' => 'success']);
        } else {
            // Invalid signature
            Log::warning('Invalid signature for MoMo IPN', ['signature' => $signature, 'generated' => $generatedSignature]);
            return response()->json(['message' => 'invalid signature'], 400);
        }
    }

    /**
     * Cancel an order (Optional: if you want to allow order cancellation).
     *
     * @param string $orderId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder($orderId, Request $request)
    {
        $order = Order::where('order_id', $orderId)
                      ->where('user_id', $request->user()->id)
                      ->first();

        if ($order && $order->status === 'pending') {
            $order->status = 'failed'; // Alternatively, use 'cancelled' if allowed
            $order->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được hủy thành công.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Không thể hủy đơn hàng này.'
        ], 400);
    }
}
