# Architecture - Fotocopy Adhijaya

## 1. Architecture Decision

Gunakan monolithic full-stack web application yang sederhana untuk dummy akademik tetapi tetap memiliki boundary yang jelas antara UI, server, database, payment, dan external communication.

Stack yang direkomendasikan:

- Next.js App Router
- TypeScript
- Tailwind CSS
- shadcn/ui hanya sebagai primitive, bukan sebagai desain final
- Supabase PostgreSQL
- Supabase Auth
- Supabase Storage untuk file order
- Midtrans Snap Sandbox
- WhatsApp `wa.me`
- Zod untuk validation
- React Hook Form untuk form kompleks

Alasan:
- Satu repository cukup untuk frontend dan backend.
- Route Handler/server action dapat menangani operasi server.
- Supabase memberi auth, database, dan storage tanpa menambah banyak infrastructure.
- Midtrans membutuhkan secret key server-side.
- WhatsApp cukup menggunakan deep link sehingga tidak perlu API integration.

## 2. High-Level Flow

```text
Browser
  |
  v
Next.js UI
  |
  +--> Server Actions / Route Handlers
  |        |
  |        +--> Supabase Auth
  |        +--> PostgreSQL
  |        +--> Supabase Storage
  |        +--> Midtrans API
  |
  +--> Midtrans Snap UI
  |
  +--> WhatsApp wa.me
```

## 3. Application Layers

### Presentation
Responsibility:
- Page rendering.
- Form.
- Client interaction.
- Loading/error/success state.
- Responsive layout.

Tidak boleh:
- Menyimpan Midtrans Server Key.
- Menentukan harga final.
- Mengubah status order tanpa server authorization.

### Application / Server
Responsibility:
- Validasi input.
- Authorization.
- Membuat order.
- Recalculate pricing.
- Membuat transaksi Midtrans.
- Memproses notification Midtrans.
- Mengubah status order.
- Generate WhatsApp link bila perlu.

### Data
Responsibility:
- Users/profile.
- Services.
- Orders.
- Order items.
- Bookings.
- Payments.
- File metadata.

### Integration
- Midtrans.
- WhatsApp.
- Supabase.

## 4. Suggested Project Structure

```text
src/
├── app/
│   ├── (public)/
│   │   ├── page.tsx
│   │   ├── layanan/
│   │   └── kontak/
│   ├── (customer)/
│   │   ├── pesanan/
│   │   ├── cart/
│   │   ├── checkout/
│   │   └── profile/
│   ├── admin/
│   │   ├── page.tsx
│   │   ├── orders/
│   │   ├── services/
│   │   └── bookings/
│   └── api/
│       └── midtrans/
│           └── notification/
├── components/
│   ├── ui/
│   ├── layout/
│   ├── services/
│   ├── cart/
│   ├── checkout/
│   ├── orders/
│   └── admin/
├── features/
│   ├── auth/
│   ├── services/
│   ├── cart/
│   ├── orders/
│   ├── bookings/
│   ├── payments/
│   └── whatsapp/
├── lib/
│   ├── supabase/
│   ├── midtrans/
│   ├── whatsapp/
│   ├── validations/
│   └── utils/
└── types/
```

## 5. Database Model

### profiles
```text
id
full_name
phone
role: CUSTOMER | ADMIN
created_at
updated_at
```

`id` mereferensikan user dari Supabase Auth.

### services
```text
id
name
slug
description
category
unit
price
is_active
image_url
created_at
updated_at
```

### orders
```text
id
order_number
customer_id
booking_id nullable
status
payment_status
subtotal
additional_fee
total
customer_note
created_at
updated_at
```

### order_items
```text
id
order_id
service_id
service_name_snapshot
unit_price_snapshot
quantity
item_note
created_at
```

Snapshot diperlukan agar perubahan harga layanan di masa depan tidak mengubah histori order lama.

### order_files
```text
id
order_id
order_item_id nullable
file_name
storage_path
mime_type
file_size
created_at
```

### bookings
```text
id
customer_id
booking_date
time_slot
note
status
created_at
updated_at
```

### payments
```text
id
order_id
provider
provider_transaction_id
gross_amount
transaction_status
fraud_status nullable
payment_type nullable
paid_at nullable
raw_notification jsonb nullable
created_at
updated_at
```

## 6. Relationship

```text
auth.users
   |
   +--- profiles

profiles
   |
   +--- orders
   |      |
   |      +--- order_items
   |      |       |
   |      |       +--- services
   |      |
   |      +--- order_files
   |      |
   |      +--- payments
   |
   +--- bookings
```

## 7. Authorization

Gunakan dua lapisan:

1. UI guard untuk pengalaman pengguna.
2. Server/database authorization sebagai sumber keamanan.

Customer hanya boleh:
- select order miliknya.
- insert order sebagai dirinya sendiri.
- update field yang memang diizinkan.
- upload file ke namespace miliknya.

