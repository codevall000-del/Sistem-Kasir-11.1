<template>
  <div class="h-screen bg-[#f8fafc] flex flex-col font-sans overflow-hidden">
    <!-- Navbar -->
    <LayoutNavbar @toggleSidebar="isSidebarOpen = !isSidebarOpen" />

    <div class="flex-1 flex overflow-hidden relative">
      <!-- Sidebar -->
      <LayoutSidebar :isOpen="isSidebarOpen" />

      <!-- Mobile Backdrop -->
      <div 
        v-if="isSidebarOpen" 
        @click="isSidebarOpen = false" 
        class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden"
      ></div>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7">
        <div class="max-w-[1400px] mx-auto space-y-6">

          <!-- Page Header & Breadcrumb -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
              <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Kelola Transaksi</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  <CreditCard class="w-3.5 h-3.5" />
                  Kasir & Billing
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                <NuxtLink to="/petugas" class="hover:text-emerald-600 transition">Dashboard</NuxtLink>
                <span class="text-slate-300">/</span>
                <span class="text-emerald-700 font-semibold">Pelunasan & Rekam Kas</span>
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button 
                @click="refreshAll" 
                class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400"
              >
                <RefreshCw class="w-3.5 h-3.5 text-slate-500" />
                <span>Segarkan Data</span>
              </button>
            </div>
          </div>

          <!-- Quick Stat Cards -->
          <!-- Stat Cards Ringkas -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                <Users class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Member Terdaftar</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ allMembersList.length }} <span class="text-xs font-medium text-slate-500">Orang</span></h3>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                <CheckCircle2 class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Member Aktif</p>
                <h3 class="text-2xl font-black text-emerald-700 tracking-tight mt-0.5">{{ memberAktifCount }} <span class="text-xs font-medium text-slate-500">Orang</span></h3>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100 shrink-0">
                <Car class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Parkir Hari Ini</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ parkirList.length }} <span class="text-xs font-medium text-slate-500">Kendaraan</span></h3>
              </div>
            </div>
          </div>

          <!-- Section 1: Kelola Iuran Seluruh Member -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
              <div>
                <h2 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                  <CreditCard class="w-4 h-4 text-emerald-600" />
                  Status & Pembayaran Iuran Member
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola perpanjangan langganan bulanan dan cetak struk pembayaran resmi</p>
              </div>

              <!-- Pencarian Cepat -->
              <div class="relative w-full sm:w-64">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari nama atau kode member..."
                  class="w-full pl-9 pr-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                />
              </div>
            </div>

            <!-- Tabel Seluruh Member -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                  <tr>
                    <th class="px-5 py-3.5">Kode Member</th>
                    <th class="px-5 py-3.5">Nama Lengkap</th>
                    <th class="px-5 py-3.5">Perusahaan</th>
                    <th class="px-5 py-3.5 text-right">Tarif Iuran</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5">Masa Berlaku</th>
                    <th class="px-5 py-3.5 text-center w-60">Aksi Pembayaran</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr 
                    v-for="member in filteredMemberList" 
                    :key="member.id" 
                    class="hover:bg-slate-50/80 transition-colors"
                  >
                    <td class="px-5 py-3.5">
                      <span class="px-2 py-0.5 rounded bg-emerald-50 font-mono text-xs font-bold text-emerald-800 border border-emerald-200">
                        {{ member.kode_member }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ member.nama_member || member.nama }}</td>
                    <td class="px-5 py-3.5 text-slate-500">{{ member.nama_perusahaan || member.perusahaan || '-' }}</td>
                    <td class="px-5 py-3.5 font-black text-slate-900 text-right">Rp 150.000</td>
                    <td class="px-5 py-3.5 text-center">
                      <span 
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider"
                        :class="isMemberExpired(member) ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                      >
                        {{ isMemberExpired(member) ? 'Kadaluarsa' : 'Aktif' }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">
                      {{ formatTanggal(member.tanggal_expired) }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                      <div class="flex items-center justify-center gap-2">
                        <!-- Tombol Perpanjang Iuran -->
                        <button 
                          type="button"
                          v-if="canPerpanjang(member)"
                          @click="openPerpanjangModal(member)"
                          class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer"
                          :title="getPerpanjangTooltip(member)"
                        >
                          <RefreshCw class="w-3.5 h-3.5" />
                          <span>Perpanjang Iuran</span>
                        </button>
                        <button 
                          type="button"
                          v-else
                          disabled
                          class="px-3 py-1.5 bg-slate-100 text-slate-400 border border-slate-200 text-xs font-semibold rounded-xl inline-flex items-center gap-1.5 cursor-not-allowed"
                          :title="getPerpanjangTooltip(member)"
                        >
                          <RefreshCw class="w-3.5 h-3.5 text-slate-300" />
                          <span>Belum Bisa Perpanjang</span>
                        </button>
                        <!-- Tombol Cetak Struk -->
                        <button 
                          type="button"
                          @click="printStrukMember(member)" 
                          class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer"
                          title="Cetak struk resmi pembayaran"
                        >
                          <Printer class="w-3.5 h-3.5 text-emerald-400" />
                          <span>Struk</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredMemberList.length === 0">
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                      <Users class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                      <p class="font-bold text-slate-700">Tidak ada data member yang ditemukan</p>
                      <p class="text-[11px] text-slate-400 mt-0.5">Silakan periksa kata kunci pencarian Anda.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Section 2: Transaksi Parkir Hari Ini -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
              <div>
                <h2 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                  <Car class="w-4 h-4 text-emerald-600" />
                  Riwayat Parkir Hari Ini
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantauan log kendaraan non-member dan member masuk-keluar secara langsung</p>
              </div>
              <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ parkirList.length }} Aktivitas Terdata
              </span>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                  <tr>
                    <th class="px-5 py-3.5">Kode Tiket / ID</th>
                    <th class="px-5 py-3.5 text-center">Tipe</th>
                    <th class="px-5 py-3.5">Pengguna</th>
                    <th class="px-5 py-3.5">Plat Nomor</th>
                    <th class="px-5 py-3.5">Waktu Masuk</th>
                    <th class="px-5 py-3.5">Waktu Keluar</th>
                    <th class="px-5 py-3.5 text-right">Tarif Parkir</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr 
                    v-for="p in parkirList" 
                    :key="p.id" 
                    class="hover:bg-slate-50/80 transition-colors"
                  >
                    <td class="px-5 py-3.5 font-bold text-slate-800 font-mono">
                      <span class="px-2 py-0.5 rounded border text-[11px]" :class="p.tipe === 'member' ? 'bg-purple-50 text-purple-700 border-purple-200 font-bold' : 'bg-slate-100 text-slate-800 border-slate-200'">
                        {{ p.kode_tiket || ('TRX-' + String(p.id).padStart(4, '0')) }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                      <span 
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider"
                        :class="p.tipe === 'member' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-sky-50 text-sky-700 border-sky-200'"
                      >
                        {{ p.tipe === 'member' ? 'Member' : 'Non-Member' }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 font-bold text-slate-900">
                      {{ p.nama || (p.tipe === 'member' ? 'Member' : 'Pengunjung Reguler') }}
                    </td>
                    <td class="px-5 py-3.5 font-mono font-bold text-slate-700 uppercase">
                      <!-- Plat nomor HANYA tercatat saat sudah keluar -->
                      {{ (p.status === 'selesai' && p.waktu_keluar && p.waktu_keluar !== '-') ? (p.plat_nomor || '-') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">{{ formatWaktu(p.waktu_masuk || p.created_at) }}</td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">{{ formatWaktu(p.waktu_keluar) }}</td>
                    <td class="px-5 py-3.5 text-right font-mono">
                      <span v-if="p.tipe === 'member'" class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                        Rp 0 (Iuran)
                      </span>
                      <span v-else class="font-bold text-slate-800">
                        {{ formatRupiah(p.biaya || p.total_biaya || 0) }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                      <span 
                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" 
                        :class="(p.waktu_keluar || p.status === 'selesai') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                      >
                        {{ (p.waktu_keluar || p.status === 'selesai') ? 'Selesai' : 'Aktif Parkir' }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="parkirList.length === 0">
                    <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                      <Car class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                      <p class="font-medium text-slate-600">Belum ada aktivitas transaksi parkir hari ini.</p>
                      <p class="text-[11px] text-slate-400 mt-0.5">Transaksi tiket akan tampil otomatis saat kendaraan melintas.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Modal Perpanjang Iuran Member -->
          <div 
            v-if="showPerpanjangModal" 
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in"
          >
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 border border-slate-100 max-h-[90vh] overflow-y-auto">
              <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center">
                    <RefreshCw class="w-4 h-4" />
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-slate-900 leading-tight">Perpanjang Iuran Member</h3>
                    <p class="text-xs text-slate-400">Pembaruan masa aktif kartu RFID</p>
                  </div>
                </div>
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                  Member
                </span>
              </div>

              <!-- Rincian Member -->
              <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2 text-xs text-slate-700 mb-4">
                <div class="flex justify-between">
                  <span class="text-slate-400 font-medium">Kode Member:</span>
                  <span class="font-mono font-bold text-slate-900">{{ selectedMember?.kode_member }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400 font-medium">Nama Anggota:</span>
                  <span class="font-bold text-slate-900">{{ selectedMember?.nama_member || selectedMember?.nama }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400 font-medium">Masa Berlaku Saat Ini:</span>
                  <span class="font-semibold text-slate-700">{{ formatTanggal(selectedMember?.tanggal_expired) }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200/80 pt-2 mt-2 items-center">
                  <span class="font-bold text-slate-900">Tarif Iuran (+1 Bulan):</span>
                  <span class="text-sm font-black text-emerald-700 font-mono">Rp 150.000</span>
                </div>
              </div>

              <!-- Input Uang Diterima dari Member -->
              <div class="mb-5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Uang yang Diberikan Member (Rp)
                </label>
                <div class="relative">
                  <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs text-slate-400 font-mono">Rp</span>
                  <input 
                    v-model.number="perpanjangBayar" 
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
                  <p v-if="perpanjangKembalian > 0" class="text-emerald-700 font-semibold flex items-center gap-1.5">
                    <span>Kembalian yang harus diberikan:</span>
                    <span class="font-mono font-bold text-emerald-800">{{ formatRupiah(perpanjangKembalian) }}</span>
                  </p>
                  <p v-else-if="perpanjangKembalian === 0 && perpanjangBayar" class="text-slate-500 font-medium">
                    Uang pas (tidak ada kembalian).
                  </p>
                  <p v-else-if="isPerpanjangKurang" class="text-rose-600 font-semibold">
                    Uang kurang {{ formatRupiah(Math.abs(perpanjangKembalian)) }}
                  </p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="grid grid-cols-2 gap-2.5">
                <button 
                  type="button"
                  @click="showPerpanjangModal = false"
                  class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer"
                >
                  Batal
                </button>
                <button 
                  type="button"
                  @click="confirmPerpanjang"
                  :disabled="loadingPerpanjang || isPerpanjangKurang || !perpanjangBayar"
                  class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-emerald-600/30 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer flex items-center justify-center gap-1.5"
                >
                  <RefreshCw v-if="loadingPerpanjang" class="w-3.5 h-3.5 animate-spin" />
                  <span>{{ loadingPerpanjang ? 'Memproses...' : 'Konfirmasi & Bayar' }}</span>
                </button>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { jsPDF } from 'jspdf';
import QrCode from 'qrcode';
import { 
  CreditCard, RefreshCw, Clock, Car, CheckCircle2, Printer, Users, Search, Banknote
} from 'lucide-vue-next';

const { $api } = useNuxtApp();
const { getUser } = useAuth();
const user = getUser();
const isSidebarOpen = ref(false);

const allMembersList = ref<any[]>([]);
const searchQuery = ref('');
const parkirList = ref<any[]>([]);

const showPerpanjangModal = ref(false);
const selectedMember = ref<any>(null);
const loadingPerpanjang = ref(false);

const TARIF_IURAN = 150000;
const perpanjangBayar = ref(TARIF_IURAN);

const perpanjangKembalian = computed(() => {
  return (Number(perpanjangBayar.value) || 0) - TARIF_IURAN;
});

const isPerpanjangKurang = computed(() => {
  return (Number(perpanjangBayar.value) || 0) < TARIF_IURAN;
});

const isMemberExpired = (m: any) => {
  if (!m.tanggal_expired) return false;
  return new Date(m.tanggal_expired) < new Date();
};

const isBulanHabis = (tanggalExpired: string) => {
  if (!tanggalExpired) return false;
  const now = new Date();
  const exp = new Date(tanggalExpired);
  const currentYM = now.getFullYear() * 12 + now.getMonth();
  const expYM = exp.getFullYear() * 12 + exp.getMonth();
  return currentYM >= expYM;
};

const canPerpanjang = (m: any) => {
  if (!m) return false;
  // 1. Tagihan harus sudah selesai / lunas
  if (m.status !== 'lunas') return false;
  // 2. Harus sudah masuk ke bulan habisnya (atau sudah kadaluarsa)
  return isBulanHabis(m.tanggal_expired);
};

const getPerpanjangTooltip = (m: any) => {
  if (!m) return '';
  if (m.status !== 'lunas') {
    return 'Tagihan sebelumnya belum selesai / lunas';
  }
  if (!isBulanHabis(m.tanggal_expired)) {
    return `Belum masuk bulan habis (aktif s/d ${formatTanggal(m.tanggal_expired)})`;
  }
  return 'Perpanjang langganan iuran (+1 bulan Rp 150.000)';
};

const memberAktifCount = computed(() => {
  return allMembersList.value.filter(m => !isMemberExpired(m)).length;
});

const filteredMemberList = computed(() => {
  if (!searchQuery.value.trim()) return allMembersList.value;
  const q = searchQuery.value.toLowerCase().trim();
  return allMembersList.value.filter((m: any) => {
    const nama = (m.nama_member || m.nama || '').toLowerCase();
    const kode = (m.kode_member || '').toLowerCase();
    const perus = (m.nama_perusahaan || m.perusahaan || '').toLowerCase();
    return nama.includes(q) || kode.includes(q) || perus.includes(q);
  });
});

const openPerpanjangModal = (member: any) => {
  if (!canPerpanjang(member)) {
    alert(getPerpanjangTooltip(member));
    return;
  }
  selectedMember.value = member;
  perpanjangBayar.value = TARIF_IURAN;
  showPerpanjangModal.value = true;
};

const confirmPerpanjang = async () => {
  if (!selectedMember.value || !canPerpanjang(selectedMember.value)) {
    alert('Member ini belum dapat diperpanjang.');
    return;
  }
  if (isPerpanjangKurang.value) {
    alert('Uang pembayaran tunai masih kurang!');
    return;
  }
  loadingPerpanjang.value = true;
  try {
    const res = await $api.post(`/member/${selectedMember.value.id}/perpanjang`, {
      total_harga: TARIF_IURAN,
      jumlah_bayar: Number(perpanjangBayar.value),
    });
    if (res.data?.success) {
      localStorage.setItem('member_updated_at', String(Date.now()));
      alert('Iuran member berhasil diperpanjang 1 bulan!');
      showPerpanjangModal.value = false;
      await fetchMembers();
    } else {
      alert(res.data?.message || 'Gagal memperpanjang member.');
    }
  } catch (error: any) {
    console.error('Failed to extend member:', error);
    alert(error?.response?.data?.message || 'Gagal memperpanjang member.');
  } finally {
    loadingPerpanjang.value = false;
  }
};

const formatTanggal = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const fetchMembers = async () => {
  try {
    const res = await $api.get('/member');
    if (res.data?.success) {
      allMembersList.value = res.data.data.data || res.data.data || [];
    }
  } catch (error) {
    console.error('Failed to fetch members:', error);
  }
};

const fetchParkir = async () => {
  try {
    const res = await $api.get('/parkir');
    if (res.data?.success) {
      parkirList.value = res.data.data || [];
    }
  } catch (error) {
    console.error('Failed to fetch parkir:', error);
  }
};

const refreshAll = () => {
  fetchMembers();
  fetchParkir();
};

const formatRupiah = (amount: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
};

const formatRupiahSimple = (amount: number) => {
  return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const formatWaktu = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' });
};

const printStrukMember = async (m: any) => {
  const nomorStruk = `TRX-MBR-${String(m.id).padStart(4, '0')}`;
  const tanggalBayar = m.tanggal_bayar 
    ? new Date(m.tanggal_bayar).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
    : new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
  const periodeMulai = m.tanggal_mulai ? new Date(m.tanggal_mulai).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-';
  const periodeExpired = m.tanggal_expired ? new Date(m.tanggal_expired).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-';
  
  const totalHarga = Number(m.total_harga) || 150000;
  const jumlahBayar = Number(m.jumlah_bayar) || totalHarga;
  const kembalian = Number(m.kembalian !== undefined && m.kembalian !== null ? m.kembalian : (jumlahBayar > totalHarga ? jumlahBayar - totalHarga : 0));

  const qrData = `PARKIR PLAZA ANDALAS
BUKTI LUNAS MEMBER
Struk: ${nomorStruk}
Member: ${m.kode_member} - ${m.nama_member || m.nama}
Total: Rp ${formatRupiahSimple(totalHarga)}
Dibayar: Rp ${formatRupiahSimple(jumlahBayar)}
Kembalian: Rp ${formatRupiahSimple(kembalian)}
Status: LUNAS`;

  const qrImage = await QrCode.toDataURL(qrData);

  const pdf = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: [80, 165],
  });

  // Header Lokasi
  pdf.setFont('helvetica', 'bold');
  pdf.setFontSize(10);
  pdf.text('E-PARKING PLAZA ANDALAS', 40, 9, { align: 'center' });
  
  pdf.setFont('helvetica', 'normal');
  pdf.setFontSize(7);
  pdf.text('Jl. Dr KRT Radjiman Widyodiningrat, Jakarta Timur', 40, 13, { align: 'center' });
  pdf.text('Telp: (021) 860-1234 / Operasional 24 Jam', 40, 17, { align: 'center' });

  // Garis Pemisah
  pdf.setLineDashPattern([1, 1], 0);
  pdf.line(5, 20, 75, 20);

  // Judul Nota
  pdf.setFont('helvetica', 'bold');
  pdf.setFontSize(8.5);
  pdf.text('STRUK BUKTI PELUNASAN MEMBER', 40, 25, { align: 'center' });

  pdf.setFont('helvetica', 'normal');
  pdf.setFontSize(7);
  pdf.text(`No. Bukti : ${nomorStruk}`, 5, 31);
  pdf.text(`Waktu     : ${tanggalBayar}`, 5, 35);
  pdf.text(`Petugas   : ${user?.name || 'Kasir Lapangan'}`, 5, 39);

  pdf.line(5, 42, 75, 42);

  // Data Member
  pdf.setFont('helvetica', 'bold');
  pdf.setFontSize(7.5);
  pdf.text('IDENTITAS MEMBER AKTIF', 5, 47);

  pdf.setFont('helvetica', 'normal');
  pdf.setFontSize(7);
  pdf.text(`Kode Member : ${m.kode_member}`, 5, 52);
  pdf.text(`Nama Member : ${m.nama_member || m.nama || '-'}`, 5, 56);
  pdf.text(`Perusahaan  : ${m.nama_perusahaan || m.perusahaan || '-'}`, 5, 60);
  pdf.text(`Paket       : Iuran Parkir Bulanan (RFID)`, 5, 64);
  pdf.text(`Masa Aktif  : ${periodeMulai} s/d ${periodeExpired}`, 5, 68);

  pdf.line(5, 71, 75, 71);

  // Rincian Finansial
  pdf.setFont('helvetica', 'bold');
  pdf.setFontSize(7.5);
  pdf.text('RINCIAN PEMBAYARAN', 5, 76);

  pdf.setFont('helvetica', 'normal');
  pdf.setFontSize(7);
  pdf.text('Total Iuran Bulanan', 5, 81);
  pdf.text(`Rp ${formatRupiahSimple(totalHarga)}`, 75, 81, { align: 'right' });

  pdf.text('Jumlah Dibayar (Tunai)', 5, 86);
  pdf.text(`Rp ${formatRupiahSimple(jumlahBayar)}`, 75, 86, { align: 'right' });

  pdf.text('Uang Kembalian', 5, 91);
  pdf.text(`Rp ${formatRupiahSimple(kembalian)}`, 75, 91, { align: 'right' });

  pdf.line(5, 95, 75, 95);

  // Status Lunas Box
  pdf.setFont('helvetica', 'bold');
  pdf.setFontSize(9);
  pdf.text('STATUS: LUNAS (PAID)', 40, 101, { align: 'center' });

  // QR Code
  pdf.addImage(qrImage, 'PNG', 28, 105, 24, 24);

  // Footer Pesan
  pdf.setFont('helvetica', 'italic');
  pdf.setFontSize(6.5);
  pdf.text('Simpan struk ini sebagai bukti pembayaran sah.', 40, 135, { align: 'center' });
  pdf.text('Kartu RFID member aktif otomatis di gerbang masuk.', 40, 139, { align: 'center' });
  pdf.text('Terima kasih atas kerja sama Anda.', 40, 143, { align: 'center' });

  pdf.save(`Struk-Member-${m.kode_member}.pdf`);
};

onMounted(() => {
  refreshAll();
});
</script>

