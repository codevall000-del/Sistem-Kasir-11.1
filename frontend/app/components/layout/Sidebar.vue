<template>
  <aside 
    class="bg-white border-r border-slate-200/90 text-slate-700 flex flex-col transition-all duration-300 z-40 fixed inset-y-0 left-0 lg:static lg:translate-x-0 w-64 h-full shrink-0 select-none" 
    :class="isOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'"
  >
    <!-- User Profile Header in Sidebar -->
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
      <h4 class="font-bold text-sm text-slate-800 truncate leading-tight">
        {{ user?.name || 'Administrator' }}
      </h4>
      <div class="flex items-center gap-1.5 mt-1">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span class="text-[11px] text-slate-500 font-medium truncate">
          {{ (user?.role === 'superadmin' || user?.role === 'super_admin') ? 'Super Admin' : 'Petugas Lapangan' }}
        </span>
      </div>
    </div>

    <!-- Navigation Section -->
    <div class="p-3 flex-1 overflow-y-auto space-y-4">
      <!-- Superadmin Menu -->
      <div v-if="user?.role === 'superadmin' || user?.role === 'super_admin'">
        <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-2 px-3">
          MENU ADMINISTRATOR
        </p>
        <nav class="space-y-1">
          <NuxtLink 
            to="/superadmin" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-purple-50 group-hover:bg-purple-100 text-purple-600 flex items-center justify-center transition-colors">
              <Users class="w-4 h-4" />
            </div>
            <span>Kelola Petugas</span>
          </NuxtLink>

          <NuxtLink 
            to="/petugas" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-emerald-50 group-hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition-colors">
              <LayoutDashboard class="w-4 h-4" />
            </div>
            <span>Monitoring Parkir</span>
          </NuxtLink>

          <NuxtLink 
            to="/petugas/laporan" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-rose-50 group-hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors">
              <BarChart3 class="w-4 h-4" />
            </div>
            <span>Laporan Rekapitulasi</span>
          </NuxtLink>
        </nav>
      </div>

      <!-- Petugas Operasional Menu -->
      <div v-else>
        <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-2 px-3">
          MENU OPERASIONAL
        </p>
        <nav class="space-y-1">
          <NuxtLink 
            to="/petugas" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-emerald-50 group-hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition-colors">
              <LayoutDashboard class="w-4 h-4" />
            </div>
            <span>Dashboard Kontrol</span>
          </NuxtLink>

          <NuxtLink 
            to="/petugas/keluar" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-amber-50 group-hover:bg-amber-100 text-amber-600 flex items-center justify-center transition-colors">
              <LogOut class="w-4 h-4" />
            </div>
            <span>Gate Keluar (Tap Out)</span>
          </NuxtLink>

          <NuxtLink 
            to="/petugas/transaksi/select" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-indigo-50 group-hover:bg-indigo-100 text-indigo-600 flex items-center justify-center transition-colors">
              <CreditCard class="w-4 h-4" />
            </div>
            <span>Kelola Transaksi</span>
          </NuxtLink>

          <NuxtLink 
            to="/petugas/member/select" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-purple-50 group-hover:bg-purple-100 text-purple-600 flex items-center justify-center transition-colors">
              <Users class="w-4 h-4" />
            </div>
            <span>Kelola Member</span>
          </NuxtLink>

          <NuxtLink 
            to="/petugas/laporan" 
            exact-active-class="bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-600 shadow-sm" 
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-xs font-semibold group"
          >
            <div class="w-7 h-7 rounded-lg bg-rose-50 group-hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors">
              <BarChart3 class="w-4 h-4" />
            </div>
            <span>Laporan Parkir</span>
          </NuxtLink>
        </nav>
      </div>
    </div>

    <!-- Status Footer -->
    <div class="p-3.5 border-t border-slate-100 bg-slate-50/50">
      <div class="flex items-center justify-between text-xs px-1">
        <div class="flex items-center gap-1.5 text-slate-500 font-medium">
          <ShieldCheck class="w-3.5 h-3.5 text-emerald-600" />
          <span>Sistem v2.4</span>
        </div>
        <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-[11px] bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Siap
        </span>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { 
  LayoutDashboard, 
  LogOut, 
  CreditCard, 
  Users, 
  BarChart3,
  ShieldCheck 
} from 'lucide-vue-next';

defineProps<{
  isOpen: boolean;
}>();

const { getUser } = useAuth();
const user = getUser();
</script>
