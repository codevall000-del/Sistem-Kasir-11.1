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

          <!-- Page Header & Actions -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
              <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Data Member Parkir</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  <IdCard class="w-3.5 h-3.5" />
                  Keanggotaan Tetap
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                <NuxtLink to="/petugas" class="hover:text-emerald-600 transition">Dashboard</NuxtLink>
                <span class="text-slate-300">/</span>
                <span class="text-emerald-700 font-semibold">Direktori & Cetak Kartu</span>
              </p>
            </div>

            <div class="flex items-center gap-2">
              <NuxtLink 
                to="/petugas/member/tambah" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm hover:shadow flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
              >
                <UserPlus class="w-4 h-4" />
                <span>Tambah Member Baru</span>
              </NuxtLink>
            </div>
          </div>

          <!-- Quick Stats Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100 shrink-0">
                <Users class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Member</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ members.length }} <span class="text-xs font-medium text-slate-500">Orang</span></h3>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                <CheckCircle2 class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Lunas</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ lunasCount }} <span class="text-xs font-medium text-slate-500">Orang</span></h3>
              </div>
            </div>
          </div>

          <!-- Main Table Card -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <!-- Table Header Filters -->
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
              <div>
                <h2 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                  <Users class="w-4 h-4 text-emerald-600" />
                  Direktori Member Terdaftar
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar pengguna pemegang kartu RFID langganan Plaza Andalas</p>
              </div>

              <div class="flex items-center gap-3">
                <div class="relative w-full sm:w-60">
                  <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Cari nama atau kode..." 
                    class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-medium transition"
                  />
                </div>

                <select 
                  v-model="statusFilter" 
                  class="px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-700 font-medium transition"
                >
                  <option value="">Semua Status</option>
                  <option value="lunas">Lunas</option>
                  <option value="belum_lunas">Belum Lunas</option>
                </select>

                <button 
                  type="button" 
                  @click="fetchMembers" 
                  class="p-2 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-xl transition cursor-pointer"
                  title="Segarkan Data"
                >
                  <RefreshCw class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                  <tr>
                    <th class="px-5 py-3.5">Kode Member</th>
                    <th class="px-5 py-3.5">Nama Member</th>
                    <th class="px-5 py-3.5">Perusahaan / Unit</th>
                    <th class="px-5 py-3.5 text-right">Tagihan</th>
                    <th class="px-5 py-3.5 text-right">Dibayar</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5">Masa Berlaku</th>
                    <th class="px-5 py-3.5 text-center w-32">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr 
                    v-for="member in filteredMembers" 
                    :key="member.id" 
                    class="hover:bg-slate-50/80 transition-colors"
                  >
                    <td class="px-5 py-3.5">
                      <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 font-mono font-bold text-xs text-slate-800">
                        {{ member.kode_member }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ member.nama_member || member.nama }}</td>
                    <td class="px-5 py-3.5 text-slate-500">{{ member.nama_perusahaan || member.perusahaan || '-' }}</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-800 text-right">{{ formatRupiah(member.total_harga ?? member.tagihan) }}</td>
                    <td class="px-5 py-3.5 text-slate-500 text-right">{{ formatRupiah(member.jumlah_bayar ?? member.dibayar) }}</td>
                    <td class="px-5 py-3.5 text-center">
                      <span 
                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" 
                        :class="member.status === 'lunas' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                      >
                        {{ member.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">{{ formatTanggal(member.tanggal_expired) }}</td>
                    <td class="px-5 py-3.5 text-center">
                      <div class="flex items-center justify-center gap-1.5">
                        <!-- Detail / Cetak Kartu -->
                        <button 
                          @click="showDetail(member.id)" 
                          class="p-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition cursor-pointer" 
                          title="Lihat & Cetak Kartu"
                        >
                          <IdCard class="w-3.5 h-3.5" />
                        </button>
                        <!-- Edit Profil -->
                        <NuxtLink 
                          :to="`/petugas/member/edit/${member.id}`" 
                          class="p-2 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition cursor-pointer" 
                          title="Ubah Profil Data Member"
                        >
                          <Pencil class="w-3.5 h-3.5" />
                        </NuxtLink>

                        <!-- Delete -->
                        <button 
                          v-if="member.status !== 'lunas'"
                          @click="confirmDelete(member)" 
                          class="p-2 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition cursor-pointer" 
                          title="Hapus Member"
                        >
                          <Trash2 class="w-3.5 h-3.5" />
                        </button>
                        <button 
                          v-else
                          disabled 
                          class="p-2 rounded-lg bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed" 
                          title="Member sudah lunas tidak dapat dihapus"
                        >
                          <Trash2 class="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredMembers.length === 0">
                    <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                      <Users class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                      <p class="font-medium text-slate-600">Tidak ada member yang cocok dengan kriteria.</p>
                      <p class="text-[11px] text-slate-400 mt-0.5">Periksa kata kunci pencarian atau status filter.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </main>
    </div>

    <!-- Modal Detail & Cetak Kartu Member -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
          <!-- Header -->
          <div class="px-6 py-5 bg-emerald-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                <IdCard class="w-4 h-4 text-white" />
              </div>
              <div>
                <h3 class="font-bold text-base">Kartu Akses Member</h3>
                <p class="text-[11px] text-emerald-100">Pratinjau fisik kartu identitas parkir</p>
              </div>
            </div>
            <button 
              @click="showModal = false" 
              class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <div v-if="selectedMember" class="p-6 space-y-5">
            <!-- Member Virtual Card Preview -->
            <div class="bg-gradient-to-br from-emerald-700 via-teal-800 to-slate-900 text-white rounded-2xl p-6 shadow-xl relative overflow-hidden border border-emerald-500/40">
              <!-- Glossy Sheen Overlay -->
              <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

              <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                  <p class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-300">PLAZA ANDALAS E-PARKING</p>
                  <h4 class="font-black text-base text-white tracking-tight">ACCESS MEMBER PASS</h4>
                </div>
                <div class="w-8 h-8 rounded-lg bg-white/10 border border-white/20 flex items-center justify-center">
                  <ParkingSquare class="w-5 h-5 text-emerald-300" />
                </div>
              </div>

              <!-- Card Chip & QR Row -->
              <div class="flex items-center gap-4 my-3 relative z-10">
                <div class="p-1.5 bg-white rounded-xl shadow shrink-0">
                  <img 
                    v-if="selectedMember.qr_image" 
                    :src="selectedMember.qr_image" 
                    alt="QR Code" 
                    class="w-20 h-20"
                  />
                  <div v-else class="w-20 h-20 flex items-center justify-center text-slate-400">
                    <QrCode class="w-10 h-10" />
                  </div>
                </div>
                <div class="text-xs space-y-1 min-w-0">
                  <p class="font-mono text-sm font-black tracking-widest text-emerald-300">{{ selectedMember.kode_member }}</p>
                  <p class="font-bold text-white text-base truncate">{{ selectedMember.nama_member || selectedMember.nama }}</p>
                  <p class="text-slate-300 text-xs truncate flex items-center gap-1">
                    <Building2 class="w-3 h-3 text-emerald-400" />
                    {{ selectedMember.nama_perusahaan || selectedMember.perusahaan || 'Personal Member' }}
                  </p>
                </div>
              </div>

              <div class="mt-4 pt-3 border-t border-emerald-600/40 flex justify-between items-center text-[11px] text-slate-300 relative z-10">
                <span>Berlaku s/d: <b class="text-white">{{ formatTanggal(selectedMember.tanggal_expired) }}</b></span>
                <span class="font-bold uppercase px-2 py-0.5 rounded-full text-[9px] tracking-wider bg-emerald-500/30 text-emerald-200 border border-emerald-400/40">
                  {{ selectedMember.status }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-1">
              <button 
                @click="downloadKartu" 
                class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
              >
                <Download class="w-4 h-4" />
                <span>Unduh Kartu (PDF)</span>
              </button>
              <button 
                @click="showModal = false" 
                class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
              >
                Tutup
              </button>
            </div>
          </div>

          <div v-else class="p-12 text-center text-slate-500">
            <div class="w-8 h-8 border-3 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-xs font-semibold">Memuat rincian member...</p>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Hapus Member -->
    <Teleport to="body">
      <div v-if="memberToDelete" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center text-slate-800 border border-slate-200">
          <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-rose-100">
            <Trash2 class="w-6 h-6" />
          </div>
          <h3 class="text-base font-bold mb-1">Hapus Data Member?</h3>
          <p class="text-xs text-slate-500 mb-5 leading-relaxed">
            Apakah Anda yakin ingin menghapus data member <b>{{ memberToDelete.nama_member || memberToDelete.nama }}</b>? Tindakan ini permanen dan tidak dapat dibatalkan.
          </p>
          <div class="flex gap-3">
            <button 
              type="button" 
              @click="memberToDelete = null" 
              class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs hover:bg-slate-200 transition cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="button" 
              @click="executeDelete" 
              class="flex-1 py-2.5 bg-rose-600 text-white font-bold rounded-xl text-xs hover:bg-rose-700 transition shadow-md shadow-rose-600/30 cursor-pointer"
            >
              Ya, Hapus
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { jsPDF } from 'jspdf';
import QRCode from 'qrcode';
import { 
  Users, CheckCircle2, Search, IdCard, 
  Pencil, Trash2, UserPlus, X, Download, ParkingSquare, 
  QrCode, Building2, RefreshCw 
} from 'lucide-vue-next';

const { $api } = useNuxtApp();

const isSidebarOpen = ref(false);
const members = ref<any[]>([]);
const searchQuery = ref('');
const statusFilter = ref('');

const showModal = ref(false);
const selectedMember = ref<any>(null);
const memberToDelete = ref<any>(null);

const lunasCount = computed(() => members.value.filter(m => m.status === 'lunas').length);

const fetchMembers = async () => {
  try {
    const res = await $api.get('/member');
    if (res.data?.success) {
      members.value = res.data.data.data || res.data.data || [];
    }
  } catch (error) {
    console.error('Failed to fetch members:', error);
  }
};

const filteredMembers = computed(() => {
  return members.value.filter(m => {
    const nama = (m.nama_member || m.nama || '').toLowerCase();
    const kode = (m.kode_member || '').toLowerCase();
    const perusahaan = (m.nama_perusahaan || m.perusahaan || '').toLowerCase();
    const q = (searchQuery.value || '').toLowerCase();
    const matchQuery = !q || nama.includes(q) || kode.includes(q) || perusahaan.includes(q);
    const matchStatus = !statusFilter.value || m.status === statusFilter.value;
    return matchQuery && matchStatus;
  });
});

const showDetail = async (id: number) => {
  showModal.value = true;
  selectedMember.value = null;
  try {
    const res = await $api.get(`/member/${id}`);
    if (res.data?.success) {
      selectedMember.value = res.data.data;
      try {
        selectedMember.value.qr_image = await QRCode.toDataURL(selectedMember.value.kode_member, { width: 160, margin: 1 });
      } catch (e) {
        console.error('QR Error', e);
      }
    }
  } catch (error) {
    console.error('Failed to fetch detail:', error);
  }
};

const confirmDelete = (member: any) => {
  if (member?.status === 'lunas') {
    alert('Member yang sudah lunas tidak dapat dihapus.');
    return;
  }
  memberToDelete.value = member;
};

const executeDelete = async () => {
  if (!memberToDelete.value || memberToDelete.value.status === 'lunas') return;
  try {
    await $api.delete(`/member/${memberToDelete.value.id}`);
    memberToDelete.value = null;
    fetchMembers();
  } catch (error: any) {
    console.error('Failed to delete member:', error);
    alert(error?.response?.data?.message || 'Gagal menghapus data member.');
  }
};

const downloadKartu = () => {
  if (!selectedMember.value || !selectedMember.value.qr_image) {
    alert("Data member belum lengkap atau QR belum dibuat.");
    return;
  }
  
  const doc = new jsPDF({
    orientation: 'landscape',
    unit: 'mm',
    format: [86, 54]
  });
  
  doc.setFillColor(5, 150, 105);
  doc.rect(0, 0, 86, 15, 'F');
  
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  doc.text('KARTU MEMBER E-PARKING', 43, 10, { align: 'center' });
  
  doc.setTextColor(0, 0, 0);
  doc.addImage(selectedMember.value.qr_image, 'PNG', 5, 20, 25, 25);
  
  doc.setFontSize(10);
  doc.setFont('helvetica', 'bold');
  doc.text(selectedMember.value.kode_member, 35, 25);
  
  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  const namaVal = selectedMember.value.nama_member || selectedMember.value.nama || '-';
  const perusahaanVal = selectedMember.value.nama_perusahaan || selectedMember.value.perusahaan || '-';
  doc.text('Nama: ' + namaVal, 35, 32);
  doc.text('Perusahaan: ' + perusahaanVal, 35, 37);
  
  const expDate = selectedMember.value.tanggal_expired ? new Date(selectedMember.value.tanggal_expired).toLocaleDateString('id-ID') : '-';
  doc.text('Berlaku s/d: ' + expDate, 35, 42);
  
  doc.save(`Kartu_Member_${selectedMember.value.kode_member}.pdf`);
};

const formatRupiah = (amount: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
};

const formatTanggal = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};

let autoRefreshTimer: any = null;

const onStorageChange = (e: StorageEvent) => {
  if (e.key === 'member_updated_at') {
    fetchMembers();
  }
};

onMounted(() => {
  fetchMembers();
  if (typeof window !== 'undefined') {
    window.addEventListener('focus', fetchMembers);
    window.addEventListener('storage', onStorageChange);
    autoRefreshTimer = setInterval(fetchMembers, 3000);
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('focus', fetchMembers);
    window.removeEventListener('storage', onStorageChange);
  }
  if (autoRefreshTimer) clearInterval(autoRefreshTimer);
});
</script>

