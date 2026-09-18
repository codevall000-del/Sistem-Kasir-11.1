<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-[#f8fafc] p-4 sm:p-6 font-sans">
    <!-- Subtle ambient background tint -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Mesin Parkir Smart Kiosk Terminal -->
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
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="text-[11px] font-mono font-bold tracking-wider text-emerald-700 uppercase">GATE TERHUBUNG</span>
        </div>
        <span class="text-xs text-slate-600 font-mono font-bold px-2 py-0.5 rounded bg-slate-100 border border-slate-200">GATE-01 IN</span>
      </div>

      <!-- Judul Terminal -->
      <div class="text-center">
        <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center mx-auto mb-3 shadow-xs">
          <LogIn class="w-7 h-7" />
        </div>
        <h1 class="text-slate-900 text-2xl font-black tracking-tight">
          GERBANG MASUK (TAP IN)
        </h1>
        <p class="text-xs font-bold text-emerald-700 uppercase tracking-widest mt-1">
          E-Parking Plaza Andalas
        </p>
      </div>

      <!-- Instructions -->
      <p class="mt-3 text-slate-500 text-xs text-center font-medium leading-relaxed">
        Silakan tekan tombol di bawah untuk mencetak tiket atau tempelkan kartu RFID member Anda.
      </p>

      <!-- Tombol Print Tiket Besar -->
      <button
        @click="printTicket"
        :disabled="loading"
        class="
          mt-5 w-full
          py-4 px-6
          rounded-2xl
          bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800
          text-white font-black text-xs sm:text-sm tracking-wider
          shadow-lg shadow-emerald-600/25
          transition duration-150
          flex items-center justify-center gap-2.5
          cursor-pointer
          disabled:opacity-50 disabled:cursor-not-allowed
        "
      >
        <Printer class="w-5 h-5" />
        <span>{{ loading ? 'MENCETAK TIKET...' : 'TEKAN UNTUK AMBIL TIKET' }}</span>
      </button>



      <!-- Divider Style -->
      <div class="w-full flex items-center gap-3 my-6">
        <div class="flex-1 h-px bg-slate-200"></div>
        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest font-mono">ATAU TAP KARTU MEMBER</span>
        <div class="flex-1 h-px bg-slate-200"></div>
      </div>

      <!-- Scanner Tap Area -->
      <div class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center">
        <div class="flex items-center justify-center gap-2 text-emerald-700 text-xs font-bold mb-2">
          <Radio class="w-4 h-4 text-emerald-600 animate-pulse" />
          <span>Tempelkan Kartu RFID Member</span>
        </div>
        <input
          v-model="scanCard"
          autofocus
          type="text"
          placeholder="Tempelkan kartu member / Scan ID..."
          class="
            w-full
            h-11
            rounded-xl
            bg-white
            border border-slate-200
            text-center text-xs sm:text-sm font-mono font-bold text-slate-800
            shadow-xs
            focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20
            placeholder:text-slate-400
          "
          @keyup.enter="openGate"
        />
      </div>
    </div>

    <!-- ========================================================== -->
    <!-- POPUP BELUM LUNAS                                          -->
    <!-- ========================================================== -->
    <div
      v-if="showPaymentPopup"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4"
    >
      <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 flex flex-col items-center text-center border border-slate-100 animate-in fade-in duration-150">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 mb-4">
          <XCircle class="w-8 h-8" />
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-1">
          Pembayaran Belum Lunas
        </h2>
        <p class="text-slate-500 text-xs leading-relaxed mb-6">
          Iuran bulanan kartu member belum diselesaikan. Silakan hubungi kasir atau petugas pos.
        </p>
        <button
          @click="closePaymentPopup"
          class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition cursor-pointer shadow-sm shadow-rose-600/30"
        >
          Tutup Pemberitahuan
        </button>
      </div>
    </div>

    <!-- ========================================================== -->
    <!-- POPUP MEMBER BERHASIL (PINTU TERBUKA)                       -->
    <!-- ========================================================== -->
    <div
      v-if="showMemberSuccessPopup"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4"
    >
      <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 flex flex-col items-center text-center border border-slate-100 animate-in fade-in duration-150">
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 mb-4">
          <CheckCircle2 class="w-8 h-8" />
        </div>
        <h2 class="text-xl font-bold text-emerald-700 mb-1">
          Akses Masuk Terbuka
        </h2>
        <p class="text-slate-400 text-xs font-medium">Kartu Member Resmi</p>
        <p class="mt-1 text-lg font-mono font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">
          {{ resultKodeMember }}
        </p>
        <p class="mt-3 text-slate-600 text-xs leading-relaxed mb-6">
          Selamat datang! Palang gerbang terbuka otomatis.
        </p>
        <button
          @click="closeMemberSuccessPopup"
          class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition cursor-pointer shadow-sm shadow-emerald-600/30"
        >
          Selesai (Tutup Gate)
        </button>
      </div>
    </div>

    <!-- ========================================================== -->
    <!-- POPUP INFO TIKET (NON-MEMBER)                               -->
    <!-- ========================================================== -->
    <div
      v-if="showTiketPopup"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4"
    >
      <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 flex flex-col items-center text-center border border-slate-100 animate-in fade-in duration-150">
        <div class="w-14 h-14 rounded-2xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-600 mb-4">
          <Info class="w-8 h-8" />
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-1">
          Tiket Terdeteksi
        </h2>
        <p class="text-slate-400 text-xs">Kode Tiket Pengunjung</p>
        <p class="mt-1 text-base font-mono font-bold text-slate-900">{{ resultKodeTiket }}</p>
        <div class="my-4 p-3 bg-sky-50 rounded-xl border border-sky-100 w-full">
          <p class="text-[11px] text-slate-500 font-medium">Total Tagihan Parkir</p>
          <p class="text-xl font-black text-sky-700 font-mono mt-0.5">Rp {{ formatRupiah(resultBiaya) }}</p>
        </div>
        <p class="text-slate-500 text-xs leading-relaxed mb-6">
          Silakan lakukan pembayaran pada loket petugas.
        </p>
        <button
          @click="closeTiketPopup"
          class="w-full py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition cursor-pointer"
        >
          Konfirmasi
        </button>
      </div>
    </div>

    <!-- ========================================================== -->
    <!-- POPUP TIKET SUDAH DIGUNAKAN                                -->
    <!-- ========================================================== -->
    <div
      v-if="showUsedTicketPopup"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4"
    >
      <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 flex flex-col items-center text-center border border-slate-100 animate-in fade-in duration-150">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 mb-4">
          <AlertCircle class="w-8 h-8" />
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-1">
          Tiket Sudah Digunakan
        </h2>
        <p class="text-slate-500 text-xs leading-relaxed mb-6">
          Tiket ini tercatat telah digunakan untuk keluar dan tidak valid untuk akses masuk kembali.
        </p>
        <button
          @click="closeUsedTicketPopup"
          class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition cursor-pointer"
        >
          Mengerti
        </button>
      </div>
    </div>

    <!-- ========================================================== -->
    <!-- POPUP ERROR UMUM                                            -->
    <!-- ========================================================== -->
    <div
      v-if="showErrorPopup"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4"
    >
      <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 flex flex-col items-center text-center border border-slate-100 animate-in fade-in duration-150">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 mb-4">
          <AlertCircle class="w-8 h-8" />
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-1">
          Gagal Membuka Pintu
        </h2>
        <p class="text-slate-500 text-xs leading-relaxed mb-6">
          {{ errorMessage }}
        </p>
        <button
          @click="closeErrorPopup"
          class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition cursor-pointer"
        >
          Coba Kembali
        </button>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { jsPDF } from "jspdf";
import QrCode from "qrcode";
import { 
  LogIn,
  Printer, 
  Radio, 
  CheckCircle2, 
  AlertCircle, 
  XCircle, 
  Info 
} from "lucide-vue-next";

const { $api } = useNuxtApp();

// ============================================================
// STATE
// ============================================================

const scanCard   = ref("");
const tiket      = ref<any>(null);
const qrPreview  = ref("");
const loading    = ref(false);

// popup flags
const showPaymentPopup       = ref(false);
const showMemberSuccessPopup = ref(false);
const showTiketPopup         = ref(false);
const showUsedTicketPopup    = ref(false);
const showErrorPopup         = ref(false);

// popup data
const resultKodeMember = ref("");
const resultKodeTiket  = ref("");
const resultBiaya      = ref(0);
const errorMessage     = ref("");

// ============================================================
// HELPERS
// ============================================================

const formatRupiah = (angka: number) =>
  new Intl.NumberFormat("id-ID").format(angka ?? 0);

// tutup semua popup
const closePaymentPopup       = () => { showPaymentPopup.value = false; };
const closeMemberSuccessPopup = () => { showMemberSuccessPopup.value = false; };
const closeTiketPopup         = () => { showTiketPopup.value = false; };
const closeUsedTicketPopup    = () => { showUsedTicketPopup.value = false; };
const closeErrorPopup         = () => { showErrorPopup.value = false; };

// ============================================================
// BUAT TIKET
// ============================================================

const buatTiket = async () => {
  try {
    const response = await $api.post("/tiket");
    tiket.value    = response.data.data;
    return tiket.value;
  } catch (error) {
    console.log(error);
    errorMessage.value = "Gagal membuat tiket. Coba lagi.";
    showErrorPopup.value = true;
  }
};

// ============================================================
// CETAK TIKET
// ============================================================

const printTicket = async () => {
  const data = await buatTiket();
  if (!data) return;

  const nomorTiket = data.kode_tiket;

  const qrData = `
PARKIR PLAZA ANDALAS

Kode : ${nomorTiket}
Waktu masuk : ${data.waktu_masuk}
Status : ${data.status}
  `;

  const qrImage = await QrCode.toDataURL(qrData);

  const pdf = new jsPDF({
    orientation: "portrait",
    unit: "mm",
    format: [80, 140],
  });

  pdf.setFontSize(12);
  pdf.text("PARKIR",              40, 12, { align: "center" });
  pdf.text("PLAZA ANDALAS",       40, 20, { align: "center" });
  pdf.setFontSize(8);
  pdf.text("Jl. Dr KRT Radjiman Widyodiningrat", 40, 28, { align: "center" });
  pdf.text("Jakarta Timur",       40, 33, { align: "center" });

  pdf.addImage(qrImage, "PNG", 25, 38, 30, 30);

  pdf.setFontSize(16);
  pdf.setFont("helvetica", "bold");
  pdf.text(nomorTiket, 40, 76, { align: "center" });

  pdf.setFontSize(8);
  pdf.setFont("helvetica", "normal");
  pdf.text(
    `Waktu Masuk : ${data.waktu_masuk}
Gate : Gate-01 Masuk

Informasi Tarif :
1. Non Member : Rp 3.000 / Jam
2. Member : Rp 150.000 / Bulan

Simpan tiket ini untuk verifikasi plat saat keluar.
Terima kasih atas kunjungan Anda.`,
    40, 86, { align: "center" }
  );

  pdf.save(`Tiket-${nomorTiket}.pdf`);
};

// ============================================================
// EKSTRAK KODE TIKET DARI HASIL SCAN QR
// ============================================================

const extractTicketCode = (value: string): string | null => {
  const text = value.trim();

  // Jika scanner langsung kirim "A0001"
  const directMatch = text.match(/^A\d+$/i);
  if (directMatch) return directMatch[0].toUpperCase();

  // Jika scanner baca seluruh isi QR: "Kode : A0001"
  const qrMatch = text.match(/Kode\s*:\s*(A\d+)/i);
  if (qrMatch) return qrMatch[1].toUpperCase();

  // Cari pola A0001 di mana pun
  const generalMatch = text.match(/\b(A\d{4,})\b/i);
  if (generalMatch) return generalMatch[1].toUpperCase();

  return null;
};

// ============================================================
// BUKA PINTU / SCAN KARTU MEMBER ATAU TIKET
// ============================================================

const openGate = async () => {
  if (!scanCard.value) return;
  if (loading.value)   return;

  loading.value = true;

  const scanned     = scanCard.value.trim();
  scanCard.value    = "";

  // ----------------------------------------------------------
  // PERTAMA: COBA MEMBER
  // ----------------------------------------------------------

  try {
    const memberResponse = await $api.post("/member/keluar", { token: scanned });

    if (memberResponse.data.success) {
      resultKodeMember.value       = memberResponse.data.data?.kode_member ?? scanned;
      showMemberSuccessPopup.value = true;
      loading.value = false;
      return;
    }

    // Jika backend return success:false dengan pesan belum lunas
    const msg: string = memberResponse.data.message ?? "";
    if (msg.toLowerCase().includes("lunas") || msg.toLowerCase().includes("bayar")) {
      showPaymentPopup.value = true;
      loading.value = false;
      return;
    }

  } catch (memberError: any) {
    const errMsg: string =
      memberError?.response?.data?.message ?? "";

    // 422 / pesan belum lunas → popup belum lunas
    if (
      errMsg.toLowerCase().includes("lunas") ||
      errMsg.toLowerCase().includes("bayar")
    ) {
      showPaymentPopup.value = true;
      loading.value = false;
      return;
    }

    // Error lain (404 member tidak ditemukan) → lanjut cek tiket
  }

  // ----------------------------------------------------------
  // KEDUA: COBA TIKET REGULER
  // ----------------------------------------------------------

  try {
    const kodeTiket = extractTicketCode(scanned) ?? scanned;

    const tiketResponse = await $api.post("/gate/keluar", { kode: kodeTiket });

    if (tiketResponse.data.success) {
      const data = tiketResponse.data.data;
      if (data?.is_member) {
        resultKodeMember.value       = data?.kode_member ?? scanned;
        showMemberSuccessPopup.value = true;
      } else {
        // Tiket non-member sudah pernah dicetak / masuk / keluar -> TIKET SUDAH DIGUNAKAN
        showUsedTicketPopup.value = true;
      }
    }

  } catch (tiketError: any) {
    const status = tiketError?.response?.status;
    const msg = tiketError?.response?.data?.message ?? "";

    if (
      status === 409 ||
      msg.toLowerCase().includes("sudah digunakan") ||
      msg.toLowerCase().includes("sudah keluar") ||
      msg.toLowerCase().includes("digunakan untuk keluar")
    ) {
      showUsedTicketPopup.value = true;
    } else {
      errorMessage.value = msg || "Kartu / tiket tidak dikenali. Coba lagi.";
      showErrorPopup.value = true;
    }
  } finally {
    loading.value = false;
  }
};
</script>