<template>
  <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
    <!-- Table Header & Controls -->
    <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-600 flex items-center justify-center">
          <Receipt class="w-5 h-5" />
        </div>
        <div>
          <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-tight">
            Aktivitas Parkir & Transaksi Terkini
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">Pemantauan data kendaraan masuk dan keluar secara real-time</p>
        </div>
      </div>

      <div class="flex items-center gap-2.5 flex-wrap">
        <!-- Search Input -->
        <div class="relative">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari tiket atau plat..."
            class="pl-9 pr-3.5 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 w-56 font-medium text-slate-800 transition"
          />
          <Search class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
        </div>

        <!-- Filter Status -->
        <select 
          v-model="selectedStatus" 
          class="px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-700 font-semibold cursor-pointer transition"
        >
          <option value="">Semua Status</option>
          <option value="selesai">Selesai (Keluar)</option>
          <option value="parkir">Aktif Parkir</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
          <tr>
            <th class="px-4 py-3 text-center w-12">No</th>
            <th class="px-4 py-3">Kode Tiket</th>
            <th class="px-4 py-3">Plat Nomor</th>
            <th class="px-4 py-3">Jenis</th>
            <th class="px-4 py-3">Waktu Masuk</th>
            <th class="px-4 py-3">Waktu Keluar</th>
            <th class="px-4 py-3">Durasi</th>
            <th class="px-4 py-3">Biaya Tarif</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
          <tr 
            v-for="(item, index) in paginatedList" 
            :key="item.id || index"
            class="hover:bg-slate-50/80 transition-colors"
          >
            <td class="px-4 py-3.5 text-center text-slate-400 font-mono">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
            <td class="px-4 py-3.5 font-mono font-bold text-slate-900">
              <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 text-xs">
                {{ item.kode_tiket }}
              </span>
            </td>
            <td class="px-4 py-3.5 font-mono font-bold text-slate-800 uppercase">
              <!-- Plat nomor HANYA tercatat saat sudah keluar -->
              {{ (item.status === 'selesai' && item.waktu_keluar && item.waktu_keluar !== '-') ? (item.plat_nomor || '-') : '-' }}
            </td>
            <td class="px-4 py-3.5 text-slate-600 capitalize">
              <span class="inline-flex items-center gap-1.5 font-medium">
                <Car v-if="item.jenis_kendaraan === 'mobil'" class="w-4 h-4 text-sky-600" />
                <Bike v-else class="w-4 h-4 text-emerald-600" />
                <span>{{ item.jenis_kendaraan || 'Motor' }}</span>
              </span>
            </td>
            <td class="px-4 py-3.5 text-slate-500 font-mono">{{ item.waktu_masuk || '-' }}</td>
            <td class="px-4 py-3.5 text-slate-500 font-mono">{{ item.waktu_keluar || '-' }}</td>
            <td class="px-4 py-3.5 text-slate-600 font-medium">{{ item.durasi || '-' }}</td>
            <td class="px-4 py-3.5 font-black text-slate-900 font-mono">Rp {{ formatRupiah(item.biaya) }}</td>
            <td class="px-4 py-3.5 text-center">
              <span 
                :class="[
                  'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1 border',
                  item.status === 'selesai' 
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                    : 'bg-amber-50 text-amber-700 border-amber-200'
                ]"
              >
                <span :class="['w-1.5 h-1.5 rounded-full', item.status === 'selesai' ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse']"></span>
                <span>{{ item.status === 'selesai' ? 'Selesai' : 'Parkir' }}</span>
              </span>
            </td>
            <td class="px-4 py-3.5 text-center">
              <button 
                @click="openDetail(item)" 
                class="px-3 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 hover:text-emerald-700 border border-slate-200 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer"
                title="Lihat Detail Transaksi"
              >
                <Eye class="w-3.5 h-3.5" />
                <span>Rincian</span>
              </button>
            </td>
          </tr>
          <tr v-if="filteredList.length === 0">
            <td colspan="10" class="px-4 py-12 text-center text-slate-400">
              <p class="font-medium text-slate-600">Tidak ada data transaksi yang cocok dengan pencarian.</p>
              <p class="text-xs text-slate-400 mt-1">Periksa kembali kata kunci atau ubah filter status.</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div class="px-5 py-3.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50 text-xs text-slate-500">
      <div>
        Menampilkan <strong>{{ filteredList.length > 0 ? (currentPage - 1) * pageSize + 1 : 0 }}</strong> sampai 
        <strong>{{ Math.min(currentPage * pageSize, filteredList.length) }}</strong> dari 
        <strong>{{ filteredList.length }}</strong> transaksi
      </div>
      <div class="flex items-center gap-1.5">
        <button 
          :disabled="currentPage <= 1"
          @click="currentPage--"
          class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 text-xs font-bold transition cursor-pointer"
        >
          Sebelumnya
        </button>
        <span class="px-3 py-1.5 font-bold font-mono text-slate-700 bg-slate-100 rounded-lg border border-slate-200">
          {{ currentPage }} / {{ totalPages || 1 }}
        </span>
        <button 
          :disabled="currentPage >= totalPages"
          @click="currentPage++"
          class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 text-xs font-bold transition cursor-pointer"
        >
          Selanjutnya
        </button>
      </div>
    </div>

    <!-- Modal Detail Transaksi -->
    <Teleport to="body">
      <div 
        v-if="selectedItem" 
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-in fade-in duration-150"
        @click.self="selectedItem = null"
      >
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-slate-100">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center">
                <Receipt class="w-4 h-4" />
              </div>
              <div>
                <h4 class="font-bold text-slate-900 text-base leading-tight">Detail Transaksi Parkir</h4>
                <p class="text-xs text-slate-400">Informasi log gate tiket</p>
              </div>
            </div>
            <button 
              @click="selectedItem = null" 
              class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
            >
              ✕
            </button>
          </div>

          <div class="space-y-3 text-xs text-slate-600 mb-6 bg-slate-50/70 p-4 rounded-xl border border-slate-200/80">
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-400 font-medium">Kode Tiket</span>
              <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">
                {{ selectedItem.kode_tiket }}
              </span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-400 font-medium">Plat Nomor</span>
              <span class="font-mono font-bold text-slate-800 uppercase">
                {{ (selectedItem.status === 'selesai' && selectedItem.waktu_keluar && selectedItem.waktu_keluar !== '-') ? (selectedItem.plat_nomor || '-') : '-' }}
              </span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-400 font-medium">Jenis Kendaraan</span>
              <span class="capitalize font-semibold text-slate-800">{{ selectedItem.jenis_kendaraan || 'Mobil' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-400 font-medium">Waktu Masuk</span>
              <span class="font-mono text-slate-800">{{ selectedItem.waktu_masuk || '-' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-400 font-medium">Waktu Keluar</span>
              <span class="font-mono text-slate-800">{{ selectedItem.waktu_keluar || '-' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-400 font-medium">Durasi Parkir</span>
              <span class="font-semibold text-slate-800">{{ selectedItem.durasi || '-' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60 items-center">
              <span class="text-slate-400 font-medium">Total Tarif Biaya</span>
              <span class="font-black text-base text-emerald-700 font-mono">
                {{ selectedItem.tipe === 'member' ? 'Rp 0 (Iuran)' : 'Rp ' + formatRupiah(selectedItem.biaya) }}
              </span>
            </div>
            <div class="flex justify-between py-1 items-center">
              <span class="text-slate-400 font-medium">Status Operasional</span>
              <span 
                class="font-bold uppercase text-[10px] px-2 py-0.5 rounded-full border"
                :class="selectedItem.status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
              >
                {{ selectedItem.status === 'selesai' ? 'Selesai' : 'Aktif Parkir' }}
              </span>
            </div>
          </div>

          <button 
            @click="selectedItem = null" 
            class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
          >
            Tutup Rincian
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { 
  Receipt, 
  Search, 
  Eye, 
  Car, 
  Bike 
} from 'lucide-vue-next';

interface Transaction {
  id?: number | string;
  kode_tiket: string;
  plat_nomor?: string;
  tipe?: string;
  nama?: string;
  jenis_kendaraan?: string;
  waktu_masuk?: string;
  waktu_keluar?: string;
  durasi?: string;
  biaya: number;
  status: 'selesai' | 'parkir' | string;
}

const props = defineProps<{
  transactions: Transaction[];
}>();

const searchQuery = ref('');
const selectedStatus = ref('');
const currentPage = ref(1);
const pageSize = 8;
const selectedItem = ref<Transaction | null>(null);

const filteredList = computed(() => {
  return props.transactions.filter(item => {
    const q = searchQuery.value.toLowerCase().trim();
    const matchSearch = !q || 
      item.kode_tiket.toLowerCase().includes(q) ||
      Boolean(item.plat_nomor && item.plat_nomor.toLowerCase().includes(q)) ||
      Boolean(item.nama && item.nama.toLowerCase().includes(q));
    const matchStatus = !selectedStatus.value || item.status === selectedStatus.value;
    return matchSearch && matchStatus;
  });
});

const totalPages = computed(() => Math.ceil(filteredList.value.length / pageSize));

const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredList.value.slice(start, start + pageSize);
});

const openDetail = (item: Transaction) => {
  selectedItem.value = item;
};

const formatRupiah = (val: number | string) => {
  return Number(val || 0).toLocaleString('id-ID');
};
</script>
