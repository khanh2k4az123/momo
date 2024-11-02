<!-- src/App.vue -->

<template>
  <div id="app">
    <nav>
      <router-link to="/">Home</router-link> |
      <router-link v-if="!isLoggedIn" to="/register">Đăng Ký</router-link> |
      <router-link v-if="!isLoggedIn" to="/login">Đăng Nhập</router-link> |
      <router-link v-if="isLoggedIn" to="/packages">Gói Dịch Vụ</router-link> |
      <router-link v-if="isLoggedIn" to="/vip-status"
        >Trạng Thái VIP</router-link
      >
      |
      <router-link v-if="isLoggedIn" to="/purchase-vip">Mua VIP</router-link> |
      <router-link v-if="isLoggedIn" to="/vip-content"
        >Nội Dung VIP</router-link
      >
      <button v-if="isLoggedIn" @click="logout">Đăng Xuất</button>
    </nav>
    <router-view></router-view>
  </div>
</template>

<script>
import apiClient from "./plugins/axios";

export default {
  name: "App",
  data() {
    return {
      isLoggedIn: false,
    };
  },
  created() {
    const token = localStorage.getItem("access_token");
    if (token) {
      this.isLoggedIn = true;
    }
  },
  methods: {
    async logout() {
      try {
        await apiClient.post("/logout");
        localStorage.removeItem("access_token");
        this.isLoggedIn = false;
        this.$router.push("/");
      } catch (error) {
        console.error(error);
        alert("Có lỗi xảy ra khi đăng xuất.");
      }
    },
  },
};
</script>
<style>
nav {
  padding: 16px;
  background-color: #f8f8f8;
}
nav a {
  margin-right: 8px;
}
button {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 6px 12px;
  cursor: pointer;
  margin-left: 8px;
}
button:hover {
  background-color: #c82333;
}
</style>
