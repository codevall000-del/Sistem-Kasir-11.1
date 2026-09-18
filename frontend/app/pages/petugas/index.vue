<template>
  <div class="h-screen bg-[#f8fafc] flex flex-col font-sans overflow-hidden">
    <!-- 1. TOP NAVBAR -->
    <LayoutNavbar @toggleSidebar="isSidebarOpen = !isSidebarOpen" />

    <div class="flex-1 flex overflow-hidden relative">
      <!-- 2. SIDEBAR -->
      <LayoutSidebar :isOpen="isSidebarOpen" />

      <!-- Backdrop for mobile drawer -->
      <div 
        v-if="isSidebarOpen" 
        @click="isSidebarOpen = false" 
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-30 lg:hidden"
      ></div>

      <!-- 3. MAIN DASHBOARD CONTENT -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        <div class="max-w-[1400px] mx-auto space-y-7">

          <!-- Dashboard Header & Breadcrumb -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                  Panel Operasional
                </span>
                <span class="text-xs text-slate-400 font-mono">• Shift Aktif</span>
              </div>
              <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                {{ greetingTime }}, {{ user?.name || 'Petugas' }}
              </h1>
              <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                <Home class="w-3.5 h-3.5 text-slate-400" />
                <span class="text-slate-400">Beranda</span>
                <ChevronRight class="w-3 h-3 text-slate-300" />
                <span class="text-emerald-700 font-bold">Ringkasan Sistem Parkir</span>
              </p>
            </div>

            <!-- Date & Export Actions -->
            <div class="flex items-center gap-2.5 flex-wrap">
              <div class="px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 shadow-xs flex items-center gap-2 font-mono">
                <Calendar class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ currentDate }}</span>
              </div>
              <button 
                @click="exportReport" 
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm shadow-emerald-600/25 flex items-center gap-2 cursor-pointer"
                title="Unduh data transaksi dalam format CSV"
              >
                <Download class="w-3.5 h-3.5" />
                <span>Export CSV</span>
              </button>
            </div>
          </div>

          <!-- ======================================================== -->
          <!-- 4. KEY PERFORMANCE INDICATORS (KPI CARDS)                -->
          <!-- ======================================================== -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <DashboardStatCard 
              title="Kendaraan Masuk Hari Ini"
              :value="stats.kendaraanMasuk || 0"
              color="blue"
              iconType="car"
              subtitle="Volume transaksi harian"
              badge="Hari Ini"
            />
            <DashboardStatCard 
              title="Pemasukan Kas Hari Ini"
              :value="formatRupiah(stats.pendapatan || 0)"
              color="green"
              iconType="wallet"
              subtitle="Akumulasi pembayaran parkir"
              badge="Kas Aktif"
            />
            <DashboardStatCard 
              title="Member Terdaftar"
              :value="stats.totalMember || 0"
              color="purple"
              iconType="user"
              subtitle="Pengguna kartu langganan"
              badge="Terdaftar"
            />
            <DashboardStatCard 
              title="Sedang Parkir (Aktif)"
              :value="(stats.sedangParkir || 0) + ' Unit'"
              color="orange"
              iconType="parking"
              subtitle="Kendaraan berada di dalam"
              badge="Real-time"
            />
          </div>

          <!-- ======================================================== -->
          <!-- 5. GERBANG OPERASIONAL UTAMA (SMART GATE CONTROLLER)     -->
          <!-- ======================================================== -->
          <div>
            <div class="flex items-center justify-between mb-3 px-1">
              <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                Akses Langsung Gerbang Lapangan
              </h2>
              <span class="text-[11px] text-slate-400 font-medium">Pilih gerbang untuk simulasi & operasi</span>
            </div>

            <div>
              <!-- GATE OUT (PINTU KELUAR) -->
              <NuxtLink
                to="/petugas/keluar"
                class="group bg-white hover:bg-amber-50/40 border border-slate-200/90 hover:border-amber-300 rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between cursor-pointer"
              >
                <div class="flex items-center gap-4">
                  <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                    <LogOut class="w-6 h-6" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-mono font-bold tracking-wider uppercase border border-amber-200">
                        GATE 01 OUT
                      </span>
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 group-hover:text-amber-700 transition-colors">
                      Terminal Keluar (Tap Out)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                      Scan tiket barcode, kalkulasi durasi tarif & pembayaran kasir
                    </p>
                  </div>
                </div>

                <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 group-hover:text-white group-hover:bg-amber-600 group-hover:border-amber-600 flex items-center justify-center transition-all shrink-0 ml-3">
                  <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </div>
              </NuxtLink>
            </div>
          </div>

          <!-- ======================================================== -->
          <!-- 6. ANALYTICS GRIDS (2 CHARTS)                            -->
          <!-- ======================================================== -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
            <!-- Kolom 1: Grafik Transaksi Per Bulan -->
            <DashboardMonthlyChart 
              title="Grafik Transaksi Kendaraan Bulanan"
              subtitle="Rekapitulasi total kendaraan parkir masuk per bulan"
              :data="monthlyTransactions"
              colorType="blue"
            />

            <!-- Kolom 2: Grafik Pemasukan Per Bulan -->
            <DashboardMonthlyChart 
              title="Grafik Pemasukan Kas Parkir Bulanan"
              subtitle="Akumulasi pendapatan tarif parkir per bulan (IDR)"
              :data="monthlyRevenue"
              colorType="emerald"
              :isCurrency="true"
            />
          </div>

          <!-- ======================================================== -->
          <!-- 7. TABEL TRANSAKSI TERKINI                               -->
          <!-- ======================================================== -->
          <DashboardTransactionTable :transactions="allTransactions" />

        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { 
  Home, 
  ChevronRight, 
  Calendar, 
  Download, 
  LogOut, 
  ArrowRight 
} from 'lucide-vue-next';
import Navbar from '~/components/layout/Navbar.vue';
import Sidebar from '~/components/layout/Sidebar.vue';
import StatCard from '~/components/dashboard/StatCard.vue';
import MonthlyChart from '~/components/dashboard/MonthlyChart.vue';
import TransactionTable from '~/components/dashboard/TransactionTable.vue';

