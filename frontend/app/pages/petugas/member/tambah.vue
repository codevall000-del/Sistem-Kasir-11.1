<template>
  <div class="h-screen bg-[#f8fafc] flex flex-col font-sans overflow-hidden">
    <LayoutNavbar @toggleSidebar="isSidebarOpen = !isSidebarOpen" />

    <div class="flex-1 flex overflow-hidden relative">
      <LayoutSidebar :isOpen="isSidebarOpen" />
      <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>

      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7 flex items-center justify-center">
        <div class="max-w-md w-full">
          <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 bg-emerald-600 text-white flex items-center justify-between">
              <div class="flex items-center gap-3">
                <NuxtLink 
                  to="/petugas/member/select" 
                  class="p-2 rounded-xl bg-white/20 hover:bg-white/30 transition text-white flex items-center justify-center cursor-pointer"
                  title="Kembali"
                >
                  <ArrowLeft class="w-4 h-4" />
                </NuxtLink>
                <div>
                  <h1 class="text-base font-bold">Pendaftaran Member Baru</h1>
                  <p class="text-xs text-emerald-100">Registrasi kartu RFID langganan tetap</p>
                </div>
              </div>
              <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                <UserPlus class="w-5 h-5 text-white" />
              </div>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="simpan" class="p-6 sm:p-8 space-y-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Nama Lengkap Pemohon
                </label>
                <div class="relative">
                  <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input 
                    v-model="form.nama_member" 
                    type="text" 
                    required
                    placeholder="Contoh: Budi Pratama" 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition font-medium"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Instansi / Perusahaan / Unit
                </label>
                <div class="relative">
                  <Building2 class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input 
                    v-model="form.nama_perusahaan" 
                    type="text" 
                    required
                    placeholder="Contoh: PT. Maju Bersama / Personal" 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition font-medium"
                  />
                </div>
              </div>

              <!-- Info Tarif Langganan (Tetap 150k) -->
              <div class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs">
                <div>
                  <span class="text-slate-600 font-medium block">Tarif Langganan Bulanan:</span>
                  <span class="text-[11px] text-slate-400">Pembayaran tunai di loket</span>
                </div>
                <span class="font-mono font-bold text-slate-900 text-sm">Rp 150.000</span>
              </div>

              <!-- Input Uang Diterima dari Member -->
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Uang yang Diberikan Member (Rp)
                </label>
                <div class="relative">
                  <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs text-slate-400 font-mono">Rp</span>
                  <input 
                    v-model.number="form.jumlah_bayar" 
                    type="number" 
                    min="0"
                    step="1000"
                    required
                    placeholder="150000" 
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition font-mono font-bold text-slate-900"
                  />
                </div>
                <!-- Tulisan kecil kembalian yang harus diberikan ke si member -->
                <div class="mt-2 text-xs">
                  <p v-if="kembalian > 0" class="text-emerald-700 font-semibold flex items-center gap-1.5">
                    <span>Kembalian yang harus diberikan:</span>
                    <span class="font-mono font-bold text-emerald-800">Rp {{ formatRupiah(kembalian) }}</span>
                  </p>
                  <p v-else-if="kembalian === 0 && form.jumlah_bayar" class="text-slate-500 font-medium">
                    Uang pas (tidak ada kembalian).
                  </p>
                  <p v-else-if="isKurang" class="text-rose-600 font-semibold">
                    Uang kurang Rp {{ formatRupiah(Math.abs(kembalian)) }}
                  </p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="pt-2 space-y-2">
                <button 
                  type="submit" 
                  :disabled="loading || isKurang || !form.jumlah_bayar" 
                  class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/30 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                  <CheckCircle2 class="w-4 h-4" />
                  <span>{{ loading ? 'Mendaftarkan Member...' : 'Simpan & Daftarkan Member' }}</span>
                </button>
                <NuxtLink 
                  to="/petugas/member/select" 
                  class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition text-center block"
                >
                  Batal
                </NuxtLink>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { 
  ArrowLeft, UserPlus, User, Building2, Banknote, CheckCircle2 
} from 'lucide-vue-next';

const { $api } = useNuxtApp();
const router = useRouter();

const isSidebarOpen = ref(false);
const loading = ref(false);

const TARIF_LANGGANAN = 150000;

const form = reactive<any>({
  nama_member: '',
  nama_perusahaan: '',
  total_harga: TARIF_LANGGANAN,
  jumlah_bayar: TARIF_LANGGANAN,
  tanggal_mulai: '',
  tanggal_expired: ''
});

const kembalian = computed(() => {
  const bayar = Number(form.jumlah_bayar) || 0;
  return bayar - TARIF_LANGGANAN;
});

const isKurang = computed(() => kembalian.value < 0);

const simpan = async () => {
  if (!form.nama_member || !form.nama_perusahaan) {
    alert('Nama dan Instansi / Perusahaan wajib diisi');
    return;
  }

  if (isKurang.value) {
    alert('Uang pembayaran kurang dari tarif langganan!');
    return;
  }

  try {
    loading.value = true;
    const res = await $api.post('/member', {
      ...form,
      total_harga: Number(form.total_harga),
      jumlah_bayar: Number(form.jumlah_bayar),
    });

    if (!res.data.success) {
      alert(res.data.message || 'Gagal menyimpan member');
      return;
    }

    alert('Member berhasil didaftarkan!');
    router.push('/petugas/member/select');
  } catch (error: any) {
    console.error('Gagal menyimpan member:', error);
    alert(error?.response?.data?.message || 'Gagal menyimpan member');
  } finally {
    loading.value = false;
  }
};

const formatRupiah = (val: any) => {
  return new Intl.NumberFormat('id-ID').format(Number(val || 0));
};
</script>
