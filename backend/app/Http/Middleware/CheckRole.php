<?php
export default defineNuxtRouteMiddleware((to) => {
  // Jangan jalankan di server
  if (import.meta.server) {
    return
  }

  const user = localStorage.getItem("user")

  // =========================
  // localhost:3000
  // =========================
  if (to.path === "/") {
    return navigateTo("/petugas/login")
  }

  // =========================
  // Belum login
  // Tidak boleh masuk /petugas
  // =========================
  if (
    to.path.startsWith("/petugas") &&
    to.path !== "/petugas/login" &&
    !user
  ) {
    return navigateTo("/petugas/login")
  }

  // =========================
  // Sudah login
  // Tidak boleh kembali ke login
  // =========================
  if (to.path === "/petugas/login" && user) {
    return navigateTo("/petugas")
  }
})