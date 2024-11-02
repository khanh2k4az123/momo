<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Liên kết đến người dùng
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Liên kết đến gói mua (packages)
            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');

            // Thông tin thanh toán MoMo
            $table->string('order_id')->unique(); // Mã đơn hàng từ MoMo
            $table->string('partner_code');
            $table->string('access_key');
            $table->decimal('amount', 10, 2);
            $table->string('order_info');
            $table->string('status')->default('pending'); // pending, successful, failed
            $table->string('pay_url')->nullable();
            $table->string('signature');
            $table->string('trans_id')->nullable(); // Mã giao dịch từ MoMo
            $table->string('message')->nullable();
            $table->string('local_message')->nullable();
            $table->string('error_code')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
