<!-- src/views/Packages.vue -->

<template>
  <div class="packages">
    <h2>Gói Dịch Vụ</h2>
    <ul>
      <li v-for="pkg in packages" :key="pkg.id">
        {{ pkg.name }} - {{ formatPrice(pkg.price) }} VNĐ -
        {{ pkg.duration }} tháng
      </li>
    </ul>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Packages",
  data() {
    return {
      packages: [],
      loading: true,
      error: "",
    };
  },
  methods: {
    async fetchPackages() {
      try {
        const response = await axios.get("/packages");
        this.packages = response.data;
      } catch (error) {
        console.error("Error fetching packages:", error);
        this.error = "Có lỗi xảy ra khi tải các gói dịch vụ.";
      } finally {
        this.loading = false;
      }
    },
    formatPrice(price) {
      return price.toLocaleString("vi-VN");
    },
  },
  mounted() {
    this.fetchPackages();
  },
};
</script>

<style scoped>
.packages {
  /* Thêm style nếu cần */
}
</style>
