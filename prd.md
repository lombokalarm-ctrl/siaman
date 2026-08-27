# PRD — Sistem Manajemen Umroh (Tahap Awal)

## Ringkasan
Sistem web berbasis MySQL untuk mengelola data jamaah, paket umroh, serta proses penagihan dan pembayaran (termasuk pembayaran bertahap) hingga terbit kuitansi. Tahap awal fokus pada operasional internal (admin/staf), dengan UI formal-minimal (gaya “Claude/Anthropic”), responsif, dan “dense mobile”.

## Tujuan
- Memusatkan data jamaah dan paket umroh agar mudah dicari, diperbarui, dan diaudit.
- Membuat penagihan (invoice) per jamaah yang rapi dengan histori pembayaran bertahap.
- Mengurangi kesalahan input, duplikasi data, dan rekonsiliasi manual.
- Menyediakan bukti pembayaran (kuitansi) yang dapat dicetak/diunduh.

## Sasaran Non-Tujuan (Tahap Awal)
- Portal jamaah (self-service) dan pembayaran online gateway.
- Integrasi akuntansi eksternal.
- Manajemen keberangkatan detail (seat, manifest, visa tracking lengkap).

## Pengguna & Hak Akses
- Admin
  - Akses penuh: konfigurasi dasar, CRUD semua modul, ekspor data, pembatalan invoice, koreksi pembayaran.
- Staf Operasional
  - CRUD jamaah & paket, membuat invoice, input pembayaran, cetak kuitansi.
- Keuangan
  - Fokus pembayaran, koreksi pembayaran, rekonsiliasi invoice, laporan pembayaran.

## Ruang Lingkup Fitur (MVP)

### 1) Manajemen Jamaah
- Pendaftaran jamaah (create) dan pengelolaan data lengkap (read/update).
- Pencarian cepat + filter.
- Detail jamaah:
  - Profil
  - Paket yang dipilih (opsional pada tahap awal)
  - Daftar invoice & histori pembayaran

**Data Jamaah (minimum)**
- ID Jamaah (kode internal) dan Nomor Pendaftaran.
- Identitas:
  - Nama Lengkap
  - Nama Bapak Kandung
  - NIK
  - Nomor KK
  - Tempat Lahir
  - Tanggal Lahir (input manual: YYYY-MM-DD)
  - Jenis Kelamin
  - Status Pernikahan
  - Pendidikan
  - Pekerjaan
- Kontak:
  - Alamat Lengkap
  - Nomor HP/WhatsApp
  - Email
- Metadata: status (aktif/nonaktif), created_at/updated_at.

**Aturan Input**
- Normalisasi otomatis ke lowercase untuk field tertentu (sesuai preferensi operasional):
  - Sales, Outlet, Pengirim (pada modul invoice/pembayaran) dan field nama tertentu jika diperlukan.

**Aturan Penomoran (ID & Pendaftaran)**
- ID Jamaah:
  - Generate otomatis oleh sistem.
  - Format tetap: `JMH-` + 6 digit urutan (contoh: `JMH-000001`).
  - Tidak bisa diubah dari UI untuk menjaga konsistensi.
- Nomor Pendaftaran:
  - Generate otomatis oleh sistem.
  - Format bisa diatur di menu Pengaturan (MVP: prefix, placeholder tahun, pemisah, panjang urutan, dan kebijakan reset).
  - Default:
    - Prefix: `REG`
    - Placeholder tahun: `{YYYY}`
    - Pemisah: `-`
    - Panjang urutan: 5 (contoh: `00001`)
    - Reset urutan: tahunan (per `{YYYY}`)
  - Contoh default: `REG-2026-00001`.

### 2) Manajemen Paket Umroh
- CRUD paket.
- Paket berisi komponen harga dasar untuk acuan invoice.

**Data Paket (minimum)**
- Nama paket, kode paket (opsional), durasi, tanggal keberangkatan (opsional), maskapai/hotel (opsional).
- Harga (angka), mata uang (default IDR), deskripsi singkat, status aktif.

### 3) Manajemen Pembayaran (Invoice per Jamaah)
- Membuat invoice per jamaah:
  - Mengacu ke paket (opsional) atau input manual item.
  - Total tagihan, termin/due date (opsional).
- Pembayaran jamaah:
  - Mendukung pembayaran bertahap (multi payment) untuk 1 invoice.
  - Setiap pembayaran menghasilkan kuitansi.
- Kuitansi pembayaran:
  - Nomor kuitansi unik.
  - Cetak/unduh (PDF pada tahap berikutnya; tahap awal HTML print-friendly).
