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
        <div class="flex flex-col items-center text-center mb-6">
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 mb-3 shadow-sm">
            <KeyRound class="w-6 h-6" />
          </div>
          <h1 class="text-xl font-black text-slate-900 tracking-tight">
            Pemulihan Kata Sandi
          </h1>
          <p class="text-xs text-slate-500 mt-1 font-medium">
            Verifikasi akun email & reset kata sandi via WhatsApp
          </p>
        </div>

        <!-- Step Indicator -->
        <div v-if="currentStep <= 3" class="flex items-center justify-center gap-2 mb-6">
          <div 
            class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold transition-all"
            :class="currentStep === 1 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : (currentStep > 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400')"
          >
            1
          </div>
          <div class="w-8 h-0.5" :class="currentStep > 1 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
          <div 
            class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold transition-all"
            :class="currentStep === 2 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : (currentStep > 2 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400')"
          >
            2
          </div>
          <div class="w-8 h-0.5" :class="currentStep > 2 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
          <div 
            class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold transition-all"
            :class="currentStep === 3 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-400'"
          >
            3
          </div>
        </div>

        <!-- Error Alert -->
        <div 
          v-if="errorMessage" 
          class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-start gap-2.5 animate-in fade-in duration-150"
        >
          <AlertTriangle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
          <div class="flex-1 leading-snug">{{ errorMessage }}</div>
        </div>

        <!-- Warning banner (misal device Fonnte disconnect) -->
        <div 
          v-if="gatewayWarning && currentStep === 2" 
          class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium flex items-start gap-2.5"
        >
          <Info class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
          <div class="flex-1 leading-snug">
            <span class="font-bold">Info Fonnte:</span> {{ gatewayWarning }}
          </div>
        </div>

        <!-- Dev simulation code banner -->
        <div 
          v-if="simulatedOtp && currentStep === 2" 
          class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-start gap-2.5"
        >
          <Info class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
          <div class="flex-1 leading-snug">
            <span class="font-bold">Kode OTP Anda:</span> <strong class="font-mono text-sm font-black text-emerald-900 bg-emerald-200/60 px-1.5 py-0.5 rounded">{{ simulatedOtp }}</strong>
          </div>
        </div>

        <!-- STEP 1: Input Email Akun -->
        <form v-if="currentStep === 1" @submit.prevent="handleSendOtp" class="space-y-4">
          <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
              Alamat Email Akun
            </label>
            <div class="relative">
              <input 
                id="email" 
                v-model="email" 
                type="email" 
                required 
                placeholder="petugas@gmail.com" 
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm bg-slate-50/60 focus:bg-white font-medium"
                :disabled="loading"
              />
              <Mail class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" />
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5">
              Masukkan email akun Anda. Sistem akan memeriksa database dan langsung mengirimkan kode OTP ke nomor WhatsApp terdaftar Anda.
            </p>
          </div>

          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition duration-150 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer text-xs sm:text-sm"
          >
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>{{ loading ? 'Memeriksa Akun...' : 'Verifikasi Akun & Kirim OTP' }}</span>
            <ArrowRight v-if="!loading" class="w-4 h-4" />
          </button>
        </form>

        <!-- STEP 2: Input Kode OTP -->
        <form v-else-if="currentStep === 2" @submit.prevent="handleVerifyOtp" class="space-y-4">
          <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1 text-slate-600">
            <div class="flex justify-between items-center">
              <span class="text-slate-400 font-medium">Akun Email:</span>
              <span class="font-bold text-slate-800">{{ email }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-400 font-medium">WhatsApp Tujuan:</span>
              <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                {{ maskedPhone }}
              </span>
            </div>
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label for="otp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Kode Verifikasi (OTP)
              </label>
              <button 
                type="button" 
                @click="currentStep = 1" 
                class="text-[11px] text-slate-400 hover:text-emerald-600 transition font-medium cursor-pointer"
              >
                Ganti Email
              </button>
            </div>
            <div class="relative">
              <input 
                id="otp" 
                v-model="otpCode" 
                type="text" 
                maxlength="6"
                required 
                placeholder="6 Digit Kode" 
                class="w-full py-3 text-center tracking-[0.5em] font-mono text-lg font-black rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-slate-900 placeholder:text-slate-300 placeholder:tracking-normal placeholder:font-sans placeholder:text-xs bg-slate-50/60 focus:bg-white"
                :disabled="loading"
              />
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 text-center">
              Kode berlaku selama 5 menit.
            </p>
          </div>

          <button 
            type="submit" 
            :disabled="loading || otpCode.length !== 6" 
            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition duration-150 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer text-xs sm:text-sm"
          >
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>{{ loading ? 'Memverifikasi...' : 'Verifikasi Kode OTP' }}</span>
            <CheckCircle2 v-if="!loading" class="w-4 h-4" />
          </button>

          <!-- Resend Countdown -->
          <div class="text-center pt-1">
            <button 
              type="button" 
              @click="handleSendOtp" 
              :disabled="countdown > 0 || loading"
              class="text-xs font-bold transition cursor-pointer"
              :class="countdown > 0 ? 'text-slate-400 cursor-not-allowed' : 'text-emerald-600 hover:text-emerald-700 hover:underline'"
            >
              <span v-if="countdown > 0">Kirim ulang kode dalam {{ countdown }} detik</span>
              <span v-else>Kirim Ulang Kode Verifikasi</span>
            </button>
          </div>
        </form>

        <!-- STEP 3: Atur Sandi Baru -->
        <form v-else-if="currentStep === 3" @submit.prevent="handleResetPassword" class="space-y-4">
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Kata Sandi Baru
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
                id="new_password" 
                v-model="newPassword" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                minlength="8"
                placeholder="Minimal 8 karakter" 
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm bg-slate-50/60 focus:bg-white font-medium"
                :disabled="loading"
              />
              <Lock class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" />
            </div>
          </div>

          <div>
            <label for="confirm_password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
              Konfirmasi Kata Sandi Baru
            </label>
            <div class="relative">
              <input 
                id="confirm_password" 
                v-model="confirmPassword" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                minlength="8"
                placeholder="Ulangi kata sandi baru" 
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition text-slate-900 placeholder:text-slate-400 text-xs sm:text-sm bg-slate-50/60 focus:bg-white font-medium"
                :disabled="loading"
              />
              <KeyRound class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" />
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition duration-150 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer text-xs sm:text-sm"
          >
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>{{ loading ? 'Menyimpan Kata Sandi...' : 'Simpan Kata Sandi Baru' }}</span>
          </button>
        </form>

        <!-- STEP 4: Success Message -->
        <div v-else class="text-center py-4 space-y-4">
          <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border border-emerald-200 shadow-sm">
            <CheckCircle2 class="w-8 h-8" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900">Kata Sandi Berhasil Diperbarui!</h3>
            <p class="text-xs text-slate-500 mt-1">
              Kata sandi akun Anda telah diperbarui. Silakan gunakan sandi baru untuk masuk ke aplikasi.
            </p>
          </div>
          <NuxtLink 
            to="/" 
            class="inline-flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm shadow-md transition"
          >
            <span>Masuk ke Halaman Login</span>
            <ArrowRight class="w-4 h-4" />
          </NuxtLink>
        </div>

        <!-- Back to login link -->
        <div v-if="currentStep < 4" class="mt-6 pt-5 border-t border-slate-100 text-center">
          <NuxtLink 
            to="/" 
            class="text-xs font-semibold text-slate-500 hover:text-emerald-600 transition inline-flex items-center gap-1.5 cursor-pointer"
          >
            <ArrowLeft class="w-3.5 h-3.5" />
            <span>Kembali ke Halaman Login</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import { 
  KeyRound, Mail, Lock, CheckCircle2, ArrowRight, 
  ArrowLeft, AlertTriangle, Info 
} from 'lucide-vue-next';

const { $api } = useNuxtApp();

const currentStep = ref(1);
const email = ref('');
const maskedPhone = ref('');
const otpCode = ref('');
const resetToken = ref('');
const newPassword = ref('');
const confirmPassword = ref('');

const loading = ref(false);
const errorMessage = ref('');
const simulatedOtp = ref('');
const gatewayWarning = ref('');
const showPassword = ref(false);

const countdown = ref(0);
let timer: any = null;

const startCountdown = (seconds = 60) => {
  countdown.value = seconds;
  if (timer) clearInterval(timer);
  timer = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value--;
    } else {
      clearInterval(timer);
    }
  }, 1000);
};

