<template>
<div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-[#f8fafc] p-4 sm:p-6 font-sans">
    <!-- Subtle architectural background accent -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-40 -right-40 w-96 h-96 bg-amber-100/40 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-slate-200/50 rounded-full blur-3xl"></div>
    </div>

    <div class="
      relative z-10
      w-full max-w-[440px]
      bg-white
      border border-slate-200/90
      rounded-3xl
      shadow-xl shadow-slate-300/30
      flex flex-col items-center
      p-6 sm:p-8
    ">
        <!-- Status Beacon Header -->
        <div class="w-full flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            <span class="text-[11px] font-mono font-bold tracking-wider text-amber-700 uppercase">PEMINDAI AKTIF</span>
          </div>
          <span class="text-xs text-slate-600 font-mono font-bold px-2 py-0.5 rounded bg-slate-100 border border-slate-200">GATE-01 OUT</span>
        </div>

        <!-- Judul Terminal -->
        <div class="text-center">
          <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center mx-auto mb-3 shadow-xs">
            <LogOut class="w-7 h-7" />
          </div>
          <h1 class="text-slate-900 text-2xl font-black tracking-tight">
            GERBANG KELUAR
          </h1>
          <p class="text-xs font-bold text-amber-700 uppercase tracking-widest mt-1">
            E-Parking Plaza Andalas
          </p>
        </div>

        <p class="mt-4 text-slate-500 text-xs text-center font-medium leading-relaxed">
          Masukkan nomor plat kendaraan terlebih dahulu, lalu tempelkan kartu member atau pindai barcode tiket parkir.
        </p>

        <!-- Input Plat Nomor Kendaraan Keluar (Wajib) -->
        <div class="w-full mt-5">
          <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5 text-left flex items-center justify-between">
            <span>1. Nomor Plat Kendaraan Keluar (Wajib)</span>
            <span v-if="inputPlatKeluar.trim()" class="text-[10px] text-emerald-600 font-bold lowercase bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">siap scan</span>
            <span v-else class="text-[10px] text-rose-600 font-bold lowercase bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">wajib diisi</span>
          </label>
          <div class="relative">
            <Car class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              ref="platInput"
              v-model="inputPlatKeluar"
              type="text"
              required
              placeholder="Contoh: B 1234 ABC"
              @input="inputPlatKeluar = inputPlatKeluar.toUpperCase()"
              class="
                w-full
                h-12
                pl-10 pr-4
                rounded-xl
                bg-slate-50
                border-2
                border-slate-200
                text-xs sm:text-sm
                font-mono font-bold uppercase
                text-slate-900
                shadow-xs
                focus:bg-white
                focus:border-amber-500
                focus:ring-2
                focus:ring-amber-500/20
                focus:outline-none
                placeholder:text-slate-400
                transition
              "
              @keyup.enter="focusScannerInput"
            />
          </div>
          <p class="text-[10px] text-slate-400 font-medium text-left mt-1.5">
            Petugas wajib mengisi nomor plat kendaraan terlebih dahulu sebelum pemindaian dapat dilakukan
          </p>
        </div>

        <!-- Scanner Box -->
        <div 
          class="w-full border rounded-2xl p-5 text-center mt-4 transition-all duration-200"
          :class="inputPlatKeluar.trim() ? 'bg-slate-50 border-slate-200/90' : 'bg-slate-100/70 border-dashed border-slate-300 opacity-75'"
        >
          <div 
            class="flex items-center justify-center gap-2 text-xs font-bold mb-2.5 transition-colors"
            :class="inputPlatKeluar.trim() ? 'text-amber-700' : 'text-slate-400'"
          >
            <ScanLine class="w-4 h-4" :class="inputPlatKeluar.trim() ? 'text-amber-600 animate-pulse' : 'text-slate-400'" />
            <span>2. Arahkan Barcode Tiket / Kartu Member</span>
          </div>
          <input
              ref="scannerInput"
              v-model="scanCard"
              type="text"
              :placeholder="inputPlatKeluar.trim() ? 'Scan barcode / Ketik kode tiket...' : 'Ketik nomor plat di atas terlebih dahulu...'"
              class="
                w-full
                h-12
                rounded-xl
                border-2
                text-center
                text-xs sm:text-sm
                font-mono font-bold
                shadow-xs
                focus:outline-none
                transition
              "
              :class="inputPlatKeluar.trim() 
                ? 'bg-white border-amber-400/50 text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 placeholder:text-slate-400' 
                : 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed placeholder:text-slate-400'"
              @keyup.enter="openGate"
              :disabled="loading || isAnyPopupOpen || !inputPlatKeluar.trim()"
          />
          <p 
            class="text-[10px] font-medium mt-2 transition-colors"
            :class="inputPlatKeluar.trim() ? 'text-slate-400' : 'text-amber-700 font-semibold'"
          >
            {{ inputPlatKeluar.trim() ? 'Tekan Enter setelah scan barcode atau mengetik kode tiket' : 'Input nomor plat di atas lalu tekan Enter untuk membuka scanner' }}
          </p>
        </div>

        <!-- Tombol Kembali -->
        <button 
            @click="router.push('/petugas')" 
            class="mt-6 w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 font-bold transition text-xs border border-slate-200 flex items-center justify-center gap-2 cursor-pointer"
        >
            <ArrowLeft class="w-3.5 h-3.5 text-slate-500" />
            <span>Kembali ke Dashboard Petugas</span>
        </button>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. POPUP PEMBAYARAN NON MEMBER (showPaymentNonMemberPopup)                -->
    <!-- ========================================================================= -->
    <div v-if="showPaymentNonMemberPopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-7 border border-slate-100">
            
            <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center">
                        <CreditCard class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 leading-tight">Pembayaran Parkir</h3>
                        <p class="text-xs text-slate-400">Pengunjung Reguler</p>
                    </div>
                </div>
                <span class="bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                    Non-Member
                </span>
            </div>

            <!-- DETAIL TIKET -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2 text-xs text-slate-700 mb-4">
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">Kode Tiket:</span>
                    <span class="font-mono font-bold text-slate-900">{{ nonMemberData?.kode_tiket }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">Waktu Masuk:</span>
                    <span class="font-mono">{{ formatTanggal(nonMemberData?.waktu_masuk) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">Durasi Parkir:</span>
                    <span class="font-semibold text-slate-800">{{ nonMemberData?.durasi || '1 Jam' }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200/80 pt-2 mt-2 items-center">
                    <span class="font-bold text-slate-900">Total Tarif:</span>
                    <span class="text-xl font-black text-rose-600 font-mono">Rp {{ formatRupiah(totalBiaya) }}</span>
                </div>
            </div>

            <!-- INPUT PLAT NOMOR NON-MEMBER -->
            <div class="mb-3.5">
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                  Nomor Plat Kendaraan (Wajib)
                </label>
                <div class="relative">
                  <Car class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input 
                    v-model="platNomorNonMember" 
                    type="text" 
                    required
                    placeholder="Contoh: B 1234 XYZ" 
                    class="w-full h-11 pl-10 pr-3.5 border border-slate-200 rounded-xl text-sm font-bold uppercase font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white"
                  />
                </div>
            </div>

            <!-- INPUT UANG BAYAR -->
            <div class="mb-5">
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Nominal Uang Pembayaran (Rp)</label>
                <input 
                    ref="paymentInput"
                    v-model="uangBayar" 
                    type="number" 
                    placeholder="Masukkan jumlah uang..." 
                    class="w-full h-11 border border-slate-200 rounded-xl px-3.5 text-base font-black text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white font-mono"
                    @keyup.enter="confirmPayment"
                />
                
                <div v-if="uangBayar && Number(uangBayar) >= totalBiaya" class="mt-2 text-right text-xs">
                    <span class="text-slate-400 font-medium">Uang Kembalian: </span>
                    <span class="font-black text-emerald-600 font-mono text-sm">Rp {{ formatRupiah(Number(uangBayar) - totalBiaya) }}</span>
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="grid grid-cols-2 gap-2.5">
                <button 
                    @click="closePaymentPopup"
                    class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer"
                >
                    Batalkan
                </button>
                <button 
                    @click="confirmPayment"
                    :disabled="loading"
                    class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-emerald-600/30 disabled:opacity-50 cursor-pointer"
                >
                    {{ loading ? 'Memproses...' : 'Bayar & Buka Gate' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. POPUP GERBANG TERBUKA (showGatePopup)                                   -->
    <!-- ========================================================================= -->
    <div v-if="showGatePopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-7 text-center border border-slate-100">
            <div class="w-16 h-16 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <CheckCircle2 class="w-9 h-9" />
            </div>

            <h3 class="text-xl font-black text-emerald-700 tracking-tight mb-1">PINTU KELUAR TERBUKA</h3>
            <p class="text-slate-500 text-xs mb-4 leading-relaxed">{{ gateMessage || 'Silakan keluar, terima kasih atas kunjungan Anda!' }}</p>

            <!-- INFO BOX JIKA ADA KEMBALIAN ATAU IDENTITAS -->
            <div v-if="gateDetails" class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs text-slate-700 mb-6 space-y-1.5 text-left">
                <div v-if="gateDetails.nama_member" class="flex justify-between"><span class="text-slate-400">Nama Member:</span> <span class="font-semibold text-slate-800">{{ gateDetails.nama_member }}</span></div>
                <div v-if="gateDetails.plat_nomor" class="flex justify-between"><span class="text-slate-400">Nomor Plat:</span> <span class="font-mono font-bold text-slate-900 uppercase">{{ gateDetails.plat_nomor }}</span></div>
                <div v-if="gateDetails.kembalian !== undefined" class="flex justify-between text-emerald-700 font-bold border-t border-slate-200 pt-1.5 mt-1.5"><span>Uang Kembalian:</span> <span class="font-mono font-black">Rp {{ formatRupiah(gateDetails.kembalian) }}</span></div>
            </div>

            <button 
                @click="closeGatePopup"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-emerald-600/30 cursor-pointer"
            >
                Selesai (Tutup Gate)
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. POPUP MEMBER BELUM TAP MASUK (showNotEnteredPopup)                      -->
    <!-- ========================================================================= -->
    <div v-if="showNotEnteredPopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-7 text-center border border-slate-100">
            <div class="w-14 h-14 bg-amber-50 border border-amber-200 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <AlertTriangle class="w-7 h-7" />
            </div>

            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-1">KENDARAAN BELUM TAP MASUK</h3>
            <p class="text-slate-500 text-xs leading-relaxed mb-6">
                Kartu member ini belum tercatat melakukan tap masuk di gerbang masuk. Silakan hubungi pengawas parkir.
            </p>

            <button 
                @click="closeNotEnteredPopup"
                class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition shadow-sm cursor-pointer"
            >
                Tutup Peringatan
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. POPUP TIKET SUDAH DIGUNAKAN (showUsedTicketPopup)                      -->
    <!-- ========================================================================= -->
    <div v-if="showUsedTicketPopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-7 text-center border border-slate-100">
            <div class="w-14 h-14 bg-rose-50 border border-rose-200 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <AlertCircle class="w-7 h-7" />
            </div>

            <h3 class="text-lg font-bold text-rose-600 mb-1">Tiket Sudah Digunakan</h3>
            <p class="text-slate-500 text-xs leading-relaxed mb-6">
                Tiket ini sudah digunakan untuk keluar sebelumnya dan tidak dapat digunakan kembali.
            </p>

            <button 
                @click="closeUsedTicketPopup"
                class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition cursor-pointer"
            >
                Mengerti
            </button>
        </div>
    </div>

</div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted } from 'vue';
import { 
  LogOut, 
  ScanLine, 
  ArrowLeft, 
  Car, 
  CreditCard, 
  CheckCircle2, 
  AlertTriangle, 
  AlertCircle 
} from 'lucide-vue-next';

definePageMeta({
  layout: false
})

const { $api } = useNuxtApp();
const router = useRouter();

// STATE UTAMA
const scanCard = ref('');
const inputPlatKeluar = ref('');
const loading = ref(false);
const platInput = ref<HTMLInputElement | null>(null);
const scannerInput = ref<HTMLInputElement | null>(null);
const paymentInput = ref<HTMLInputElement | null>(null);

// POPUP STATES
const showPaymentNonMemberPopup = ref(false);
const showGatePopup = ref(false);
const showNotEnteredPopup = ref(false);
const showUsedTicketPopup = ref(false);

// DATA FORM & PROSES
const memberToken = ref('');
const nonMemberData = ref<any>(null);
const platNomorNonMember = ref('');
const totalBiaya = ref(0);
const uangBayar = ref<number | ''>('');
const gateMessage = ref('');
const gateDetails = ref<any>(null);

// COMPUTED: CHECK APAKAH ADA POPUP AKTIF
const isAnyPopupOpen = computed(() => {
    return showPaymentNonMemberPopup.value || 
           showGatePopup.value || 
           showNotEnteredPopup.value || 
           showUsedTicketPopup.value;
});

// FOKUS SCANNER ATAU PLAT INPUT
const focusScannerInput = async () => {
    if (!inputPlatKeluar.value.trim()) {
        platInput.value?.focus();
        return;
    }
    await nextTick();
    scannerInput.value?.focus();
};

const focusScanner = async () => {
    await nextTick();
    scanCard.value = '';
    setTimeout(() => {
        if (!inputPlatKeluar.value.trim()) {
            platInput.value?.focus();
        } else {
            scannerInput.value?.focus();
        }
    }, 150);
};

onMounted(() => {
    focusScanner();
});

// EKSTRAKSI KODE TIKET DARI SCANNER ATAU QR CODE
const extractTicketCode = (text: string) => {
    if (!text) return null;
    const qrMatch = text.match(/Kode\s*:\s*(A\d+)/i);
    if (qrMatch && qrMatch[1]) return qrMatch[1].toUpperCase();

    const generalMatch = text.match(/\bA\d{4,}\b/i);
    if (generalMatch) return generalMatch[0].toUpperCase();

    return null;
};

// =========================================================================
// HANDLER SCAN UTAMA (openGate)
// =========================================================================
const openGate = async () => {
    if (isAnyPopupOpen.value || loading.value) return;

    if (!inputPlatKeluar.value.trim()) {
        alert('Silakan masukkan nomor plat kendaraan terlebih dahulu sebelum scan!');
        platInput.value?.focus();
        return;
    }

    const scanned = scanCard.value.trim();
    if (!scanned) return;

    loading.value = true;

    try {
        const extractedTicketCode = extractTicketCode(scanned);
        const finalCode = extractedTicketCode || scanned;

        // Panggil endpoint scan backend dengan nomor plat kendaraan (jika diisi)
        const payload: any = { kode: finalCode };
        if (inputPlatKeluar.value.trim()) {
            payload.plat_nomor = inputPlatKeluar.value.trim().toUpperCase();
        }

        const response = await $api.post('/gate/keluar', payload);

        if (response.data.success) {
            const data = response.data.data;

            if (data.is_member) {
                // JIKA MEMBER
                gateMessage.value = 'Kartu Member Valid. Pintu Terbuka!';
                gateDetails.value = {
                    nama_member: data.nama_member,
                    plat_nomor: data.plat_nomor || inputPlatKeluar.value.trim().toUpperCase()
                };
                showGatePopup.value = true;
            } else {
                // JIKA REGULER (NON-MEMBER)
                nonMemberData.value = data;
                totalBiaya.value = response.data.biaya || data.biaya || 0;
                uangBayar.value = '';
                // Gunakan plat yang diinput petugas atau otomatis dari tiket (hasil jepretan ANPR masuk)
                platNomorNonMember.value = inputPlatKeluar.value.trim().toUpperCase() || data.plat_nomor || '';
                showPaymentNonMemberPopup.value = true;

                await nextTick();
                setTimeout(() => {
                    paymentInput.value?.focus();
                }, 200);
            }
        }
    } catch (error: any) {
        const status = error.response?.status;
        const msg = error.response?.data?.message || 'Terjadi kesalahan sistem.';

        if (
            status === 409 ||
            msg.toLowerCase().includes('sudah digunakan') ||
            msg.toLowerCase().includes('sudah keluar') ||
            msg.toLowerCase().includes('digunakan untuk keluar')
        ) {
            // TIKET SUDAH DIGUNAKAN
            showUsedTicketPopup.value = true;
        } else if (status === 400 && msg.toLowerCase().includes('belum')) {
            // BELUM TAP MASUK
            showNotEnteredPopup.value = true;
        } else {
            alert(msg);
            focusScanner();
        }
    } finally {
        loading.value = false;
    }
};

// =========================================================================
// HANDLER KONFIRMASI PEMBAYARAN NON-MEMBER
// =========================================================================
const confirmPayment = async () => {
    if (!platNomorNonMember.value.trim()) {
        alert('Silakan masukkan nomor plat kendaraan pengunjung.');
        return;
    }

    if (uangBayar.value === '' || Number(uangBayar.value) <= 0) {
        alert('Silakan masukkan nominal uang pembayaran yang sah.');
        paymentInput.value?.focus();
        return;
    }

    const bayar = Number(uangBayar.value);
    if (bayar < totalBiaya.value) {
        alert(`Uang pembayaran kurang Rp ${formatRupiah(totalBiaya.value - bayar)}`);
        paymentInput.value?.focus();
        return;
    }

    loading.value = true;

    try {
        const response = await $api.post('/gate/payment', {
            kode: nonMemberData.value.kode_tiket,
            bayar: bayar,
            plat_nomor: platNomorNonMember.value.trim().toUpperCase()
        });

        if (response.data.success) {
            const kembalian = response.data.kembalian || (bayar - totalBiaya.value);
            showPaymentNonMemberPopup.value = false;
            
            gateMessage.value = 'Pembayaran Berhasil! Pintu Terbuka.';
            gateDetails.value = {
                kembalian: kembalian,
                plat_nomor: platNomorNonMember.value.trim().toUpperCase()
            };
            showGatePopup.value = true;
        }
    } catch (error: any) {
        const status = error.response?.status;
        if (status === 409) {
            showPaymentNonMemberPopup.value = false;
            showUsedTicketPopup.value = true;
        } else {
            alert(error.response?.data?.message || 'Gagal memproses pembayaran.');
        }
    } finally {
        loading.value = false;
    }
};

// CLOSE POPUP HANDLERS
const closePaymentPopup = () => {
    showPaymentNonMemberPopup.value = false;
    nonMemberData.value = null;
    uangBayar.value = '';
    platNomorNonMember.value = '';
    focusScanner();
};

const closeGatePopup = () => {
    showGatePopup.value = false;
    gateDetails.value = null;
    inputPlatKeluar.value = '';
    focusScanner();
};

const closeNotEnteredPopup = () => {
    showNotEnteredPopup.value = false;
    focusScanner();
};

const closeUsedTicketPopup = () => {
    showUsedTicketPopup.value = false;
    focusScanner();
};

// FORMATTERS
const formatRupiah = (angka: number) => {
    return new Intl.NumberFormat('id-ID').format(angka || 0);
};

const formatTanggal = (tanggal: string) => {
    if (!tanggal) return '-';
    return new Date(tanggal).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>
