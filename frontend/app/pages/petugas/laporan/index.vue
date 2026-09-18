<template>
  <div class="h-screen bg-[#f8fafc] flex flex-col font-sans overflow-hidden">
    <!-- Navbar -->
    <div class="no-print">
      <LayoutNavbar @toggleSidebar="isSidebarOpen = !isSidebarOpen" />
    </div>

    <div class="flex-1 flex overflow-hidden relative">
      <!-- Sidebar -->
      <div class="no-print">
        <LayoutSidebar :isOpen="isSidebarOpen" />
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>
      </div>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7">
        <div class="max-w-[1400px] mx-auto space-y-6">

          <!-- Header & Actions -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
              <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Laporan & Rekapitulasi</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  <FileSpreadsheet class="w-3.5 h-3.5" />
                  Audit Operasional
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                <NuxtLink to="/petugas" class="hover:text-emerald-600 transition flex items-center gap-1">
                  <span>Dashboard</span>
                </NuxtLink>
                <span class="text-slate-300">/</span>
                <span class="text-emerald-700 font-semibold">Rekap Pendapatan & Iuran</span>
              </p>
            </div>

            <div class="flex items-center gap-2 no-print">
              <button 
                @click="printReport" 
                class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm hover:shadow flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2"
              >
                <Printer class="w-4 h-4" />
                <span>Cetak Laporan Resmi</span>
              </button>
            </div>
          </div>

          <!-- Financial Summary Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100 shrink-0">
                <CreditCard class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Penerimaan Member</p>
                <h3 class="text-xl font-black text-slate-900 tracking-tight mt-0.5">{{ totalTagihanFormatted }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ filteredMembers.length }} member terdata</p>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                <Car class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Penerimaan Parkir Harian</p>
                <h3 class="text-xl font-black text-slate-900 tracking-tight mt-0.5">{{ totalPendapatanFormatted }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ filteredParkir.length }} tiket parkir</p>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shrink-0">
                <TrendingUp class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Akumulasi Kas</p>
                <h3 class="text-xl font-black text-emerald-600 tracking-tight mt-0.5">
                  Rp {{ formatRupiah(totalTagihanMember + totalPendapatanParkir) }}
                </h3>
                <p class="text-[11px] text-emerald-700/80 font-medium mt-0.5">Gabungan pendapatan aktif</p>
              </div>
            </div>
          </div>

          <!-- Filter Toolbar -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap gap-4 items-end no-print">
            <div class="w-full sm:w-auto">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-slate-400" />
                Dari Tanggal
              </label>
              <input 
                type="date" 
                v-model="filter.dari" 
                class="w-full sm:w-auto border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 font-medium" 
              />
            </div>

            <div class="w-full sm:w-auto">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-slate-400" />
                Sampai Tanggal
              </label>
              <input 
                type="date" 
                v-model="filter.sampai" 
                class="w-full sm:w-auto border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 font-medium" 
              />
            </div>

            <div class="w-full sm:w-44">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                <Filter class="w-3.5 h-3.5 text-slate-400" />
                Status Pelunasan
              </label>
              <select 
                v-model="filter.status" 
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 font-medium text-slate-700"
              >
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="belum lunas">Belum Lunas</option>
              </select>
            </div>

            <div class="flex-1 min-w-[220px]">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                <Search class="w-3.5 h-3.5 text-slate-400" />
                Pencarian Kata Kunci
              </label>
              <input 
                type="text" 
                v-model="filter.search" 
                placeholder="Cari nama atau kode tiket..." 
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 font-medium" 
              />
            </div>

            <button 
              @click="fetchData" 
              class="w-full sm:w-auto px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer flex items-center justify-center gap-2"
            >
              <Filter class="w-3.5 h-3.5" />
              <span>Filter Data</span>
            </button>
          </div>

          <!-- Report Tabs -->
          <div class="flex flex-wrap gap-2 p-1.5 bg-slate-200/60 rounded-2xl w-fit no-print">
            <button 
              @click="activeTab = 'member'" 
              class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer" 
              :class="activeTab === 'member' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              <CreditCard class="w-3.5 h-3.5" />
              <span>Laporan Iuran Member</span>
              <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="activeTab === 'member' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300 text-slate-700'">
                {{ filteredMembers.length }}
              </span>
            </button>
            <button 
              @click="activeTab = 'member_parkir'" 
              class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer" 
              :class="activeTab === 'member_parkir' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              <Users class="w-3.5 h-3.5" />
              <span>Keluar Masuk Member</span>
              <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="activeTab === 'member_parkir' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300 text-slate-700'">
                {{ filteredMemberParkir.length }}
              </span>
            </button>
            <button 
              @click="activeTab = 'transaksi'" 
              class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer" 
              :class="activeTab === 'transaksi' ? 'bg-white text-emerald-800 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
              <Car class="w-3.5 h-3.5" />
              <span>Laporan Tiket Harian</span>
              <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="activeTab === 'transaksi' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300 text-slate-700'">
                {{ filteredParkir.length }}
              </span>
            </button>
          </div>

          <!-- Tab 1: Member Iuran Table -->
          <div v-if="activeTab === 'member'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
              <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                  <CreditCard class="w-4 h-4 text-emerald-600" />
                  Rekapitulasi Iuran Member Langganan
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Status kewajiban tagihan berkala anggota parkir Plaza Andalas</p>
              </div>
              <span class="text-xs font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                Penerimaan: <b class="text-emerald-700 font-black">{{ totalTagihanFormatted }}</b>
              </span>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                  <tr>
                    <th class="px-5 py-3.5 w-14 text-center">No</th>
                    <th class="px-5 py-3.5">Kode Member</th>
                    <th class="px-5 py-3.5">Nama Lengkap</th>
                    <th class="px-5 py-3.5">Perusahaan / Instansi</th>
                    <th class="px-5 py-3.5 text-right">Jumlah Tagihan</th>
                    <th class="px-5 py-3.5 text-right">Telah Dibayar</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5">Masa Berlaku</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(m, index) in filteredMembers" :key="m.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 text-center font-mono text-slate-400">{{ index + 1 }}</td>
                    <td class="px-5 py-3.5">
                      <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono font-bold text-slate-800">
                        {{ m.kode_member }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ m.nama_member || m.nama }}</td>
                    <td class="px-5 py-3.5 text-slate-500">{{ m.nama_perusahaan || m.perusahaan || '-' }}</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-700 text-right">Rp {{ formatRupiah(m.total_harga ?? m.jumlah_tagihan) }}</td>
                    <td class="px-5 py-3.5 font-black text-emerald-600 text-right">Rp {{ formatRupiah(m.jumlah_bayar) }}</td>
                    <td class="px-5 py-3.5 text-center">
                      <span 
                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" 
                        :class="(m.status === 'Lunas' || m.status === 'lunas') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                      >
                        {{ m.status }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">{{ formatTanggal(m.tanggal_expired) }}</td>
                  </tr>
                  <tr v-if="filteredMembers.length === 0">
                    <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                      <CreditCard class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                      <p class="font-medium text-slate-600">Tidak ada data laporan member yang sesuai.</p>
                      <p class="text-[11px] text-slate-400 mt-0.5">Ubah pengaturan filter tanggal atau kata kunci.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tab 2: Log Keluar Masuk Member -->
          <div v-if="activeTab === 'member_parkir'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
              <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                  <Users class="w-4 h-4 text-emerald-600" />
                  Rekapitulasi Keluar Masuk Member
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan pergerakan kendaraan member beserta plat nomor yang dicatat saat keluar</p>
              </div>
              <span class="text-xs font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                Total Log: <b class="text-emerald-700 font-black">{{ filteredMemberParkir.length }} Sesi</b>
              </span>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                  <tr>
                    <th class="px-5 py-3.5 w-14 text-center">No</th>
                    <th class="px-5 py-3.5">Kode Member</th>
                    <th class="px-5 py-3.5">Nama Member</th>
                    <th class="px-5 py-3.5">Perusahaan</th>
                    <th class="px-5 py-3.5">Plat Nomor</th>
                    <th class="px-5 py-3.5">Waktu Masuk</th>
                    <th class="px-5 py-3.5">Waktu Keluar</th>
                    <th class="px-5 py-3.5">Durasi</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(p, index) in filteredMemberParkir" :key="p.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 text-center font-mono text-slate-400">{{ index + 1 }}</td>
                    <td class="px-5 py-3.5">
                      <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-800 border border-purple-200 font-mono font-bold">
                        {{ p.kode_tiket }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ p.nama }}</td>
                    <td class="px-5 py-3.5 text-slate-500">{{ p.nama_perusahaan || '-' }}</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-slate-700 uppercase">
                      <!-- Plat nomor HANYA tercatat saat sudah keluar -->
                      {{ (p.status === 'selesai' && p.waktu_keluar && p.waktu_keluar !== '-') ? (p.plat_nomor || '-') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">{{ formatWaktu(p.waktu_masuk || p.created_at) }}</td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">{{ formatWaktu(p.waktu_keluar) }}</td>
                    <td class="px-5 py-3.5 text-slate-700 font-medium">{{ p.durasi }}</td>
                    <td class="px-5 py-3.5 text-center">
                      <span 
                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" 
                        :class="(p.waktu_keluar || p.status === 'selesai') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                      >
                        {{ (p.waktu_keluar || p.status === 'selesai') ? 'Selesai' : 'Aktif' }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="filteredMemberParkir.length === 0">
                    <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                      <Users class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                      <p class="font-medium text-slate-600">Tidak ada riwayat keluar masuk member yang sesuai.</p>
                      <p class="text-[11px] text-slate-400 mt-0.5">Ubah pengaturan filter tanggal atau kata kunci.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tab 2: Transaksi Table -->
          <div v-if="activeTab === 'transaksi'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
              <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                  <Car class="w-4 h-4 text-emerald-600" />
                  Rekapitulasi Tiket Parkir Harian
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Rincian kendaraan pengunjung beserta durasi dan tarif terbayar</p>
              </div>
              <span class="text-xs font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                Penerimaan: <b class="text-emerald-700 font-black">{{ totalPendapatanFormatted }}</b>
              </span>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                  <tr>
                    <th class="px-5 py-3.5 w-14 text-center">No</th>
                    <th class="px-5 py-3.5">Kode Tiket</th>
                    <th class="px-5 py-3.5">Plat Nomor</th>
                    <th class="px-5 py-3.5">Jenis Kendaraan</th>
                    <th class="px-5 py-3.5">Waktu Masuk</th>
                    <th class="px-5 py-3.5">Waktu Keluar</th>
                    <th class="px-5 py-3.5 text-right">Tarif Parkir</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(p, index) in filteredParkir" :key="p.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 text-center font-mono text-slate-400">{{ index + 1 }}</td>
                    <td class="px-5 py-3.5">
                      <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono font-bold text-slate-800">
                        {{ p.kode_tiket || ('TRX-' + String(p.id).padStart(4, '0')) }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 font-mono font-bold text-slate-700 uppercase">
                      {{ (p.status === 'selesai' && p.waktu_keluar && p.waktu_keluar !== '-') ? (p.plat_nomor || '-') : '-' }}
                    </td>
                    <td class="px-5 py-3.5 capitalize text-slate-600 font-medium">{{ p.jenis_kendaraan || 'Mobil' }}</td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">{{ formatWaktu(p.waktu_masuk || p.created_at) }}</td>
                    <td class="px-5 py-3.5 text-slate-600 font-mono">{{ formatWaktu(p.waktu_keluar) }}</td>
                    <td class="px-5 py-3.5 font-bold text-slate-800 text-right">Rp {{ formatRupiah(p.total_biaya || p.biaya || 0) }}</td>
                    <td class="px-5 py-3.5 text-center">
                      <span 
                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" 
                        :class="(p.waktu_keluar || p.status === 'selesai') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                      >
                        {{ p.waktu_keluar || p.status === 'selesai' ? 'Selesai' : 'Aktif' }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="filteredParkir.length === 0">
                    <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                      <Car class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                      <p class="font-medium text-slate-600">Tidak ada data laporan transaksi yang sesuai.</p>
                      <p class="text-[11px] text-slate-400 mt-0.5">Ubah pengaturan filter tanggal atau kata kunci.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { 
  FileSpreadsheet, Printer, Calendar, Filter, Search, 
  CreditCard, Car, TrendingUp, Users 
} from 'lucide-vue-next';

const { $api } = useNuxtApp();
const isSidebarOpen = ref(false);
const activeTab = ref<'member' | 'member_parkir' | 'transaksi'>('member');

const members = ref<any[]>([]);
const parkirList = ref<any[]>([]);

const filter = reactive({
  dari: '',
  sampai: '',
  status: '',
  search: ''
});

const fetchData = async () => {
  try {
    const resM = await $api.get('/member');
    if (resM.data?.success) {
      members.value = (resM.data.data.data || resM.data.data || []).map((m: any) => ({
        ...m,
        jumlah_tagihan: m.total_harga ?? m.tagihan ?? 150000,
        jumlah_bayar: m.jumlah_bayar ?? m.dibayar ?? 0,
        status: m.status === 'lunas' ? 'Lunas' : 'Belum Lunas'
      }));
    }
  } catch (err) {
    console.error('Error fetching member report:', err);
  }

  try {
    const resP = await $api.get('/parkir');
    if (resP.data?.success) {
      parkirList.value = resP.data.data || [];
    }
  } catch (err) {
    console.error('Error fetching parkir report:', err);
  }
};

const filteredMembers = computed(() => {
  return members.value.filter(m => {
    let match = true;
    if (filter.status) {
      match = match && m.status.toLowerCase() === filter.status.toLowerCase();
    }
    if (filter.dari) {
      const tgl = (m.tanggal_mulai || m.created_at || '').slice(0, 10);
      if (tgl) match = match && tgl >= filter.dari;
    }
    if (filter.sampai) {
      const tgl = (m.tanggal_mulai || m.created_at || '').slice(0, 10);
      if (tgl) match = match && tgl <= filter.sampai;
    }
    if (filter.search) {
      const q = filter.search.toLowerCase();
      match = match && (
        ((m.nama_member || m.nama || '').toLowerCase().includes(q)) || 
        ((m.kode_member || '').toLowerCase().includes(q)) ||
        ((m.nama_perusahaan || m.perusahaan || '').toLowerCase().includes(q))
      );
    }
    return match;
  });
});

// Rekapitulasi Keluar Masuk Member
const filteredMemberParkir = computed(() => {
  return parkirList.value.filter(p => {
    if (p.tipe !== 'member') return false;
    let match = true;
    if (filter.status) {
      const pStatus = (p.status === 'selesai' || p.waktu_keluar) ? 'lunas' : 'belum lunas';
      // Atau cocokkan dengan kata selesai / parkir
      match = match && (p.status.toLowerCase() === filter.status.toLowerCase() || pStatus === filter.status.toLowerCase());
    }
    if (filter.dari) {
      const tgl = (p.waktu_masuk || p.created_at || '').slice(0, 10);
      if (tgl) match = match && tgl >= filter.dari;
    }
    if (filter.sampai) {
      const tgl = (p.waktu_masuk || p.created_at || '').slice(0, 10);
      if (tgl) match = match && tgl <= filter.sampai;
    }
    if (filter.search) {
      const q = filter.search.toLowerCase();
      match = match && Boolean(
        (p.kode_tiket && p.kode_tiket.toLowerCase().includes(q)) ||
        (p.nama && p.nama.toLowerCase().includes(q)) ||
        (p.nama_perusahaan && p.nama_perusahaan.toLowerCase().includes(q)) ||
        (p.plat_nomor && p.plat_nomor.toLowerCase().includes(q))
      );
    }
    return match;
  });
});

// Rekapitulasi Tiket Harian Non-Member
const filteredParkir = computed(() => {
  return parkirList.value.filter(p => {
    if (p.tipe === 'member') return false;
    let match = true;
    if (filter.dari) {
      const tgl = (p.waktu_masuk || p.created_at || '').slice(0, 10);
      if (tgl) match = match && tgl >= filter.dari;
    }
    if (filter.sampai) {
      const tgl = (p.waktu_masuk || p.created_at || '').slice(0, 10);
      if (tgl) match = match && tgl <= filter.sampai;
    }
    if (filter.search) {
      const q = filter.search.toLowerCase();
      match = match && Boolean(
        (p.kode_tiket && p.kode_tiket.toLowerCase().includes(q)) ||
        (p.plat_nomor && p.plat_nomor.toLowerCase().includes(q))
      );
    }
    return match;
  });
});

const totalTagihanMember = computed(() => {
  return filteredMembers.value.reduce((acc, m) => acc + (Number(m.jumlah_bayar) || 0), 0);
});

const totalPendapatanParkir = computed(() => {
  return filteredParkir.value.reduce((acc, p) => acc + (Number(p.total_biaya || p.biaya) || 0), 0);
});

const totalTagihanFormatted = computed(() => {
  return 'Rp ' + formatRupiah(totalTagihanMember.value);
});

const totalPendapatanFormatted = computed(() => {
  return 'Rp ' + formatRupiah(totalPendapatanParkir.value);
});

const printReport = () => {
  window.print();
};

const formatRupiah = (val: any) => {
  return new Intl.NumberFormat('id-ID').format(Number(val || 0));
};

const formatTanggal = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatWaktu = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' });
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
  body {
    background-color: white !important;
  }
}
</style>

