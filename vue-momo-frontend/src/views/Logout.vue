<!-- src/components/Logout.vue -->

<template>
  <button @click="handleLogout">Đăng Xuất</button>
</template>

<script>
import axios from "axios";

export default {
  name: "Logout",
  methods: {
    async handleLogout() {
      try {
        await axios.post("/logout");
        // Xóa token khỏi localStorage
        localStorage.removeItem("auth_token");
        // Xóa header Authorization
        delete axios.defaults.headers.common["Authorization"];
        // Redirect đến trang đăng nhập
        this.$router.push("/login");
      } catch (error) {
        console.error("Logout error:", error);
        alert("Có lỗi xảy ra khi đăng xuất.");
      }
    },
  },
};
</script>
