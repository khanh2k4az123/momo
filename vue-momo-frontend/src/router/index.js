import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";
import Register from "../views/Register.vue";
import Login from "../views/Login.vue";
import Packages from "../views/Packages.vue";
import Checkout from "../views/Checkout.vue";
import PaymentReturn from "../views/PaymentReturn.vue";

const routes = [
  { path: "/", name: "Home", component: Home },
  { path: "/register", name: "Register", component: Register },
  { path: "/login", name: "Login", component: Login },
  { path: "/packages", name: "Packages", component: Packages },
  { path: "/checkout/:packageId", name: "Checkout", component: Checkout },
  { path: "/payment-return", name: "PaymentReturn", component: PaymentReturn },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
