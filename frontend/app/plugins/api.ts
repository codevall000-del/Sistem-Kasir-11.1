import axios from "axios";
import { defineNuxtPlugin } from "#app";

export default defineNuxtPlugin((nuxtApp) => {
  const api = axios.create({
    baseURL: "http://localhost:8000/api",
    headers: {
      Accept: "application/json",
    },
  });

  // Request interceptor: attach auth token if available
  api.interceptors.request.use((config) => {
    if (import.meta.client) {
      const token = localStorage.getItem('auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    }
    return config;
  });

  // Response interceptor: redirect to login on 401
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401 && import.meta.client) {
        const path = window.location.pathname;
        // Don't redirect if already on login page or public endpoints
        if (path !== '/' && !path.startsWith('/user')) {
          localStorage.removeItem('auth_token');
          localStorage.removeItem('auth_user');
          window.location.href = '/';
        }
      }
      return Promise.reject(error);
    }
  );

  return {
    provide: {
      api,
    },
  };
});