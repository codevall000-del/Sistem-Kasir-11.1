<template>
  <div class="h-screen bg-[#f8fafc] flex flex-col font-sans overflow-hidden">
    <!-- Navbar -->
    <LayoutNavbar @toggleSidebar="isSidebarOpen = !isSidebarOpen" />

    <div class="flex-1 flex overflow-hidden relative">
      <!-- Sidebar -->
      <LayoutSidebar :isOpen="isSidebarOpen" />
      <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7">
        <div class="max-w-[1400px] mx-auto space-y-6">

          <!-- Header & Actions -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
              <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Panel Super Admin</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                  <ShieldCheck class="w-3.5 h-3.5" />
                  Sistem Root
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                <span class="text-slate-400">Super Administrator</span>
                <span class="text-slate-300">/</span>
                <span class="text-emerald-700 font-semibold">Kelola Akses Petugas</span>
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button 
                @click="openModal()" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm hover:shadow flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
              >
                <UserPlus class="w-4 h-4" />
                <span>Tambah Petugas Baru</span>
              </button>
            </div>
          </div>

          <!-- Stat Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100 shrink-0">
                <Users class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Petugas</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ petugasList.length }} <span class="text-xs font-medium text-slate-500">Akun</span></h3>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                <Activity class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Layanan</p>
                <h3 class="text-sm font-bold text-emerald-600 mt-1 flex items-center gap-2">
                  <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                  </span>
                  MySQL & Sanctum Aktif
                </h3>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100 shrink-0">
                <ShieldCheck class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tingkat Hak Akses</p>
                <h3 class="text-sm font-black text-slate-800 mt-1">Super Administrator</h3>
              </div>
            </div>
          </div>

          <!-- Table Card -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
              <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                  <Users class="w-4 h-4 text-emerald-600" />
                  Daftar Akun Petugas Parkir
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Pengaturan akun yang memiliki wewenang operasional gerbang masuk dan keluar</p>
              </div>

              <div class="relative w-full md:w-64">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  placeholder="Cari nama atau email..." 
                  class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-medium transition"
                />
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                  <tr>
                    <th class="px-5 py-3.5 w-14 text-center">No</th>
                    <th class="px-5 py-3.5">Petugas</th>
                    <th class="px-5 py-3.5">Alamat Email</th>
                    <th class="px-5 py-3.5">No. WhatsApp</th>
                    <th class="px-5 py-3.5">Tanggal Registrasi</th>
                    <th class="px-5 py-3.5 text-center">Wewenang</th>
                    <th class="px-5 py-3.5 text-center w-28">Tindakan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(p, index) in filteredPetugas" :key="p.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-3.5 text-center font-mono text-slate-400">{{ index + 1 }}</td>
                    <td class="px-5 py-3.5">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-black text-xs flex items-center justify-center border border-emerald-200">
                          {{ p.name ? p.name.charAt(0).toUpperCase() : 'P' }}
                        </div>
                        <span class="font-bold text-slate-900">{{ p.name }}</span>
                      </div>
                    </td>
                    <td class="px-5 py-3.5 font-mono text-slate-600">{{ p.email }}</td>
                    <td class="px-5 py-3.5 font-mono text-slate-600">{{ p.no_hp || '-' }}</td>
                    <td class="px-5 py-3.5 text-slate-500">{{ formatTanggal(p.created_at) }}</td>
                    <td class="px-5 py-3.5 text-center">
                      <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ p.role || 'Petugas' }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                      <div class="flex items-center justify-center gap-1.5">
                        <button 
                          @click="openModal(p)" 
                          class="p-2 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition cursor-pointer" 
                          title="Ubah Data Petugas"
                        >
                          <Pencil class="w-3.5 h-3.5" />
                        </button>
                        <button 
                          @click="handleDelete(p.id)" 
                          class="p-2 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition cursor-pointer" 
                          title="Hapus Petugas"
                        >
                          <Trash2 class="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredPetugas.length === 0">
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                      <Users class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                      <p class="font-medium text-slate-600">Tidak ada data petugas yang cocok.</p>
                      <p class="text-[11px] text-slate-400 mt-0.5">Silakan periksa kata kunci pencarian atau tambah petugas baru.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </main>
    </div>

    <!-- Modal Form Tambah / Edit Petugas -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
          <!-- Header -->
          <div class="px-6 py-5 bg-emerald-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                <component :is="isEditing ? Pencil : UserPlus" class="w-4 h-4 text-white" />
              </div>
              <div>
                <h3 class="font-bold text-base">{{ isEditing ? 'Edit Akun Petugas' : 'Tambah Petugas Baru' }}</h3>
                <p class="text-[11px] text-emerald-100">Kredensial login ke terminal operasional</p>
              </div>
            </div>
            <button 
              @click="showModal = false" 
              class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
              <div class="relative">
                <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  v-model="form.name" 
                  type="text" 
                  required 
                  placeholder="Contoh: Budi Santoso" 
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
              <div class="relative">
                <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  v-model="form.email" 
                  type="email" 
                  required 
                  placeholder="petugas@plazaandalas.com" 
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
              <div class="relative">
                <Phone class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  v-model="form.no_hp" 
                  type="text" 
                  placeholder="Contoh: 081234567890" 
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ isEditing ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Kata Sandi' }}
              </label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  v-model="form.password" 
                  :type="showPassword ? 'text' : 'password'" 
                  :required="!isEditing"
                  placeholder="Minimal 8 karakter" 
                  minlength="8"
                  class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                />
                <button 
                  type="button" 
                  @click="showPassword = !showPassword"
                  class="text-slate-400 hover:text-slate-600 absolute right-3 top-1/2 -translate-y-1/2 p-1 cursor-pointer"
                >
                  <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div v-if="!isEditing || form.password">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi</label>
              <div class="relative">
                <KeyRound class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input 
                  v-model="form.password_confirmation" 
                  :type="showPassword ? 'text' : 'password'" 
                  :required="!isEditing || !!form.password"
                  placeholder="Ulangi kata sandi" 
                  class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition"
                />
              </div>
            </div>

            <div class="pt-3 flex gap-3">
              <button 
                type="submit" 
                :disabled="loading" 
                class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2"
              >
                <CheckCircle2 class="w-4 h-4" />
                <span>{{ loading ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Daftarkan Petugas') }}</span>
              </button>
              <button 
                type="button" 
                @click="showModal = false" 
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
              >
                Batal
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { 
  Users, Activity, ShieldCheck, Search, Pencil, Trash2, 
  UserPlus, X, KeyRound, Mail, User, Lock, Eye, EyeOff, CheckCircle2, Phone 
} from 'lucide-vue-next';

const { $api } = useNuxtApp();
const { isLoggedIn, isAdmin } = useAuth();
const router = useRouter();
const isSidebarOpen = ref(false);

const petugasList = ref<any[]>([]);
const searchQuery = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const showPassword = ref(false);

const form = ref({
  id: null as number | null,
  name: '',
  email: '',
  no_hp: '',
  password: '',
  password_confirmation: ''
});

const fetchPetugas = async () => {
  try {
    const res = await $api.get('/petugas');
    petugasList.value = res.data?.data || res.data || [];
  } catch (error) {
    console.error('Error fetching petugas:', error);
  }
};

const filteredPetugas = computed(() => {
  if (!searchQuery.value) return petugasList.value;
  const q = searchQuery.value.toLowerCase();
  return petugasList.value.filter(p => 
    (p.name && p.name.toLowerCase().includes(q)) || 
    (p.email && p.email.toLowerCase().includes(q)) ||
    (p.no_hp && p.no_hp.toLowerCase().includes(q))
  );
});

const openModal = (petugas: any = null) => {
  showPassword.value = false;
  if (petugas) {
    isEditing.value = true;
    form.value = {
      id: petugas.id,
      name: petugas.name,
      email: petugas.email,
      no_hp: petugas.no_hp || '',
      password: '',
      password_confirmation: ''
    };
  } else {
    isEditing.value = false;
    form.value = {
      id: null,
      name: '',
      email: '',
      no_hp: '',
      password: '',
      password_confirmation: ''
    };
  }
  showModal.value = true;
};

const handleSubmit = async () => {
  if (!isEditing.value && form.value.password !== form.value.password_confirmation) {
    alert('Konfirmasi password tidak cocok!');
    return;
  }

  loading.value = true;
  try {
    if (isEditing.value && form.value.id) {
      const payload: any = {
        name: form.value.name,
        email: form.value.email,
        no_hp: form.value.no_hp
      };
      if (form.value.password) {
        payload.password = form.value.password;
        payload.password_confirmation = form.value.password_confirmation;
      }
      await $api.put(`/petugas/${form.value.id}`, payload);
      alert('Data petugas berhasil diperbarui');
    } else {
      await $api.post('/petugas', {
        name: form.value.name,
        email: form.value.email,
        no_hp: form.value.no_hp,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation
      });
      alert('Petugas baru berhasil ditambahkan');
    }
    showModal.value = false;
    fetchPetugas();
  } catch (error: any) {
    console.error('Submit error:', error);
    alert(error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data');
  } finally {
    loading.value = false;
  }
};

const handleDelete = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus petugas ini?')) {
    try {
      await $api.delete(`/petugas/${id}`);
      alert('Petugas berhasil dihapus');
      fetchPetugas();
    } catch (error) {
      console.error('Delete error:', error);
      alert('Gagal menghapus petugas');
    }
  }
};

const formatTanggal = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
};

onMounted(() => {
  if (!isLoggedIn() || !isAdmin()) {
    router.push('/');
    return;
  }
  fetchPetugas();
});
</script>

