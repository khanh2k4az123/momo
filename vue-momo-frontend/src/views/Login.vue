<!-- src/views/Login.vue -->

<template>
  <div class="login">
    <form @submit.prevent="handleLogin">
      <input v-model="email" type="email" placeholder="Email" required />
      <input
        v-model="password"
        type="password"
        placeholder="Mật khẩu"
        required
      />
      <button type="submit">Đăng Nhập</button>
    </form>
    <div v-if="errors.length" class="error">
      <ul>
        <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
      </ul>
    </div>
  </div>
</template>

<script>
import apiClient from "../plugins/axios";

export default {
  name: "Login",
  data() {
    return {
      email: "",
      password: "",
      errors: [],
    };
  },
  methods: {
    async handleLogin() {
      this.errors = []; // Reset errors trước khi gửi yêu cầu
      try {
        const response = await apiClient.post("/login", {
          email: this.email,
          password: this.password,
        });
        // Lưu token vào localStorage
        localStorage.setItem("access_token", response.data.access_token);
        // Cập nhật trạng thái đăng nhập
        this.isLoggedIn = true;
        // Chuyển hướng đến trang chính hoặc trang cần
        this.$router.push("/");
      } catch (error) {
        if (error.response && error.response.status === 422) {
          // Xử lý lỗi xác thực
          this.errors = Object.values(error.response.data.errors).flat();
        } else if (error.response && error.response.status === 401) {
          // Lỗi xác thực không hợp lệ
          this.errors.push("Thông tin đăng nhập không chính xác.");
        } else {
          // Xử lý các lỗi khác
          this.errors.push("Có lỗi xảy ra khi đăng nhập.");
        }
        console.error("Login error:", error);
      }
    },
  },
};
</script>

<style scoped>
.error {
  color: red;
  margin-top: 10px;
}
</style>
