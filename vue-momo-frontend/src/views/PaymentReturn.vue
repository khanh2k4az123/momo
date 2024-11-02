<!-- src/views/PaymentReturn.vue -->
<template>
  <div class="payment-return">
    <h2>Kết Quả Thanh Toán</h2>
    <div v-if="status === 'success'">
      <p>Thanh toán thành công!</p>
      <router-link to="/vip-status">Xem Trạng Thái VIP</router-link>
    </div>
    <div v-else-if="status === 'failed'">
      <p>Thanh toán thất bại: {{ message }}</p>
      <router-link to="/purchase-vip">Thử lại</router-link>
    </div>
    <div v-else>
      <p>Đang xử lý...</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "PaymentReturn",
  data() {
    return {
      status: "",
      message: "",
    };
  },
  methods: {
    async handlePaymentReturn() {
      const resultCode = this.$route.query.resultCode;
      const message = this.$route.query.message;
      const orderId = this.$route.query.orderId;
      const requestId = this.$route.query.requestId;

      if (resultCode === "0" || this.$route.query.status === "success") {
        this.status = "success";
        this.message = "Thanh toán thành công!";
        // Gọi API để lấy VIP status mới
        await this.updateVipStatus();
      } else {
        this.status = "failed";
        this.message =
          message || this.$route.query.message || "Thanh toán thất bại.";
      }
    },
    async updateVipStatus() {
      try {
        const token = localStorage.getItem("access_token"); // Lấy token từ nơi bạn lưu trữ
        const response = await axios.get(
          "http://localhost:8000/api/user/vip-status",
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        console.log("Updated VIP Status:", response.data);
        // Có thể cập nhật state hoặc Vuex store ở đây
      } catch (error) {
        console.error("Error updating VIP status:", error);
      }
    },
  },

  mounted() {
    this.handlePaymentReturn();
  },
};
</script>
