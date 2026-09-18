# Panduan Sistem Desain E-Parking Plaza Andalas

Dokumen ini mendefinisikan bahasa visual, sistem komponen, tipografi, dan palet warna untuk platform E-Parking Plaza Andalas.

---

## 1. Identitas & Karakter Visual

- Nama Produk: E-Parking Plaza Andalas
- Tipe Platform: Smart Mobility, Industrial Barrier Gate & Enterprise Parking Management Console
- Karakter: Presisi tinggi, kokoh, bersih, modern, dan sangat mudah dioperasikan di lapangan (operator booth, kiosk gate, dan ruang admin).
- Design Read: Sistem Manajemen Operasional Parkir & Terminal Gerbang Pintar dengan dial ENERGY 2 / RHYTHM 2 / MOTION 2.

---

## 2. Dials (Tingkat Energi, Ritme, dan Gerak)

| Dial | Nilai | Penjelasan |
|---|---|---|
| ENERGY | 2 (Balanced) | Tampilan profesional berstandar enterprise seperti Stripe / Linear. Bersih tanpa warna-warni berlebihan, namun tetap memiliki aksen tegas pada metrik utama. |
| RHYTHM | 2 (Consistent with clear hierarchy) | Grid terstruktur dengan pembagian jelas antara ringkasan eksekutif (KPI cards), kontrol operasional langsung (Gate launcher), visualisasi analitik (grafik batang bergradasi halus), dan data tabular. |
| MOTION | 2 (Transitions & Micro-interactions) | Transisi halus pada hover kartu, modal dialog dengan animasi scale-in lembut, feedback visual pada tombol aksi, dan status pulse real-time. |

---

## 3. Palet Warna Resmi

1. Brand Primary (Emerald):
   - Base: #059669 (Emerald 600)
   - Hover / Active: #047857 (Emerald 700)
   - Tinted Surfaces / Badge: #ecfdf5 (Emerald 50), Border: #a7f3d0 (Emerald 200)
   - Makna: Akses resmi, status aktif, gerbang terbuka, pembayaran lunas.
2. Neutral Base & Surfaces:
   - Background Utama Dashboard: #f8fafc (Slate 50)
   - Surface Kartu: #ffffff dengan border #e2e8f0 (Slate 200)
   - Teks Utama: #0f172a (Slate 900)
   - Teks Sekunder: #64748b (Slate 500)
   - Terminal Kiosk Keras: #090d16 (Deep Obsidian) dan #111827 (Slate 900)
3. Semantic Accents:
   - Indigo / Sky (#3b82f6 / #4f46e5): Metrik analitik, volume kendaraan, dan rincian transaksi.
   - Amber (#d97706 / #f59e0b): Status parkir berjalan, peringatan, gate keluar scanner.
   - Rose / Coral (#e11d48): Tagihan tertunda, pembatalan, tombol hapus, alert bahaya.

---

## 4. Tipografi

- Font Antarmuka: Inter / System Sans-serif modern (font-sans), dengan letter tracking seimbang (tracking-tight pada heading, normal pada body).
- Tabular Data & Kode: Monospace (font-mono / tabular-nums) untuk Kode Tiket, Plat Nomor Kendaraan, Waktu / Jam, dan Nominal Uang (Rupiah).

---

## 5. Standar Eksekusi (Craftsmanship)

1. Tanpa Emoji Sembarangan: Ganti seluruh emoji dekoratif dengan icon stroke SVG berkualitas tinggi dari lucide-vue-next di dalam wadah icon elegan berlatar lembut.
2. Tanpa Blok Warna Solid Silau: Kartu statistik menggunakan latar putih bersih dengan border halus, tipografi angka besar yang tegas, dan badge tren atau icon badge semantik.
3. Kiosk Berkelas Industri: Tampilan gerbang masuk dan keluar dirancang menyerupai terminal layar sentuh fisik modern (smart terminal kiosk) dengan jam digital presisi, status gate aktif, slot tap RFID yang jelas, dan dialog konfirmasi responsif.
4. Resilience & State Lengkap: Menyediakan loading spinner elegan, empty state ramah saat tabel kosong, serta validasi form yang jelas.
5. Kepatuhan Antislop: Tidak menggunakan em dash di teks antarmuka, memenuhi rasio kontras WCAG AA (minimal 4.5:1), dan mendukung navigasi keyboard secara menyeluruh.