const handleSendOtp = async () => {
  if (!email.value.trim()) {
    errorMessage.value = 'Silakan masukkan alamat email akun Anda.';
    return;
  }

  loading.value = true;
  errorMessage.value = '';
  gatewayWarning.value = '';
  try {
    const res = await $api.post('/auth/forgot-password', {
      email: email.value.trim()
    });

    if (res.data?.success) {
      currentStep.value = 2;
      maskedPhone.value = res.data?.data?.masked_phone || '';
      simulatedOtp.value = res.data?.data?.otp || '';
      gatewayWarning.value = res.data?.data?.warning || '';
      startCountdown(60);
    } else {
      errorMessage.value = res.data?.message || 'Gagal memeriksa akun.';
    }
  } catch (error: any) {
    console.error('Send OTP error:', error);
    errorMessage.value = error.response?.data?.message || 'Alamat email tidak ditemukan atau terjadi kesalahan server.';
  } finally {
    loading.value = false;
  }
};

const handleVerifyOtp = async () => {
  if (!otpCode.value.trim() || otpCode.value.trim().length !== 6) {
    errorMessage.value = 'Masukkan 6 digit kode verifikasi dengan benar.';
    return;
  }

  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await $api.post('/auth/verify-otp', {
      email: email.value.trim(),
      otp: otpCode.value.trim()
    });

    if (res.data?.success) {
      resetToken.value = res.data?.reset_token;
      currentStep.value = 3;
    } else {
      errorMessage.value = res.data?.message || 'Kode verifikasi salah atau kedaluwarsa.';
    }
  } catch (error: any) {
    console.error('Verify OTP error:', error);
    errorMessage.value = error.response?.data?.message || 'Kode OTP tidak cocok atau sudah tidak berlaku.';
  } finally {
    loading.value = false;
  }
};

const handleResetPassword = async () => {
  if (newPassword.value.length < 8) {
    errorMessage.value = 'Kata sandi baru minimal harus 8 karakter.';
    return;
  }

  if (newPassword.value !== confirmPassword.value) {
    errorMessage.value = 'Konfirmasi kata sandi tidak cocok.';
    return;
  }

  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await $api.post('/auth/reset-password', {
      email: email.value.trim(),
      reset_token: resetToken.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value
    });

    if (res.data?.success) {
      currentStep.value = 4;
    } else {
      errorMessage.value = res.data?.message || 'Gagal mengatur ulang kata sandi.';
    }
  } catch (error: any) {
    console.error('Reset password error:', error);
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan saat memperbarui kata sandi.';
  } finally {
    loading.value = false;
  }
};

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
