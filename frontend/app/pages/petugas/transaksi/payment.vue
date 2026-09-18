<template>
  <div class="h-screen bg-[#f8fafc] flex flex-col font-sans overflow-hidden">
    <!-- Navbar -->
    <LayoutNavbar @toggleSidebar="isSidebarOpen = !isSidebarOpen" />

    <div class="flex-1 flex overflow-hidden relative">
      <LayoutSidebar :isOpen="isSidebarOpen" />
      <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7 flex items-center justify-center">
        <div class="max-w-lg w-full">

          <!-- Card -->
          <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xl overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 bg-emerald-600 text-white flex items-center justify-between">
              <div class="flex items-center gap-3">
                <NuxtLink 
                  to="/petugas/transaksi/select" 
                  class="p-2 rounded-xl bg-white/20 hover:bg-white/30 transition text-white flex items-center justify-center cursor-pointer"
                  title="Kembali ke Daftar Transaksi"
                >
                  <ArrowLeft class="w-4 h-4" />
                </NuxtLink>
                <div>
                  <h1 class="text-base font-bold">Pelunasan Tagihan Kasir</h1>
                  <p class="text-xs text-emerald-100">Penerimaan kas pembayaran iuran parkir member</p>
                </div>
              </div>
              <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                <CreditCard class="w-5 h-5 text-white" />
              </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-12 text-center text-slate-500">
              <div class="w-8 h-8 border-3 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
              <p class="text-xs font-semibold text-slate-600">Memuat data tagihan member...</p>
            </div>

            <!-- Member Found -->
            <div v-else-if="member" class="p-6 sm:p-8 space-y-6">
              
              <!-- Member Info Header -->
              <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-lg font-black border border-emerald-200 shrink-0">
                  {{ (member.nama_member || member.nama) ? (member.nama_member || member.nama).charAt(0).toUpperCase() : 'M' }}
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-bold text-slate-900 text-sm truncate">{{ member.nama_member || member.nama }}</h3>
                  <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                    <span class="font-mono bg-white px-2 py-0.5 rounded border border-slate-200 text-slate-700 font-bold text-[11px]">
                      {{ member.kode_member }}
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="truncate">{{ member.nama_perusahaan || member.perusahaan || 'Personal Member' }}</span>
                  </div>
                </div>
              </div>

              <!-- Bill Display Card -->
              <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 text-center">
                <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider mb-1">Total Sisa Tagihan</p>
                <h2 class="text-3xl sm:text-4xl font-black text-emerald-700 tracking-tight font-mono">
                  {{ formatRupiah(sisaTagihan) }}
                </h2>
                <div class="mt-3 pt-3 border-t border-emerald-200/60 flex items-center justify-around text-xs text-slate-600">
                  <span>Total Kewajiban: <b class="text-slate-800">{{ formatRupiah(member.total_harga ?? member.tagihan) }}</b></span>
                  <span class="text-emerald-300">|</span>
                  <span>Sudah Dibayar: <b class="text-emerald-700">{{ formatRupiah(member.jumlah_bayar ?? member.dibayar) }}</b></span>
                </div>
              </div>

              <!-- Payment Form -->
              <form @submit.prevent="submitPayment" class="space-y-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Metode Pembayaran
                  </label>
                  <div class="grid grid-cols-3 gap-2.5">
                    <button 
                      type="button" 
                      @click="metode = 'tunai'"
                      class="py-3 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1.5 cursor-pointer focus:outline-none" 
                      :class="metode === 'tunai' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 ring-2 ring-emerald-500/20 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                    >
                      <Banknote class="w-5 h-5" :class="metode === 'tunai' ? 'text-emerald-600' : 'text-slate-400'" />
                      <span>Tunai (Cash)</span>
                    </button>
                    <button 
                      type="button" 
                      @click="metode = 'qris'"
                      class="py-3 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1.5 cursor-pointer focus:outline-none" 
                      :class="metode === 'qris' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 ring-2 ring-emerald-500/20 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                    >
                      <QrCode class="w-5 h-5" :class="metode === 'qris' ? 'text-emerald-600' : 'text-slate-400'" />
                      <span>QRIS Dinamis</span>
                    </button>
                    <button 
                      type="button" 
                      @click="metode = 'transfer'"
                      class="py-3 px-3 rounded-xl border text-xs font-bold transition flex flex-col items-center gap-1.5 cursor-pointer focus:outline-none" 
                      :class="metode === 'transfer' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 ring-2 ring-emerald-500/20 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                    >
                      <Building2 class="w-5 h-5" :class="metode === 'transfer' ? 'text-emerald-600' : 'text-slate-400'" />
                      <span>Transfer Bank</span>
                    </button>
                  </div>
                </div>

                <div>
                  <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                      Nominal Diterima Kasir (Rp)
                    </label>
                    <button 
                      type="button" 
                      @click="jumlahBayar = sisaTagihan"
                      class="text-[11px] text-emerald-600 font-bold hover:underline cursor-pointer flex items-center gap-1"
                    >
                      <CheckCircle2 class="w-3.5 h-3.5" />
                      <span>Bayar Pas</span>
                    </button>
                  </div>
                  <input 
                    type="number" 
                    v-model.number="jumlahBayar" 
                    required 
                    min="1"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xl font-black text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 font-mono transition" 
                    placeholder="Masukkan nominal"
                  />

                  <!-- Quick Presets -->
                  <div class="flex gap-1.5 mt-2 overflow-x-auto pb-1">
                    <button 
                      type="button" 
                      @click="jumlahBayar = sisaTagihan" 
                      class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-[11px] font-semibold text-slate-700 cursor-pointer transition shrink-0"
                    >
                      Pas: {{ formatRupiah(sisaTagihan) }}
                    </button>
                    <button 
                      type="button" 
                      @click="jumlahBayar = 50000" 
                      class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-[11px] font-semibold text-slate-700 cursor-pointer transition shrink-0"
                    >
                      50.000
                    </button>
                    <button 
                      type="button" 
                      @click="jumlahBayar = 100000" 
                      class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-[11px] font-semibold text-slate-700 cursor-pointer transition shrink-0"
                    >
                      100.000
                    </button>
                    <button 
                      type="button" 
                      @click="jumlahBayar = 150000" 
                      class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-[11px] font-semibold text-slate-700 cursor-pointer transition shrink-0"
                    >
                      150.000
                    </button>
                    <button 
                      type="button" 
                      @click="jumlahBayar = 200000" 
                      class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-[11px] font-semibold text-slate-700 cursor-pointer transition shrink-0"
                    >
                      200.000
                    </button>
                  </div>
                </div>

                <!-- Kembalian Info -->
                <div 
                  v-if="metode === 'tunai' && kembalian > 0" 
                  class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex justify-between items-center text-xs"
                >
                  <span class="text-amber-900 font-bold flex items-center gap-2">
                    <Coins class="w-4 h-4 text-amber-600" />
                    Uang Kembalian Pelanggan:
                  </span>
                  <span class="text-amber-900 font-black text-lg font-mono">{{ formatRupiah(kembalian) }}</span>
                </div>

                <!-- Submit Button -->
                <button 
                  type="submit" 
                  :disabled="isSubmitting || !jumlahBayar || Number(jumlahBayar) <= 0" 
                  class="w-full mt-3 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-lg shadow-emerald-600/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                  <span v-if="isSubmitting">Memproses Pembayaran...</span>
                  <span v-else class="flex items-center gap-2">
                    <CheckCircle2 class="w-4 h-4" />
                    <span>Konfirmasi Pembayaran</span>
                  </span>
                </button>
              </form>

            </div>

            <!-- Not Found State -->
            <div v-else class="p-12 text-center text-slate-500 space-y-3">
              <AlertCircle class="w-10 h-10 text-amber-500 mx-auto" />
              <p class="font-bold text-slate-800">Member tidak ditemukan</p>
              <p class="text-xs text-slate-400">Data tagihan tidak dapat diakses atau ID member tidak valid.</p>
              <NuxtLink 
                to="/petugas/transaksi/select" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition cursor-pointer"
              >
                <ArrowLeft class="w-3.5 h-3.5" />
                <span>Kembali ke Daftar</span>
              </NuxtLink>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { 
  ArrowLeft, CreditCard, Banknote, QrCode, Building2, 
  CheckCircle2, Coins, AlertCircle 
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const { $api } = useNuxtApp();

const isSidebarOpen = ref(false);
const member = ref<any>(null);
const loading = ref(true);
const isSubmitting = ref(false);
const metode = ref('tunai');
const jumlahBayar = ref<number | ''>('');

const sisaTagihan = computed(() => {
  if (!member.value) return 0;
  const total = member.value.total_harga ?? member.value.tagihan ?? 0;
  const paid = member.value.jumlah_bayar ?? member.value.dibayar ?? 0;
  return Math.max(0, total - paid);
});

const kembalian = computed(() => {
  if (typeof jumlahBayar.value === 'number' && jumlahBayar.value > sisaTagihan.value) {
    return jumlahBayar.value - sisaTagihan.value;
  }
  return 0;
});

const fetchMember = async () => {
  const id = route.query.member_id;
  if (!id) {
    loading.value = false;
    return;
  }

  try {
    const res = await $api.get(`/member/${id}`);
    if (res.data?.success) {
      member.value = res.data.data;
      const total = res.data.data.total_harga ?? res.data.data.tagihan ?? 0;
      const paid = res.data.data.jumlah_bayar ?? res.data.data.dibayar ?? 0;
      jumlahBayar.value = Math.max(0, total - paid);
    }
  } catch (error) {
    console.error('Failed to fetch member:', error);
  } finally {
    loading.value = false;
  }
};

const submitPayment = async () => {
  if (!member.value || typeof jumlahBayar.value !== 'number' || jumlahBayar.value <= 0) return;

  isSubmitting.value = true;
  try {
    const res = await $api.put(`/member/${member.value.id}/pembayaran`, {
      jumlah_bayar: jumlahBayar.value,
      is_pelunasan: true
    });

    if (res.data?.success !== false) {
      alert(res.data?.message || 'Pembayaran berhasil dikonfirmasi!');
      router.push('/petugas/transaksi/select');
    } else {
      alert(res.data?.message || 'Gagal melakukan pembayaran.');
    }
  } catch (error: any) {
    console.error('Failed to submit payment:', error);
    alert(error?.response?.data?.message || 'Terjadi kesalahan saat memproses pembayaran.');
  } finally {
    isSubmitting.value = false;
  }
};

const formatRupiah = (amount: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
};

onMounted(() => {
  fetchMember();
});
</script>