const { getUser, isLoggedIn, isPetugas, isAdmin } = useAuth();
const { $api } = useNuxtApp();
const router = useRouter();
const user = getUser();

const isSidebarOpen = ref(false);
const currentDate = ref('');
const stats = ref({
  totalMember: 0,
  kendaraanMasuk: 0,
  pendapatan: 0,
  sedangParkir: 0
});

const greetingTime = computed(() => {
  const hour = new Date().getHours();
  if (hour >= 4 && hour < 11) return 'Selamat Pagi';
  if (hour >= 11 && hour < 15) return 'Selamat Siang';
  if (hour >= 15 && hour < 18) return 'Selamat Sore';
  return 'Selamat Malam';
});

// Default 12 Bulan
const currentMonthIdx = new Date().getMonth();
const defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

// Data Grafik Transaksi Bulanan Real-time
const monthlyTransactions = ref(
  defaultMonths.map((m, idx) => ({ label: m, value: 0, highlight: idx === currentMonthIdx }))
);

// Data Grafik Pemasukan Bulanan Real-time
const monthlyRevenue = ref(
  defaultMonths.map((m, idx) => ({ label: m, value: 0, highlight: idx === currentMonthIdx }))
);

// Data Transaksi Tabel Real-time dari Database
const allTransactions = ref<any[]>([]);

let intervalId: any = null;

const formatRupiah = (value: number) => {
  if (!value) return 'Rp 0';
  return 'Rp ' + Number(value).toLocaleString('id-ID');
};

const updateCurrentDate = () => {
  const options: Intl.DateTimeFormatOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  currentDate.value = new Date().toLocaleDateString('id-ID', options);
};

const fetchStats = async () => {
  try {
    const res = await $api.get('/dashboard/stats');
    if (res.data?.success && res.data.data) {
      const d = res.data.data;
      if (d.pendapatan !== undefined) stats.value.pendapatan = d.pendapatan;
      if (d.kendaraanMasuk !== undefined) stats.value.kendaraanMasuk = d.kendaraanMasuk;
      if (d.totalMember !== undefined) stats.value.totalMember = d.totalMember;
      if (d.sedangParkir !== undefined) stats.value.sedangParkir = d.sedangParkir;
      if (Array.isArray(d.monthlyTransactions) && d.monthlyTransactions.length > 0) {
        monthlyTransactions.value = d.monthlyTransactions;
      }
      if (Array.isArray(d.monthlyRevenue) && d.monthlyRevenue.length > 0) {
        monthlyRevenue.value = d.monthlyRevenue;
      }
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
  }
};

const fetchTransactions = async () => {
  try {
    const res = await $api.get('/parkir');
    if (res.data?.success && Array.isArray(res.data.data)) {
      allTransactions.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching transactions:', error);
  }
};

const exportReport = () => {
  const csvContent = 'data:text/csv;charset=utf-8,' 
    + 'Kode,Plat Nomor,Jenis Kendaraan,Waktu Masuk,Waktu Keluar,Durasi,Biaya,Status\n'
    + allTransactions.value.map(t => {
      const plat = (t.status === 'selesai' && t.waktu_keluar && t.waktu_keluar !== '-') ? (t.plat_nomor || '-') : '-';
      return `${t.kode_tiket},${plat},${t.jenis_kendaraan || 'Kendaraan'},${t.waktu_masuk},${t.waktu_keluar},${t.durasi},${t.biaya},${t.status}`;
    }).join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', 'Laporan_E-Parking_' + new Date().toISOString().split('T')[0] + '.csv');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(() => {
  if (!isLoggedIn() || (!isPetugas() && !isAdmin())) {
    router.push('/');
    return;
  }
  updateCurrentDate();
  fetchStats();
  fetchTransactions();
  intervalId = setInterval(() => {
    fetchStats();
    fetchTransactions();
  }, 30000);
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>
