# Rules - Fotocopy Adhijaya

Dokumen ini menjadi aturan kerja untuk AI coding agent dan developer. Aturannya diadaptasi dari prinsip anti-slop pada `anti-slop-copywriting`, `anti-slop-ui`, `taste-skill`, dan `soft-skill`, lalu dipersempit untuk website Fotocopy Adhijaya. Referensi tersebut menekankan design read sebelum coding, anti-default discipline, specificity, restraint, evidence over claims, dan penggunaan visual yang memiliki alasan. citeturn0view0turn1view0turn1view1turn1view2

## 1. Product Truth

1. Jangan mengarang fakta bisnis.
2. Jangan mengarang harga resmi, alamat, jam buka, jumlah pelanggan, testimonial, rating, penghargaan, atau klaim kualitas.
3. Jika data belum tersedia, gunakan placeholder yang jelas atau copy netral.
4. Jangan membuat testimonial fiktif.
5. Jangan membuat statistik fiktif.
6. Jangan menyebut layanan yang belum ditetapkan sebagai layanan resmi.
7. Data dummy harus diberi struktur yang mudah diganti dengan data nyata.

## 2. Copywriting

### Wajib
- Tulis seperti bisnis lokal yang benar-benar berbicara kepada pelanggan.
- Gunakan bahasa Indonesia yang sederhana.
- Utamakan informasi konkret.
- CTA harus menjelaskan tindakan.
- Gunakan kata yang familiar bagi target customer.
- Ulangi istilah jika pengulangan membuat informasi lebih jelas.

### Hindari
- "Unlock"
- "Elevate"
- "Empower"
- "Seamless"
- "Game-changing"
- "Cutting-edge"
- "Revolutionary"
- "Next-level"
- "The future of..."
- "Di era digital..."
- "Hadir sebagai solusi..."
- "Mari kita..."
- "At its core..."
- "Bukan sekadar..."
- "Tidak hanya..., tetapi juga..."
- kalimat promosi yang tidak dapat dibuktikan.

Jangan membuat copy terasa seperti brosur perusahaan besar jika produk sebenarnya adalah UMKM lokal.

## 3. Voice

Voice Fotocopy Adhijaya:
- ramah
- langsung
- ringan
- praktis
- modern
- tidak terlalu formal
- tidak sok gaul

Gunakan istilah seperti:
- Pesan sekarang
- Tambah ke keranjang
- Lanjut checkout
- Lihat pesanan
- Hubungi admin

Hindari slang yang cepat usang atau terlalu banyak emoji.

## 4. Design Read

Sebelum mengubah UI secara signifikan, tentukan secara internal:

> Website layanan lokal untuk pelajar dan pelanggan umum, dengan visual modern, minimalis, muda, dan praktis. Clean consumer service, bukan SaaS futuristik.

Jangan mengganti arah desain hanya karena sebuah pattern sedang populer.

## 5. Visual Identity

Palette:
- Primary: blue.
- Neutral: white/off-white/slate.
- Accent: satu warna pendukung saja bila diperlukan.

Contoh token awal:
```css
--primary: #2563EB;
--primary-dark: #1D4ED8;
--surface: #FFFFFF;
--background: #F8FAFC;
--foreground: #0F172A;
--muted: #64748B;
--border: #E2E8F0;
```

Token boleh disesuaikan setelah implementasi, tetapi jangan memperluas palette tanpa alasan.

### Dilarang sebagai default
- blue-purple gradient.
- purple-black tech gradient.
- neon.
- rainbow gradient.
- blurred glow di seluruh halaman.
- background mesh generik.
- dot/grid pattern hanya untuk dekorasi.
- glassmorphism di navbar, card, modal, dan sidebar sekaligus.
- shadow besar pada semua komponen.
- glow pada semua komponen.

Referensi UI secara eksplisit memperingatkan pattern tersebut sebagai visual default yang membuat interface terasa generik. citeturn1view0

## 6. Shape dan Elevation

Gunakan maksimal beberapa level radius:
- small untuk input.
- medium untuk card.
- large untuk hero/feature container.
- pill hanya untuk badge atau kontrol yang memang cocok.

Jangan membuat:
- semua card pill.
- semua button pill.
- semua input pill.

Shadow hanya untuk menunjukkan elevation. Mayoritas surface boleh flat.

## 7. Typography

Gunakan font modern yang mudah dibaca.

Prioritas:
1. Plus Jakarta Sans atau font sans modern sejenis.
2. Geist bila tersedia.

Jangan memakai typography hanya untuk terlihat futuristik.

Heading:
- jelas.
- tidak terlalu panjang.
- hierarchy kuat.

Body:
- ukuran nyaman di mobile.
- line-height cukup.
- jangan menggunakan text terlalu tipis.

## 8. Layout

- Mobile-first.
- Gunakan whitespace untuk hierarchy.
- Jangan memaksa semua section menjadi grid 3 kolom.
- Jangan membuat semua card berukuran sama jika informasi tidak sama penting.
- Hindari center-align semua teks.
- Gunakan alignment yang mendukung scanning.
- Desktop boleh menggunakan split layout bila membantu.
- Mobile harus menjadi desain yang sengaja dirancang, bukan desktop yang diperkecil.

