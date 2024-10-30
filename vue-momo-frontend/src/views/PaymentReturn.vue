<template>
  <div>
    <h2>Kết Quả Thanh Toán</h2>
    <div v-if="status === 'success'">
      <p>Thanh toán thành công!</p>
      <p>Mã đơn hàng: {{ orderId }}</p>
    </div>
    <div v-else-if="status === 'failed'">
      <p>Thanh toán thất bại: {{ message }}</p>
      <p>Mã đơn hàng: {{ orderId }}</p>
    </div>
    <div v-else>
      <p>Đang xử lý...</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "PaymentReturn",
  data() {
    return {
      status: "",
      message: "",
      orderId: "",
    };
  },
  created() {
    const urlParams = new URLSearchParams(window.location.search);
    const resultCode = urlParams.get("resultCode");
    const message = urlParams.get("message");
    const orderId = urlParams.get("orderId");

    this.orderId = orderId;

    if (resultCode === "0") {
      this.status = "success";
      this.message = "Thanh toán thành công!";
    } else {
      this.status = "failed";
      this.message = message || "Thanh toán thất bại.";
    }

    // Bạn có thể gọi API để lấy thông tin chi tiết thanh toán nếu cần
  },
};
</script>

<style scoped>
/* Thêm kiểu dáng nếu cần */
</style>
