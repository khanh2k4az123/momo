<!-- src/views/Home.vue -->

<template>
  <div class="home">
    <h1>Chào Mừng Đến Với Ứng Dụng</h1>
    <div v-if="user">
      <p>Xin chào, {{ user.name }}!</p>
      <Logout />
      <router-link to="/vip-status">Xem Trạng Thái VIP</router-link>
      <router-link to="/purchase-vip">Mua Gói VIP</router-link>
      <router-link to="/vip-content">Nội Dung VIP</router-link>
    </div>
    <div v-else>
      <p>Vui lòng đăng nhập để tiếp tục.</p>
      <router-link to="/login">Đăng Nhập</router-link>
      <router-link to="/register">Đăng Ký</router-link>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Logout from "../views/Logout.vue";

export default {
  name: "Home",
  components: {
    Logout,
  },
  data() {
    return {
      user: null,
    };
  },
  methods: {
    async fetchUser() {
      try {
        const response = await axios.get("/user");
        this.user = response.data;
      } catch (error) {
        console.error("Error fetching user:", error);
        this.user = null;
      }
    },
  },
  mounted() {
    this.fetchUser();
  },
};
</script>
