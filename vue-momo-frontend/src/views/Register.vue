<template>
  <div>
    <h2>Đăng Ký</h2>
    <form @submit.prevent="register">
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
        <label>Nhập Lại Mật Khẩu:</label>
        <input type="password" v-model="password_confirmation" required />
      </div>
      <button type="submit">Đăng Ký</button>
    </form>
    <div v-if="errorMessage" style="color: red">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import apiClient from "../plugins/axios";

export default {
  name: "Register",
  data() {
    return {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      errorMessage: "",
    };
  },
  methods: {
    async register() {
      try {
        const response = await apiClient.post("/register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });
        localStorage.setItem("access_token", response.data.access_token);
        this.$router.push("/packages");
      } catch (error) {
        if (error.response && error.response.data.errors) {
          this.errorMessage = Object.values(error.response.data.errors)
            .flat()
            .join(" ");
        } else {
          this.errorMessage = "Có lỗi xảy ra.";
        }
      }
    },
  },
};
</script>

<style scoped>
/* Thêm kiểu dáng nếu cần */
</style>