## 9. Component Hierarchy

Setiap halaman harus memiliki satu primary action.

Contoh:
- Home: Lihat layanan / Pesan sekarang.
- Service detail: Tambah ke keranjang.
- Cart: Lanjut checkout.
- Checkout: Bayar sekarang.
- Order detail: Lihat status / Hubungi admin.
- Admin order detail: Update status.

Jangan memberi lima tombol primary yang sama kuat dalam satu viewport.

## 10. Motion

Motion:
- singkat.
- membantu memahami perubahan state.
- tidak mengganggu proses checkout.
- tidak menjadi dekorasi utama.

Gunakan:
- hover feedback.
- pressed state.
- loading state.
- subtle page transition bila perlu.

Hindari:
- infinite floating animation.
- parallax berlebihan.
- animation pada setiap section.
- bounce yang tidak punya fungsi.
- transition linear generik untuk semua hal.

Hormati `prefers-reduced-motion`.

## 11. Accessibility

- Gunakan semantic HTML.
- Semua form memiliki label.
- Focus state harus terlihat.
- Button harus memiliki state disabled/loading.
- Error harus dapat dipahami tanpa hanya mengandalkan warna.
- Kontras harus memadai.
- Icon-only button membutuhkan accessible label.
- Keyboard navigation harus tetap bekerja.

## 12. Forms

- Satu field, satu tujuan.
- Label jangan hanya placeholder.
- Error ditampilkan dekat field.
- Jangan menghapus input user ketika validation gagal.
- Format nomor WhatsApp dijelaskan bila diperlukan.
- File upload menampilkan jenis/ukuran file yang diterima.

## 13. Cart dan Pricing

- Client bukan sumber kebenaran harga.
- Server selalu mengambil harga terbaru dari database.
- Simpan price snapshot pada order item.
- Total server harus dapat direproduksi.
- Jangan membulatkan total secara berbeda antara UI dan server.

## 14. Payment

- Midtrans Sandbox only.
- Secret key server-only.
- Jangan expose server key melalui `NEXT_PUBLIC_*`.
- Jangan menganggap payment berhasil hanya karena callback browser sukses.
- Server notification harus idempotent.
- Simpan transaction ID.
- Payment status dan order status tidak boleh dicampur tanpa alasan.

## 15. Security

- Validate semua input server-side.
- Authorize berdasarkan session dan role.
- Customer hanya boleh membaca order sendiri.
- Jangan mempercayai `customer_id`, `role`, `price`, `total`, atau status dari client.
- File upload harus divalidasi.
- Jangan menampilkan stack trace.
- Jangan commit secret.
- Jangan membuat admin route hanya terlindungi oleh hidden UI.

## 16. WhatsApp

- Nomor berasal dari environment variable.
- Jangan hard-code nomor di banyak tempat.
- Gunakan URL encoding.
- Pesan prefilled harus singkat dan relevan.
- Jangan membuat integrasi WhatsApp API jika hanya membutuhkan external chat link.

## 17. Data dan Dummy Content

Gunakan dummy data yang masuk akal tetapi jangan menyamarkannya sebagai fakta bisnis.

Contoh aman:
- `Contoh layanan`
- `Harga contoh`
- `Alamat toko`
- `Nomor WhatsApp`

Jika data belum final, tandai pada seed/config.

## 18. Anti-Slop Preflight

Sebelum menyatakan UI selesai, periksa:

- Apakah ada gradient yang hanya dekoratif?
- Apakah terlalu banyak rounded/pill?
- Apakah semua card memiliki shadow?
- Apakah terlalu banyak warna?
- Apakah semua teks centered?
- Apakah ada section 3-card yang dibuat hanya karena template?
- Apakah ada icon dekoratif yang tidak menambah informasi?
- Apakah motion terlalu banyak?
- Apakah copy berisi buzzword?
- Apakah ada klaim tanpa bukti?
- Apakah ada placeholder yang terlihat seperti fakta?
- Apakah desain masih terasa seperti template AI?

Jika jawabannya ya, sederhanakan.

## 19. Engineering Discipline

- Prefer solusi paling sederhana yang memenuhi requirement.
- Jangan menambah dependency tanpa alasan.
- Jangan membuat abstraction sebelum ada pengulangan nyata.
- Jangan membuat generic component yang terlalu fleksibel.
- Jangan mengubah banyak file untuk perubahan kecil jika tidak diperlukan.
- Jangan memperbaiki area di luar scope secara opportunistic.
- TypeScript strict.
- Lint dan typecheck harus bersih sebelum final.

## 20. Definition of Done

Sebuah feature dianggap selesai jika:
- behavior sesuai PRD.
- mobile dan desktop berfungsi.
- loading/error/empty state tersedia bila relevan.
- authorization benar.
- tidak ada secret leakage.
- copy sesuai voice.
- visual tidak melanggar aturan anti-slop.
- tidak ada klaim bisnis fiktif.
- typecheck/lint/build berhasil.