Admin:
- dapat membaca dan mengubah order sesuai policy.
- dapat CRUD services.

Jangan mengandalkan `role` yang dikirim dari client.

Jika menggunakan Supabase RLS, policy harus menjadi boundary tambahan, bukan sekadar fitur opsional.

## 8. Order Creation

Jangan membuat order dari total yang dikirim browser.

Flow:

```text
Client cart
   |
   v
POST /checkout
   |
   +--> validate session
   +--> validate items
   +--> fetch current service prices
   +--> calculate subtotal
   +--> validate booking
   +--> create order
   +--> create order_items snapshots
   +--> create Midtrans transaction
   |
   v
Return Snap token
```

## 9. Midtrans Architecture

### Client
Menerima Snap token dari server dan membuka Midtrans Snap.

### Server
Memiliki:
- `MIDTRANS_SERVER_KEY`
- mode sandbox/production
- API integration.

### Notification

```text
Midtrans
   |
   v
/api/midtrans/notification
   |
   +--> validate notification
   +--> find payment/order
   +--> map transaction status
   +--> update payment
   +--> update order
```

Notification harus idempotent. Notification yang sama tidak boleh membuat side effect berulang.

Untuk dummy:
- production mode harus false.
- secret key tidak pernah masuk `NEXT_PUBLIC_*`.

## 10. WhatsApp Architecture

Tidak perlu backend integration.

```text
UI
  |
  v
buildWhatsAppUrl(number, message)
  |
  v
window.open("https://wa.me/...")
```

Nomor berasal dari:

```env
NEXT_PUBLIC_WHATSAPP_NUMBER=
```

Message dapat dibuat dari:
- order number.
- customer name.
- pertanyaan.
- status order.

Jangan menaruh nomor WhatsApp langsung di banyak file.

## 11. File Upload

Gunakan Supabase Storage.

Aturan:
- Batasi ukuran file.
- Validasi MIME type.
- Validasi extension.
- Gunakan folder berdasarkan user/order.
- Jangan menampilkan public bucket bila file bersifat privat.
- Admin mendapatkan akses melalui authorized URL/signed URL.

Contoh path:

```text
orders/{order_id}/{uuid}-{safe_file_name}
```

## 12. Authentication

Supabase Auth:
- Email/password sebagai minimum.
- Role disimpan di profile/database.
- Setelah login, server menentukan akses berdasarkan role.

Optional:
- Magic link tidak wajib untuk dummy.

## 13. State Management

Jangan menambah global state library sebelum diperlukan.

Gunakan:
- Server state dari database/server components.
- React Hook Form untuk form.
- URL search params untuk filter admin sederhana.
- Local state untuk UI transient state.
- Cart dapat disimpan di localStorage sebelum checkout jika ingin guest cart; saat checkout server tetap melakukan validasi ulang.

## 14. Error Handling

Kategori:
- Validation error: tampilkan dekat field.
- Unauthorized: 401/redirect login.
- Forbidden: 403.
- Not found: 404.
- Payment error: tampilkan retry path.
- Network/server error: tampilkan pesan singkat dan tindakan yang relevan.

Jangan menampilkan stack trace atau secret ke pengguna.

## 15. Performance

- Gunakan Server Components secara default.
- Client Component hanya untuk interaksi yang memang membutuhkan browser state.
- Optimalkan image.
- Lazy-load bagian yang berat.
- Hindari library besar untuk kebutuhan kecil.
- Jangan menambahkan animation library bila CSS sederhana sudah cukup.

## 16. Responsive Layout

Mobile:
```text
max width: full
padding: 16px
single column
sticky CTA bila relevan
```

Desktop:
```text
max width: 1200-1280px
centered container
grid/flex sesuai konteks
admin menggunakan sidebar
```

Gunakan breakpoint Tailwind standar dan mobile-first. Jangan membuat desain desktop lalu mengecilkannya.

## 17. Environment Variables

```env
NEXT_PUBLIC_SUPABASE_URL=
NEXT_PUBLIC_SUPABASE_ANON_KEY=

MIDTRANS_SERVER_KEY=
NEXT_PUBLIC_MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

NEXT_PUBLIC_WHATSAPP_NUMBER=
```

Jangan commit `.env.local`.

## 18. Deployment Shape

Untuk dummy:
- Next.js deployment di Vercel atau hosting Node-compatible.
- Supabase untuk database/auth/storage.
- Midtrans Sandbox untuk pembayaran.
- WhatsApp sebagai external deep link.

Production deployment tidak menjadi scope utama.

## 19. Non-Goals Teknis

Tidak perlu:
- Microservices.
- Redis.
- Message queue.
- Kubernetes.
- Event bus.
- Elasticsearch.
- Complex analytics.
- Real-time chat.
- WhatsApp API.
- Production payment settlement.

Arsitektur harus cukup untuk demo end-to-end tanpa overengineering.
