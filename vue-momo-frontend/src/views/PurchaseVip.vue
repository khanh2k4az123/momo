<!-- src/views/PurchaseVip.vue -->

<template>
  <div class="purchase-vip">
    <h2>Mua Gói VIP</h2>
    <div v-if="loading">
      <p>Đang tải các gói VIP...</p>
    </div>
    <div v-else>
      <select v-model="selectedPackageId">
        <option value="">Chọn gói VIP</option>
        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
          {{ pkg.name }} - {{ formatPrice(pkg.price) }} VNĐ -
          {{ pkg.duration }} tháng
        </option>
      </select>
      <button @click="handlePurchase" :disabled="!selectedPackageId">
        Mua Ngay
      </button>
    </div>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script>
import apiClient from "../plugins/axios"; // Import apiClient đã cấu hình

export default {
  name: "PurchaseVip",
  data() {
    return {
      packages: [],
      selectedPackageId: "",
      loading: true,
      error: "",
    };
  },
  methods: {
    async fetchPackages() {
      try {
        const response = await apiClient.get("/packages");
        this.packages = response.data;
        console.log("Fetched packages:", this.packages); // Debug
      } catch (error) {
        console.error("Error fetching packages:", error);
        this.error = "Có lỗi xảy ra khi tải các gói VIP.";
      } finally {
        this.loading = false;
      }
    },
    formatPrice(price) {
      if (price === undefined || price === null) {
        console.warn("Price is undefined or null:", price);
        return "0";
      }
      const numPrice = Number(price);
      if (isNaN(numPrice)) {
        console.warn("Price is not a valid number:", price);
        return "0";
      }
      return numPrice.toLocaleString("vi-VN");
    },
    async handlePurchase() {
      if (!this.selectedPackageId) return;

      try {
        const selectedPackage = this.packages.find(
          (pkg) => pkg.id === parseInt(this.selectedPackageId)
        );

        if (!selectedPackage) {
          this.error = "Gói VIP không tồn tại.";
          return;
        }

        const response = await apiClient.post("/create-order", {
          // Kiểm tra endpoint
          package_id: this.selectedPackageId,
          order_info: `Mua gói VIP ${selectedPackage.name}`,
          extra_data: "", // Nếu cần, thêm dữ liệu bổ sung
        });

        if (response.data.status === "success") {
          // Redirect đến URL thanh toán MoMo
          window.location.href = response.data.payment_url;
        } else {
          this.error =
            response.data.message || "Có lỗi xảy ra khi tạo đơn hàng.";
        }
      } catch (error) {
        console.error("Error creating order:", error);
        if (error.response && error.response.data) {
          this.error =
            error.response.data.message || "Có lỗi xảy ra khi tạo đơn hàng.";
        } else {
          this.error = "Có lỗi xảy ra khi tạo đơn hàng.";
        }
      }
    },
  },
  mounted() {
    this.fetchPackages();
  },
};
</script>

<style scoped>
.error {
  color: red;
  margin-top: 10px;
}
</style>
