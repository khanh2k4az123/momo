<!-- src/views/Register.vue -->

<template>
  <div class="register">
    <h2>Đăng Ký</h2>
    <form @submit.prevent="handleRegister">
      <div>
        <label>Tên:</label>
        <input type="text" v-model="name" required />
      </div>
      <div>
        <label>Email:</label>
        <input type="email" v-model="email" required />
      </div>
      <div>
        <label>Mật Khẩu:</label>
        <input type="password" v-model="password" required />
      </div>
      <div>
        <label>Xác Nhận Mật Khẩu:</label>
        <input type="password" v-model="password_confirmation" required />
      </div>
      <button type="submit">Đăng Ký</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Register",
  data() {
    return {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      error: "",
    };
  },
  methods: {
    async handleRegister() {
      try {
        const response = await axios.post("/register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });

        if (response.data.status === "success") {
          // Redirect đến trang đăng nhập sau khi đăng ký thành công
          this.$router.push("/login");
        } else {
          this.error = response.data.message || "Đăng ký thất bại.";
        }
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message || "Đăng ký thất bại.";
        } else {
          this.error = "Có lỗi xảy ra. Vui lòng thử lại.";
        }
      }
    },
  },
};
</script>

<style scoped>
.error {
  color: red;
}
</style>
