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
                  <h1 class="text-base font-bold">Edit Informasi Member</h1>
                  <p class="text-xs text-emerald-100">Perbarui identitas dan instansi member</p>
                </div>
              </div>
              <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                <Pencil class="w-5 h-5 text-white" />
              </div>
            </div>

            <div v-if="loadingMember" class="p-12 text-center text-slate-500">
              <div class="w-8 h-8 border-3 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
              <p class="text-xs font-semibold text-slate-600">Memuat rincian data member...</p>
            </div>

            <!-- Form Body -->
            <form v-else @submit.prevent="updateMemberProfile" class="p-6 sm:p-8 space-y-4">
              <!-- Member ID Badge -->
              <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center text-xs">
                <span class="text-slate-500 font-medium">Nomor Kartu / ID:</span>
                <span class="font-mono font-bold text-emerald-800 bg-white px-2.5 py-0.5 rounded border border-slate-200">
                  {{ form.kode_member }}
                </span>
              </div>

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

              <!-- Buttons -->
              <div class="pt-2 space-y-2">
                <button 
                  type="submit" 
                  :disabled="loadingSubmit" 
                  class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/30 transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                  <CheckCircle2 class="w-4 h-4" />
                  <span>{{ loadingSubmit ? 'Menyimpan Perubahan...' : 'Simpan Perubahan' }}</span>
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
import { ref, reactive, onMounted } from 'vue';
import { 
  ArrowLeft, Pencil, CheckCircle2, User, Building2 
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const { $api } = useNuxtApp();

const isSidebarOpen = ref(false);
const loadingMember = ref(true);
const loadingSubmit = ref(false);

const form = reactive<any>({
  id: null,
  kode_member: '',
  nama_member: '',
  nama_perusahaan: ''
});

const fetchMemberDetail = async () => {
  const id = route.params.id;
  if (!id) return;

  try {
    const res = await $api.get(`/member/${id}`);
    if (res.data?.success) {
      const m = res.data.data;
      form.id = m.id;
      form.kode_member = m.kode_member;
      form.nama_member = m.nama_member || m.nama || '';
      form.nama_perusahaan = m.nama_perusahaan || m.perusahaan || '';
    }
  } catch (error) {
    console.error('Failed to fetch detail:', error);
  } finally {
    loadingMember.value = false;
  }
};

const updateMemberProfile = async () => {
  if (!form.nama_member || !form.nama_perusahaan) {
    alert('Nama dan Instansi / Perusahaan wajib diisi');
    return;
  }

  loadingSubmit.value = true;
  try {
    const res = await $api.put(`/member/${form.id}`, {
      nama_member: form.nama_member,
      nama_perusahaan: form.nama_perusahaan
    });

    if (res.data?.success !== false) {
      alert('Data profil member berhasil diperbarui!');
      router.push('/petugas/member/select');
    } else {
      alert('Gagal memperbarui data member.');
    }
  } catch (error: any) {
    console.error('Failed to update:', error);
    alert(error?.response?.data?.message || 'Terjadi kesalahan saat memperbarui.');
  } finally {
    loadingSubmit.value = false;
  }
};

onMounted(() => {
  fetchMemberDetail();
});
</script>