- Histori pembayaran pada invoice:
  - Menampilkan daftar pembayaran, sisa tagihan, status invoice (Unpaid/Partial/Paid).

**Data Invoice (minimum)**
- Nomor invoice unik (format dapat dikonfigurasi di tahap berikutnya).
- Jamaah_id, paket_id (opsional), tanggal invoice.
- Total, diskon (opsional), pajak (opsional), grand_total.
- Status: Draft/Sent/Unpaid/Partial/Paid/Void.
- Catatan internal.

**Data Payment (minimum)**
- Invoice_id, tanggal bayar, jumlah bayar.
- Metode: Cash/Transfer/EDC (extendable).
- Referensi transfer (opsional).
- Pengirim (nama pengirim), Outlet, Sales (opsional).
- Dibuat oleh (user_id).

**Aturan Status Invoice**
- Jika sum(payment.amount) == 0 → Unpaid
- Jika 0 < sum(payment.amount) < grand_total → Partial
- Jika sum(payment.amount) >= grand_total → Paid
- Jika Void → tidak dapat ditambahkan payment baru (hanya admin).

## Alur Pengguna (User Flow)
1) Staf membuat Jamaah → verifikasi data inti → simpan.
2) Staf membuat Paket (jika belum ada) → simpan.
3) Staf membuat Invoice untuk jamaah:
   - pilih paket (opsional) → sistem isi total dari paket → simpan invoice.
4) Jamaah melakukan pembayaran:
   - staf input pembayaran (nominal & metode) → sistem update status invoice → terbit kuitansi.
5) Pembayaran bertahap:
   - setiap input pembayaran menambah histori → sisa tagihan turun sampai Paid.

## Layar (Screens) — MVP
- Auth: Login
- Dashboard: ringkasan (jamaah total, invoice unpaid/partial/paid, pembayaran hari ini)
- Jamaah
  - List + pencarian + tombol Tambah
  - Detail (Profil, Invoice)
  - Form Tambah/Edit
- Paket
  - List + tombol Tambah
  - Form Tambah/Edit
- Invoice
  - List invoice (filter status)
  - Detail invoice (ringkasan, histori pembayaran, tombol Tambah Pembayaran, tombol Cetak Kuitansi)
- Kuitansi
  - Halaman print-friendly per payment

## Kebutuhan Non-Fungsional
- Teknologi: Web app + MySQL (InnoDB), deploy Nginx + PHP 8.2-fpm.
- Keamanan:
  - Password hashing (bcrypt/argon2).
  - Session secure, CSRF untuk form, validasi input server-side.
  - Role-based access (minimal 3 role di atas).
- Audit:
  - created_by/updated_by di entitas utama.
  - Log perubahan penting: void invoice, edit payment (tahap berikutnya jika diperlukan).
- Kinerja:
  - Pencarian jamaah responsif pada data menengah (index di kolom nama, no HP, no KTP).
- Backup:
  - Rekomendasi job backup harian database (di VPS).

## Model Data Tingkat Tinggi (Draft)
- users(id, name, email, password_hash, role, created_at, updated_at)
- jamaah(
  id,
  id_jamaah,
  nomor_pendaftaran,
  nama_lengkap,
  nama_bapak_kandung,
  nik,
  nomor_kk,
  tempat_lahir,
  tanggal_lahir,
  jenis_kelamin,
  status_pernikahan,
  pendidikan,
  pekerjaan,
  alamat_lengkap,
  hp,
  email,
  status,
  created_at,
  updated_at
)
- number_sequences(
  id,
  key,
  scope_year,
  scope_month,
  last_number,
  updated_at
)
- settings(
  id,
  key,
  value,
  updated_at
)
- paket(id, nama, kode, durasi_hari, tanggal_berangkat, harga, currency, deskripsi, status, created_at, updated_at)
- invoices(id, nomor, jamaah_id, paket_id, tanggal, subtotal, diskon, pajak, grand_total, status, notes, created_at, updated_at)
- invoice_items(id, invoice_id, label, qty, price, total)
- payments(id, invoice_id, nomor_kuitansi, tanggal, amount, metode, reference, pengirim, outlet, sales, created_at, created_by)

## Laporan (Tahap Berikutnya, tetapi disiapkan struktur)
- Rekap pembayaran per periode.
- Rekap invoice outstanding.
- Rekap pembayaran per metode.

## Milestone Tahap Awal
1) Prototype UI (layout + halaman inti) + PRD final.
2) Skema database + CRUD Jamaah dan Paket.
3) Invoice + multi-payment + kuitansi print-friendly.
4) Hardening: RBAC, validasi, audit, export sederhana.
