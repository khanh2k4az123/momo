<!-- src/views/VipStatus.vue -->

<template>
  <div class="vip-status">
    <h2>Trạng Thái VIP</h2>
    <div v-if="loading">
      <p>Đang tải...</p>
    </div>
    <div v-else>
      <!-- Hiển thị dữ liệu để debug -->
      <pre>{{ vipStatus }}</pre>

      <p v-if="vipStatus.is_vip">
        Bạn đang là thành viên VIP. Thời gian VIP hết hạn vào:
        {{ formatDate(vipStatus.vip_expires_at) }}
        <br />
        Thời gian còn lại: {{ remainingDays }} ngày
      </p>
      <p v-else>Bạn chưa là thành viên VIP.</p>
    </div>
  </div>
</template>

<script>
import apiClient from "../plugins/axios"; // Sử dụng apiClient đã cấu hình
import moment from "moment";

export default {
  name: "VipStatus",
  data() {
    return {
      vipStatus: {
        is_vip: false,
        vip_expires_at: null,
      },
      loading: true,
      remainingDays: 0,
      timer: null,
    };
  },
  methods: {
    async fetchVipStatus() {
      try {
        const response = await apiClient.get("/user/vip-status");
        console.log("VIP Status Response:", response.data); // Debug
        this.vipStatus = response.data;
        this.calculateRemainingDays();
        this.startTimer();
      } catch (error) {
        console.error("Error fetching VIP status:", error);
        // Bạn có thể thêm logic hiển thị thông báo lỗi cho người dùng
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return "Chưa có VIP";
      return moment(dateStr).format("DD/MM/YYYY");
    },
    calculateRemainingDays() {
      if (this.vipStatus.is_vip && this.vipStatus.vip_expires_at) {
        const today = moment();
        const expireDate = moment(this.vipStatus.vip_expires_at);
        this.remainingDays = expireDate.diff(today, "days");
        if (this.remainingDays < 0) {
          this.remainingDays = 0;
          this.vipStatus.is_vip = false;
        }
      }
    },
    startTimer() {
      if (this.vipStatus.is_vip) {
        this.timer = setInterval(() => {
          this.calculateRemainingDays();
          if (this.remainingDays <= 0) {
            clearInterval(this.timer);
            // Có thể thêm logic để cập nhật trạng thái VIP trên frontend nếu cần
          }
        }, 86400000); // Cập nhật mỗi ngày (86400000 ms)
      }
    },
  },
  mounted() {
    this.fetchVipStatus();
  },
  beforeUnmount() {
    if (this.timer) {
      clearInterval(this.timer);
    }
  },
};
</script>

<style scoped>
.vip-status {
  /* Thêm style nếu cần */
}
</style>
