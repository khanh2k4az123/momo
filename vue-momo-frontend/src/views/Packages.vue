<template>
  <div>
    <h2>Chọn Gói Dịch Vụ</h2>
    <div v-if="packages.length">
      <div v-for="pkg in packages" :key="pkg.id" class="pkg">
        <h3>{{ pkg.name }}</h3>
        <p>Giá: {{ pkg.price }} USD</p>
        <button @click="purchasePackage(pkg.id)">Mua Ngay</button>
      </div>
    </div>
    <div v-else>
      <p>Đang tải...</p>
    </div>
    <div v-if="errorMessage" style="color: red">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import apiClient from "../plugins/axios";

export default {
  name: "Packages",
  data() {
    return {
      packages: [],
      errorMessage: "",
    };
  },
  async created() {
    try {
      const response = await apiClient.get("/packages");
      this.packages = response.data;
    } catch (error) {
      this.errorMessage = "Có lỗi xảy ra khi lấy danh sách gói dịch vụ.";
    }
  },
  methods: {
    async purchasePackage(pkgId) {
      try {
        // Tạo đơn hàng
        const response = await apiClient.post("/create-order", {
          package_id: pkgId,
          order_info: "Thanh toán gói dịch vụ",
          extra_data: "",
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
.pkg {
  border: 1px solid #ccc;
  padding: 16px;
  margin-bottom: 16px;
}
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
