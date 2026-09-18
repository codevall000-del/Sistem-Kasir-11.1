import tailwindcss from "@tailwindcss/vite"

export default defineNuxtConfig({
  compatibilityDate: '2025-11-03',

  devtools: {
     enabled: true 
    },

  css: ['~/assets/css/main.css'],

  plugins: ['~/plugins/api.ts'],
  
  vite: {
    plugins: [tailwindcss()],
    server: {
      hmr: {
        overlay: false
      }
    }
  },
});