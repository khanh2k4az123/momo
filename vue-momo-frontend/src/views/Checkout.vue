<template>
  <div>
    <h2>Checkout</h2>
    <div v-if="package">
      <h3>{{ package.name }}</h3>
      <p>Giá: {{ package.price }} USD</p>
      <form @submit.prevent="checkout">
        <div>
          <label>Thông Tin:</label>
          <input type="text" v-model="orderInfo" required />
        </div>
        <div>
          <label>Thêm Dữ Liệu (tuỳ chọn):</label>
          <input type="text" v-model="extraData" />
        </div>
        <button type="submit">Thanh Toán MoMo</button>
      </form>
    </div>
    <div v-else>
      <p>Không tìm thấy gói dịch vụ.</p>
    </div>
    <div v-if="errorMessage" style="color: red">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import apiClient from "../plugins/axios";

export default {
  name: "Checkout",
  data() {
    return {
      package: null,
      orderInfo: "",
      extraData: "",
      errorMessage: "",
    };
  },
  async created() {
    const packageId = this.$route.params.packageId;
    try {
      const response = await apiClient.get("/packages");
      this.package = response.data.find((p) => p.id == packageId);
    } catch (error) {
      this.errorMessage = "Có lỗi xảy ra khi lấy thông tin gói dịch vụ.";
    }
  },
  methods: {
    async checkout() {
      try {
        const response = await apiClient.post("/create-order", {
          package_id: this.package.id,
          order_info: this.orderInfo,
          extra_data: this.extraData,
        });

        if (response.data.status === "success") {
          // Chuyển hướng tới URL thanh toán MoMo
          window.location.href = response.data.payment_url;
        } else {
          this.errorMessage = response.data.message;
        }
      } catch (error) {
        if (error.response && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage = "Có lỗi xảy ra khi tạo đơn hàng.";
        }
      }
    },
  },
};
</script>

<style scoped>
button {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 8px 16px;
  cursor: pointer;
}
button:hover {
  background-color: #218838;
}
</style>
