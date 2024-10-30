<template>
  <div>
    <h2>Đăng Nhập</h2>
    <form @submit.prevent="login">
      <div>
        <label>Email:</label>
        <input type="email" v-model="email" required />
      </div>
      <div>
        <label>Mật Khẩu:</label>
        <input type="password" v-model="password" required />
      </div>
      <button type="submit">Đăng Nhập</button>
    </form>
    <div v-if="errorMessage" style="color: red">
      {{ errorMessage }}
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
      errorMessage: "",
    };
  },
  methods: {
    async login() {
      try {
        const response = await apiClient.post("/login", {
          email: this.email,
          password: this.password,
        });
        localStorage.setItem("access_token", response.data.access_token);
        this.$router.push("/packages");
      } catch (error) {
        if (error.response && error.response.data.errors) {
          this.errorMessage = Object.values(error.response.data.errors)
            .flat()
            .join(" ");
        } else if (error.response && error.response.data.message) {
          this.errorMessage = error.response.data.message;
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
