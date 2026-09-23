# Skills - Fotocopy Adhijaya

Dokumen ini adalah operating manual untuk AI coding agent yang mengerjakan proyek Fotocopy Adhijaya. Isinya menggabungkan pendekatan anti-slop copywriting/UI dengan design-taste dan soft-skill, lalu disesuaikan dengan aplikasi commerce/service sederhana ini. Referensi asli menekankan membaca brief sebelum coding, design read, anti-default discipline, visual restraint, copy yang spesifik, dan preflight sebelum delivery. citeturn0view0turn1view0turn1view1turn1view2

## 1. Design Read Skill

Sebelum membuat UI:

> Reading this as: local print/copy service for students and general customers, with a modern minimal youthful language, leaning toward clean consumer-service design with restrained motion.

Gunakan:
- DESIGN_VARIANCE: 5
- MOTION_INTENSITY: 3
- VISUAL_DENSITY: 3

Jangan otomatis memakai aesthetic SaaS, AI, crypto, dashboard, atau agency.

## 2. Brief Interpretation Skill

Baca requirement berdasarkan:
1. siapa user-nya.
2. tindakan yang harus dilakukan.
3. informasi yang dibutuhkan.
4. konteks bisnis.
5. perangkat utama.
6. data yang benar-benar tersedia.

Jika requirement sudah jelas, jangan bertanya ulang.

Jika data bisnis belum tersedia, jangan mengarang. Gunakan placeholder/config.

## 3. UI Composition Skill

### Prioritas
1. Hierarchy.
2. Legibility.
3. Task completion.
4. Spacing.
5. Visual polish.
6. Decoration.

Jangan membalik urutan tersebut.

### Rule
Satu viewport harus memiliki focal point yang jelas.

Untuk halaman transaksi:
- detail order lebih penting daripada dekorasi.
- harga dan CTA harus mudah ditemukan.
- status harus mudah dipindai.

Untuk halaman admin:
- data dan action lebih penting daripada visual marketing.

## 4. Responsive Skill

Selalu implementasikan mobile-first.

### Mobile
- width penuh.
- padding sekitar 16px.
- single column.
- controls mudah disentuh.
- CTA tidak tenggelam.
- tabel berubah menjadi card/list.

### Desktop
- content max-width.
- gunakan whitespace tambahan.
- split layout hanya bila berguna.
- admin sidebar boleh muncul.
- jangan membuat elemen sekadar besar karena viewport besar.

## 5. Component Skill

Buat component berdasarkan behavior.

Contoh:
- `ServiceCard`
- `ServicePrice`
- `QuantityControl`
- `CartItem`
- `OrderStatus`
- `BookingForm`
- `CheckoutSummary`
- `PaymentButton`
- `WhatsAppButton`

Hindari component seperti:
- `UniversalCard`
- `SuperButton`
- `MegaContainer`

yang menerima terlalu banyak prop hanya untuk mengejar reuse.

## 6. Copywriting Skill

Copy harus:
- konkret.
- pendek.
- informatif.
- natural.
- sesuai konteks Indonesia.

Jangan menggunakan:
- buzzword.
- klaim tanpa bukti.
- fake social proof.
- theatrical opener.
- chatbot closer.
- forced rule of three.
- synonym cycling.
- filler phrases.
- negative parallelism yang dipaksakan.

Jika kalimat bisa dipendekkan tanpa kehilangan informasi, pendekkan.

## 7. CTA Skill

CTA harus menyebut tindakan.

Baik:
- `Tambah ke keranjang`
- `Lanjut checkout`
- `Bayar sekarang`
- `Lihat detail`
- `Hubungi admin`

Hindari:
- `Mulai perjalanan`
- `Unlock sekarang`
- `Rasakan kemudahan`
- `Pelajari lebih lanjut` jika action sebenarnya sudah spesifik.

## 8. Visual Anti-Slop Skill

Jangan default ke:
- gradient biru-ungu.
- dark tech background.
- mesh glow.
- glassmorphism.
- semua elemen pill.
- semua elemen shadow.
- icon besar sebagai dekorasi.
- grid tiga kolom di semua section.
- infinite motion.

Gunakan blue sebagai brand anchor dengan neutral surface.

Satu accent tambahan sudah cukup.

## 9. Typography Skill

Prioritaskan:
- Plus Jakarta Sans.
- Geist.

Gunakan:
- display size yang responsif.
- heading yang ringkas.
- body yang nyaman dibaca.

Jangan menggunakan font display yang terlalu eksperimental untuk halaman checkout/admin.

## 10. Icon Skill

Icon harus membantu scanning.

Gunakan icon line yang ringan dan konsisten.

