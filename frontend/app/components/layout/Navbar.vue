<template>
  <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/90 text-slate-800 h-16 flex items-center justify-between px-4 sm:px-6 shadow-xs z-30 shrink-0">
    <!-- Left: Branding -->
    <div class="flex items-center">
      <NuxtLink to="/petugas" class="flex flex-col group">
        <div class="flex items-center gap-1.5">
          <span class="font-black text-base sm:text-lg tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors">
            E-Parking
          </span>
          <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
            PRO
          </span>
        </div>
        <span class="text-[10px] text-slate-500 font-medium tracking-wide uppercase leading-none hidden sm:block">
          Plaza Andalas Terminal
        </span>
      </NuxtLink>
    </div>

    <!-- Center: Live Digital Clock (Medium to Large Screens) -->
    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-mono text-slate-600 font-medium">
      <Clock class="w-3.5 h-3.5 text-emerald-600" />
      <span>{{ liveTime }}</span>
    </div>

    <!-- Right: Status, User & Logout -->
    <div class="flex items-center gap-3">
      <!-- Live Server Status Indicator -->
      <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-[11px] font-semibold text-emerald-700">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span>Online</span>
      </div>

      <!-- User Profile Text (without monogram logo) -->
      <div class="flex items-center pl-3 border-l border-slate-200">
        <div class="flex flex-col text-left">
          <span class="text-xs font-bold text-slate-800 leading-tight truncate max-w-[140px]">
            {{ user?.name || 'Petugas Aktif' }}
          </span>
          <span class="text-[10px] text-slate-500 font-medium leading-none">
            {{ (user?.role === 'superadmin' || user?.role === 'super_admin') ? 'Super Administrator' : 'Operator Loket' }}
          </span>
        </div>
      </div>

      <!-- Logout Button -->
      <button 
        @click="showLogoutModal = true" 
        class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-rose-50 text-slate-600 hover:text-rose-600 hover:border-rose-200 text-xs font-bold rounded-xl transition border border-slate-200/80 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500"
        title="Keluar dari Akun"
      >
        <LogOut class="w-3.5 h-3.5" />
        <span class="hidden sm:inline">Keluar</span>
      </button>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <Teleport to="body">
      <div 
        v-if="showLogoutModal" 
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-in fade-in duration-150"
        @click.self="showLogoutModal = false"
      >
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center text-slate-800 border border-slate-100">
          <div class="w-12 h-12 bg-rose-50 border border-rose-200 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <LogOut class="w-6 h-6" />
          </div>
          <h3 class="text-base font-bold text-slate-900 mb-1">Konfirmasi Keluar Sistem</h3>
          <p class="text-xs text-slate-500 mb-6 leading-relaxed">
            Apakah Anda yakin ingin mengakhiri sesi kerja pada terminal E-Parking ini?
          </p>
          <div class="grid grid-cols-2 gap-2.5">
            <button 
              type="button" 
              @click="showLogoutModal = false" 
              class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="button" 
              @click="confirmLogout" 
              class="py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-rose-600/30 cursor-pointer"
            >
              Ya, Keluar
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  LogOut, 
  Clock 
} from 'lucide-vue-next';

defineEmits(['toggleSidebar']);

const { getUser, logout } = useAuth();
const router = useRouter();
const user = getUser();
const showLogoutModal = ref(false);

const liveTime = ref('');
let timerId: any = null;

const updateTime = () => {
  const now = new Date();
  liveTime.value = now.toLocaleDateString('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }) + ' • ' + now.toLocaleTimeString('id-ID', { hour12: false });
};

const confirmLogout = async () => {
  showLogoutModal.value = false;
  await logout();
  router.push('/');
};

onMounted(() => {
  updateTime();
  timerId = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timerId) clearInterval(timerId);
});
</script>
