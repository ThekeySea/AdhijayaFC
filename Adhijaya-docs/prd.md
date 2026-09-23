# PRD - Fotocopy Adhijaya

## 1. Ringkasan Produk

Fotocopy Adhijaya adalah website dummy untuk UMKM jasa fotokopi dan percetakan. Website dibuat sebagai proyek akademik yang mensimulasikan alur bisnis nyata: pelanggan memilih layanan, memasukkan detail pesanan, memasukkan ke keranjang, melakukan checkout, membayar melalui Midtrans Sandbox, melihat status pesanan, serta menghubungi admin melalui WhatsApp.

Produk harus terasa seperti website usaha lokal yang benar-benar bisa dipakai, bukan sekadar landing page. Fokus utama adalah mengurangi komunikasi manual yang berulang, membuat detail pesanan lebih jelas, dan memberi admin tempat sederhana untuk mengelola order.

Website bersifat mobile-first, tetapi seluruh alur utama harus nyaman digunakan di desktop.

## 2. Tujuan

### Tujuan utama
- Menyediakan katalog layanan Fotocopy Adhijaya yang mudah dipahami.
- Memungkinkan pelanggan membuat booking/pesanan tanpa harus menjelaskan detail berulang kali lewat chat.
- Menyediakan cart sebelum checkout.
- Menyediakan pembayaran dummy melalui Midtrans Sandbox.
- Menyediakan status order yang dapat dipantau pelanggan.
- Menyediakan role Customer dan Admin.
- Menyediakan jalur komunikasi eksternal melalui WhatsApp menggunakan nomor WhatsApp milik pemilik proyek.
- Menjadi demonstrasi end-to-end untuk kebutuhan tugas.

### Bukan tujuan
- Bukan sistem kasir penuh.
- Bukan marketplace multi-vendor.
- Bukan sistem produksi percetakan yang menghitung biaya berdasarkan mesin atau material secara real-time.
- Bukan payment production. Midtrans hanya digunakan dalam mode Sandbox.
- Bukan integrasi WhatsApp Business API. Tombol WhatsApp membuka chat eksternal melalui wa.me.

## 3. Target Pengguna

### Customer
Pelajar, mahasiswa, pekerja, atau pelanggan umum yang membutuhkan fotokopi/print/scan/penjilidan dan ingin memesan dengan lebih praktis.

Kebutuhan utama:
- Mengetahui layanan dan harga.
- Memilih layanan.
- Menentukan detail pekerjaan.
- Mengunggah file bila diperlukan.
- Melihat total biaya.
- Checkout.
- Membayar.
- Melihat status pesanan.
- Menghubungi admin bila ada kebutuhan khusus.

### Admin
Pemilik atau operator Fotocopy Adhijaya.

Kebutuhan utama:
- Melihat pesanan masuk.
- Melihat detail pesanan dan file.
- Mengubah status pesanan.
- Melihat booking.
- Mengelola layanan, harga, dan ketersediaan.
- Menghubungi customer melalui WhatsApp.
- Memastikan order yang dibayar dapat diproses.

## 4. Masalah yang Diselesaikan

Website dirancang untuk menangani masalah operasional yang umum terjadi pada usaha fotokopi:
- Customer harus bertanya layanan dan harga satu per satu melalui chat.
- Detail order mudah terlewat ketika disampaikan melalui pesan.
- File dan instruksi pekerjaan tidak tersusun dalam satu order.
- Customer tidak memiliki ringkasan pesanan yang jelas.
- Status pekerjaan sulit diketahui tanpa bertanya ke admin.
- Admin harus mencatat pesanan secara manual.
- Pembayaran dan order tidak memiliki satu alur yang jelas.

## 5. Scope Fitur

### 5.1 Public / Guest
- Homepage.
- Daftar layanan.
- Detail layanan.
- Informasi jam operasional.
- Informasi kontak.
- Tombol WhatsApp.
- CTA untuk membuat pesanan.
- Login/register.

### 5.2 Customer
- Register dan login.
- Profile sederhana.
- Katalog layanan.
- Membuat booking/order.
- Memilih beberapa item layanan.
- Cart.
- Mengubah quantity.
- Menghapus item.
- Mengisi detail pengerjaan.
- Upload file.
- Checkout.
- Membuat transaksi Midtrans Sandbox.
- Melihat status pembayaran.
- Melihat status pengerjaan.
- Riwayat order.
- Detail order.
- Tombol WhatsApp dengan pesan yang telah diprefill.
- Cancel order jika status masih memungkinkan.