Rules:
- satu family icon.
- ukuran konsisten.
- icon button wajib memiliki label aksesibilitas.
- jangan menggunakan icon sebagai pengganti teks pada action penting.

## 11. Motion Skill

Default:
- hover: subtle.
- press: subtle.
- loading: purposeful.
- success: brief.

Tidak perlu animation library untuk kebutuhan yang bisa ditangani CSS.

Semua motion harus berhenti atau dikurangi untuk `prefers-reduced-motion`.

## 12. Payment Skill

Ketika mengerjakan Midtrans:

1. Pastikan mode Sandbox.
2. Pastikan Server Key hanya server-side.
3. Buat transaction dari server.
4. Client hanya menerima token yang memang diperlukan.
5. Jangan percaya payment result dari client sebagai source of truth.
6. Proses notification.
7. Idempotent update.
8. Simpan provider transaction ID.
9. Tampilkan status yang berasal dari database.

## 13. Order Skill

Ketika membuat order:

```text
Validate auth
→ Validate request
→ Fetch service data
→ Recalculate price
→ Validate booking
→ Create order
→ Create item snapshots
→ Create payment transaction
→ Return payment token
```

Jangan:
```text
Receive total from browser
→ Save total
→ Mark paid
```

## 14. Authorization Skill

Sebelum endpoint/server action mengubah data, tanyakan:

- Siapa user?
- Apa role-nya?
- Resource ini milik siapa?
- Apakah role tersebut boleh melakukan action ini?

UI hiding bukan authorization.

## 15. File Upload Skill

Sebelum upload:
- validate MIME.
- validate extension.
- validate size.
- generate safe path.
- jangan percaya filename.
- simpan metadata.

Jangan membuat public URL permanen untuk file customer jika file seharusnya privat.

## 16. WhatsApp Skill

Buat satu utility:

```ts
buildWhatsAppUrl({
  phone,
  message,
})
```

Sumber nomor:
```env
NEXT_PUBLIC_WHATSAPP_NUMBER=
```

Message dapat menggunakan:
```text
Halo Admin Fotocopy Adhijaya, saya ingin menanyakan order #FA-XXXX.
```

Jangan membuat nomor berbeda di halaman berbeda.

## 17. Empty / Loading / Error Skill

Setiap data-driven screen harus mempertimbangkan:

### Loading
Tampilkan skeleton atau progress yang sesuai.

### Empty
Jelaskan kondisi dan action berikutnya.

Contoh:
`Belum ada pesanan.`

Lalu:
`Lihat layanan`

### Error
Jelaskan apa yang gagal dan apa yang bisa dilakukan.

Hindari:
`Something went wrong!!!`

## 18. Review Skill

Setelah implementasi feature:

### Functional review
- happy path.
- invalid input.
- unauthorized access.
- duplicate action.
- refresh setelah action.
- mobile.

### Visual review
- hierarchy.
- spacing.
- typography.
- responsive.
- focus.
- loading.
- empty.
- error.

### Anti-slop review
Cari:
- gradient default.
- excessive radius.
- excessive shadow.
- excessive glow.
- decorative icon.
- generic copy.
- fake claim.
- repetitive card layout.
- unnecessary animation.

## 19. Preflight Skill

Sebelum delivery:

```text
[ ] npm/pnpm typecheck
[ ] lint
[ ] build
[ ] environment variables documented
[ ] no secret committed
[ ] auth checked
[ ] role checked
[ ] payment flow checked
[ ] WhatsApp link checked
[ ] mobile checked
[ ] desktop checked
[ ] empty state checked
[ ] error state checked
[ ] loading state checked
[ ] copy reviewed
[ ] visual anti-slop review
```

## 20. Change Management Skill

Ketika menerima perubahan:
1. Identifikasi requirement yang berubah.
2. Cari component/server action yang terdampak.
3. Ubah scope sekecil mungkin.
4. Jangan melakukan redesign total tanpa permintaan.
5. Pertahankan behavior yang sudah benar.
6. Re-run relevant checks.

## 21. Research and Evidence Skill

Jika informasi eksternal dibutuhkan:
- cari sumber yang relevan.
- gunakan dokumentasi resmi untuk API/payment/security.
- jangan mengarang behavior library.
- catat keputusan teknis penting.

Untuk copy bisnis:
- hanya gunakan fakta yang diberikan user atau sumber bisnis yang dapat diverifikasi.

## 22. Delivery Style

Hasil akhir harus terasa seperti:
- website UMKM nyata.
- modern tetapi tidak berlebihan.
- muda tetapi tidak memaksa slang.
- sederhana tetapi tidak kosong.
- polished tetapi tidak penuh dekorasi.

Target kualitasnya bukan "terlihat seperti template premium". Targetnya adalah interface yang masuk akal untuk digunakan customer Fotocopy Adhijaya.
