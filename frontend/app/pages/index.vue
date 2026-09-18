<template>
  <div class="min-h-screen bg-[#f8fafc] flex flex-col items-center justify-center p-4 sm:p-6 font-sans relative overflow-hidden">
    <!-- Subtle architectural background accent -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-100/40 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-slate-200/50 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
      <!-- Main Card -->
      <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-300/30 p-8 sm:p-10">
        <!-- Header -->
        <div class="flex flex-col items-center text-center mb-8">
          <div class="flex items-center gap-2">
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
              E-Parking
            </h1>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
              v2.4 Pro
            </span>
          </div>
          <p class="text-xs font-bold text-emerald-700 tracking-wider uppercase mt-1">Plaza Andalas</p>
          <p class="text-xs text-slate-500 mt-1 font-medium">Sistem Administrasi & Operasional Parkir Terpadu</p>
        </div>

        <!-- Error Message Alert -->
        <div 
          v-if="errorMessage" 
          class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-start gap-3 animate-in fade-in duration-150"
        >
          <AlertTriangle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
          <div class="flex-1 leading-snug">{{ errorMessage }}</div>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
              Email Pengguna
            </label>
            <div class="relative">
              <input 
                id="email" 
                v-model="email" 
                type="email" 
                required 
                autocomplete="email" 
                placeholder="petugas@gmail.com"
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm bg-slate-50/60 focus:bg-white font-medium"
                :disabled="loading"
              />
              <Mail class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" />
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Kata Sandi
              </label>
              <button 
                type="button" 
                @click="showPassword = !showPassword" 
                class="text-[11px] text-slate-400 hover:text-emerald-600 font-semibold transition cursor-pointer"
              >
                {{ showPassword ? 'Sembunyikan' : 'Lihat Sandi' }}
              </button>
            </div>
            <div class="relative">
              <input 
                id="password" 
                v-model="password" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                autocomplete="current-password" 
                placeholder="••••••••"
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm bg-slate-50/60 focus:bg-white font-medium"
                :disabled="loading"
              />
              <Lock class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" />
            </div>
            <div class="flex justify-end mt-1.5">
              <NuxtLink 
                to="/forgot-password" 
                class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition"
              >
                Lupa kata sandi?
              </NuxtLink>
            </div>
          </div>

          <!-- Quick Autofill Helper Buttons -->
          <div class="pt-1.5">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-2">Akun Demo Cepat:</p>
            <div class="grid grid-cols-2 gap-2">
              <button 
                type="button" 
                @click="fillAccount('petugas@gmail.com', '12345678')" 
                class="px-3 py-2 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 border border-slate-200 rounded-xl text-slate-700 hover:text-emerald-700 text-xs font-bold transition text-left flex items-center justify-between cursor-pointer group"
              >
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  <span>Petugas</span>
                </div>
                <span class="text-[10px] text-slate-400 font-normal group-hover:text-emerald-600">Pilih</span>
              </button>
              <button 
                type="button" 
                @click="fillAccount('admin@gmail.com', '12345678')" 
                class="px-3 py-2 bg-slate-50 hover:bg-purple-50 hover:border-purple-300 border border-slate-200 rounded-xl text-slate-700 hover:text-purple-700 text-xs font-bold transition text-left flex items-center justify-between cursor-pointer group"
              >
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                  <span>Super Admin</span>
                </div>
                <span class="text-[10px] text-slate-400 font-normal group-hover:text-purple-600">Pilih</span>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full mt-4 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/40 transition duration-150 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer text-xs sm:text-sm"
          >
            <svg 
              v-if="loading" 
              class="animate-spin h-4 w-4 text-white" 
              xmlns="http://www.w3.org/2000/svg" 
              fill="none" 
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>{{ loading ? 'Memverifikasi Kredensial...' : 'Masuk ke Sistem' }}</span>
            <ArrowRight v-if="!loading" class="w-4 h-4" />
          </button>
        </form>

        <!-- Kiosk Link & Footer -->
        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <NuxtLink 
            to="/user" 
            class="font-semibold text-slate-600 hover:text-emerald-600 transition flex items-center gap-1.5"
          >
            <ExternalLink class="w-3.5 h-3.5 text-emerald-600" />
            <span>Kiosk Pengunjung</span>
          </NuxtLink>
          <span class="inline-flex items-center gap-1.5 text-emerald-600 font-semibold text-[11px]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Server Siap
          </span>
        </div>
      </div>

      <!-- Copyright Note -->
      <p class="text-center text-[11px] text-slate-500 mt-6 font-medium">
        &copy; 2026 E-Parking Plaza Andalas. Hak Cipta Dilindungi.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { 
  Mail, 
  Lock, 
  AlertTriangle, 
  ArrowRight, 
  ExternalLink 
} from 'lucide-vue-next';

const router = useRouter();
const { login, getUser } = useAuth();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const fillAccount = (accEmail: string, accPass: string) => {
  email.value = accEmail;
  password.value = accPass;
  errorMessage.value = '';
};

const handleSubmit = async () => {
  if (!email.value || !password.value) {
    errorMessage.value = 'Silakan masukkan email dan kata sandi.';
    return;
  }

  loading.value = true;
  errorMessage.value = '';

  try {
    const res = await login(email.value, password.value);
    const user = res?.user || getUser();

    if (user?.role === 'superadmin' || user?.role === 'super_admin') {
      await navigateTo('/superadmin');
    } else {
      await navigateTo('/petugas');
    }
  } catch (err: any) {
    errorMessage.value =
      err?.response?.data?.message ||
      err?.message ||
      'Login gagal. Periksa kembali email dan password Anda.';
  } finally {
    loading.value = false;
  }
};
</script>