### 5.3 Admin
- Login sebagai admin.
- Dashboard ringkas.
- Daftar order.
- Filter berdasarkan status.
- Detail order.
- Melihat file yang diunggah.
- Mengubah status order.
- Mengelola layanan.
- Mengelola harga.
- Mengelola status aktif/nonaktif layanan.
- Melihat booking mendatang.
- Tombol WhatsApp ke customer.

## 6. Role dan Authorization

### Customer
Dapat:
- Mengelola profile sendiri.
- Membuat order.
- Melihat order miliknya sendiri.
- Melakukan pembayaran order miliknya.
- Mengunggah file pada order miliknya.
- Membatalkan order sesuai aturan status.

Tidak dapat:
- Melihat order customer lain.
- Mengakses dashboard admin.
- Mengubah harga.
- Mengubah status order secara manual.

### Admin
Dapat:
- Mengakses dashboard admin.
- Melihat seluruh order.
- Mengubah status order.
- CRUD layanan.
- Melihat informasi customer yang diperlukan untuk operasional.

## 7. Status Order

Gunakan state machine sederhana:

`PENDING_PAYMENT`
→ `PAID`
→ `PROCESSING`
→ `READY`
→ `COMPLETED`

State alternatif:
- `CANCELLED`
- `PAYMENT_FAILED`

Aturan:
- Order baru dibuat sebagai `PENDING_PAYMENT`.
- Pembayaran Sandbox yang berhasil mengubah status pembayaran menjadi paid dan order menjadi `PAID`.
- Admin dapat mengubah `PAID` → `PROCESSING` → `READY` → `COMPLETED`.
- Customer dapat membatalkan order sebelum diproses jika aturan bisnis mengizinkan.
- Jangan membiarkan UI mengubah status secara langsung tanpa authorization server-side.

## 8. Booking

Booking digunakan untuk memberi tahu admin kapan customer berencana datang atau mengambil hasil.

Data minimal:
- Tanggal.
- Slot waktu.
- Catatan.
- Nama customer.
- Nomor WhatsApp.
- Referensi order.

Untuk dummy, slot dapat berupa pilihan waktu sederhana. Tidak perlu membuat sistem kalender kompleks.

## 9. Layanan

Contoh layanan yang dapat disediakan sebagai seed data:
- Fotokopi hitam putih.
- Fotokopi warna.
- Print dokumen.
- Print foto.
- Scan dokumen.
- Jilid.
- Laminasi.

Harga harus diperlakukan sebagai data yang dapat diubah admin, bukan hard-coded di UI.

Jika harga aktual bisnis belum dikonfirmasi, gunakan placeholder yang jelas dan jangan menampilkan klaim harga sebagai harga resmi.

## 10. Cart dan Pricing

Cart menyimpan:
- service_id
- nama layanan snapshot
- harga snapshot
- quantity
- detail pengerjaan
- file attachment jika diperlukan

Total dihitung ulang di server berdasarkan harga yang tersimpan di database. Client tidak boleh menjadi sumber kebenaran harga.

Rumus dasar:

`subtotal = harga x quantity`

Jika terdapat biaya tambahan, simpan sebagai komponen terpisah agar mudah diaudit.

## 11. Checkout

Alur:

1. Customer membuka cart.
2. Customer memeriksa item.
3. Customer memilih booking/pickup.
4. Customer mengisi catatan.
5. Server melakukan validasi ulang.
6. Server membuat order.
7. Server membuat transaksi Midtrans Sandbox.
8. Customer membuka Snap payment UI.
9. Midtrans mengirim hasil/status transaksi.
10. Server memperbarui status pembayaran.
11. Customer melihat halaman hasil pembayaran dan detail order.

Jangan menandai order sebagai paid hanya berdasarkan callback client.

## 12. Midtrans Sandbox

Integrasi menggunakan Midtrans Sandbox.

Secret key:
- Hanya di server.
- Disimpan sebagai environment variable.
- Tidak boleh masuk repository.

Client key:
- Boleh digunakan di sisi client sesuai kebutuhan Snap.
- Tetap disimpan sebagai environment variable agar konfigurasi mudah diganti.

Environment variables yang disiapkan:

```env
MIDTRANS_SERVER_KEY=
NEXT_PUBLIC_MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

Webhook/notification endpoint harus diverifikasi dan diproses server-side.

## 13. WhatsApp

Website menggunakan nomor WhatsApp pemilik proyek sebagai tujuan komunikasi eksternal.

Environment variable:

```env
NEXT_PUBLIC_WHATSAPP_NUMBER=
```

Format penyimpanan nomor:
- Gunakan format internasional tanpa tanda `+`, spasi, atau tanda baca.
- Contoh struktur: `628xxxxxxxxxx`

Link dibentuk dengan:
`https://wa.me/{number}?text={encoded_message}`

Contoh pesan:
- Pertanyaan umum.
- Konfirmasi order.
- Pertanyaan terkait file.
- Follow-up order berdasarkan nomor order.

Nomor WhatsApp tidak boleh di-hard-code di banyak komponen.

## 14. UX Requirements

- Mobile-first.
- Desktop tetap memiliki layout yang proporsional.
- Navigasi sederhana.
- CTA utama jelas.
- Form checkout tidak terasa panjang.
- Cart mudah diakses.
- Status order mudah dipahami.
- Loading, empty, error, dan success state wajib tersedia.
- Semua interactive element memiliki feedback.
- Target sentuh mobile minimal sekitar 44px.
- Kontras teks dan background harus memenuhi kebutuhan aksesibilitas dasar.
- Jangan mengandalkan warna saja untuk menyampaikan status.

## 15. Visual Direction

Design read:

> Website layanan lokal untuk pelajar dan pelanggan umum, dengan bahasa visual modern, minimalis, muda, dan praktis. Arahnya clean consumer service, bukan startup SaaS dan bukan desain futuristik.

Arah visual:
- Warna utama: biru.
- Base: putih/off-white dan neutral slate.
- Accent: satu turunan biru atau cyan yang digunakan hemat.
- Hindari gradient biru-ungu sebagai identitas utama.
- Hindari neon.
- Hindari glassmorphism berlebihan.
- Hindari semua komponen berbentuk pill.
- Hindari shadow besar di semua kartu.
- Gunakan whitespace sebagai bagian dari hierarchy.
- Gunakan radius yang konsisten tetapi tidak berlebihan.
- Typography harus modern dan mudah dibaca.
- Motion singkat dan fungsional.

Design dials:
- DESIGN_VARIANCE: 5
- MOTION_INTENSITY: 3
- VISUAL_DENSITY: 3

## 16. Responsive Behavior

Breakpoint harus dimulai dari mobile.

Mobile:
- Single column.
- Bottom navigation atau compact navigation bila memang diperlukan.
- Cart summary dapat menjadi sticky pada tahap checkout.
- Form field full width.
- Table admin berubah menjadi card/list.

Desktop:
- Max-width content container.
- Dua kolom untuk detail yang memang membutuhkan.
- Sidebar admin.
- Cart dan checkout dapat menggunakan split layout.
- Jangan sekadar memperbesar versi mobile.

## 17. Acceptance Criteria

### Customer
- [ ] Guest dapat melihat homepage dan layanan.
- [ ] Customer dapat register/login.
- [ ] Customer dapat menambahkan layanan ke cart.
- [ ] Customer dapat mengubah quantity.
- [ ] Customer dapat menghapus item.
- [ ] Customer dapat mengisi detail order.
- [ ] Customer dapat upload file.
- [ ] Customer dapat membuat order.
- [ ] Customer dapat membuka pembayaran Midtrans Sandbox.
- [ ] Order tidak dianggap paid sebelum status server mengonfirmasi pembayaran.
- [ ] Customer dapat melihat riwayat dan detail order.
- [ ] Customer dapat menghubungi admin melalui WhatsApp.

### Admin
- [ ] Admin dapat login.
- [ ] Admin dapat melihat daftar order.
- [ ] Admin dapat melihat detail order.
- [ ] Admin dapat mengubah status order.
- [ ] Admin dapat melihat file order.
- [ ] Admin dapat CRUD layanan.
- [ ] Admin tidak dapat mengakses data melalui URL/API tanpa authorization.

### Quality
- [ ] Tidak ada secret key di client bundle atau repository.
- [ ] Tidak ada harga final yang hanya dipercaya dari client.
- [ ] Responsive di mobile dan desktop.
- [ ] Error state dan loading state tersedia.
- [ ] UI tidak menggunakan template visual generik.
- [ ] Copy tidak mengandung klaim bisnis yang belum dibuktikan.
