// src/router/index.js

import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";
import Register from "../views/Register.vue";
import Login from "../views/Login.vue";
import VipStatus from "../views/VipStatus.vue";
import PurchaseVip from "../views/PurchaseVip.vue";
import VipContent from "../views/VipContent.vue";
import PaymentReturn from "../views/PaymentReturn.vue";
import Packages from "../views/Packages.vue";

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
  },
  {
    path: "/register",
    name: "Register",
    component: Register,
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
  },
  {
    path: "/vip-status",
    name: "VipStatus",
    component: VipStatus,
    meta: { requiresAuth: true },
  },
  {
    path: "/purchase-vip",
    name: "PurchaseVip",
    component: PurchaseVip,
    meta: { requiresAuth: true },
  },
  {
    path: "/vip-content",
    name: "VipContent",
    component: VipContent,
    meta: { requiresAuth: true, requiresVip: true },
  },
  {
    path: "/packages",
    name: "Packages",
    component: Packages,
    meta: { requiresAuth: true },
  },
  {
    path: "/payment-return",
    name: "PaymentReturn",
    component: PaymentReturn,
  },
  // Thêm các route khác nếu cần
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
