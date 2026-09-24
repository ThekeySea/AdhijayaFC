<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderFileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_file_upload_and_download(): void
    {
        $order = Order::factory()->create();
        $file = OrderFile::factory()->create(['order_id' => $order->id]);

        $this->post('/pesanan/'.$order->id.'/files')->assertRedirect('/login');
        $this->get('/pesanan/'.$order->id.'/files/'.$file->id.'/download')->assertRedirect('/login');
    }

    public function test_customer_can_upload_valid_file_to_own_order(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)->post('/pesanan/'.$order->id.'/files', [
            'files' => [UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(1, $order->files()->count());

        $stored = $order->files()->sole();
        $this->assertSame('dokumen.pdf', $stored->file_name);
        Storage::disk('local')->assertExists($stored->storage_path);
    }

    public function test_customer_cannot_upload_to_other_customer_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create();

        $this->actingAs($user)
            ->post('/pesanan/'.$order->id.'/files', [
                'files' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')],
            ])
            ->assertForbidden();

        $this->assertSame(0, $order->files()->count());
    }

    public function test_admin_cannot_upload_files(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paid()->create();

        $this->actingAs($admin)
            ->post('/pesanan/'.$order->id.'/files', [
                'files' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')],
            ])
            ->assertForbidden();
    }

    public function test_upload_rejects_disallowed_extension(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $user->id]);

        $this->actingAs($user)
            ->post('/pesanan/'.$order->id.'/files', [
                'files' => [UploadedFile::fake()->create('script.php', 10, 'application/x-php')],
            ])
            ->assertSessionHasErrors('files.0');

        $this->assertSame(0, $order->files()->count());
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $user->id]);

        $this->actingAs($user)
            ->post('/pesanan/'.$order->id.'/files', [
                'files' => [UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf')],
            ])
            ->assertSessionHasErrors('files.0');

        $this->assertSame(0, $order->files()->count());
    }

    public function test_upload_requires_at_least_one_file(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $user->id]);

        $this->actingAs($user)
            ->post('/pesanan/'.$order->id.'/files', [])
            ->assertSessionHasErrors('files');
    }

    public function test_cannot_upload_when_order_completed(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Completed,
        ]);

        $this->actingAs($user)
            ->post('/pesanan/'.$order->id.'/files', [
                'files' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')],
            ])
            ->assertSessionHas('status');

        $this->assertSame(0, $order->files()->count());
    }

    public function test_checkout_accepts_optional_file_upload(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 500]);

        $this->actingAs($user);
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);

        $this->post('/checkout', [
            'phone' => '6281234567890',
            'pickup_date' => now()->addDay()->toDateString(),
            'time_slot' => '09.00-10.00',
            'files' => [UploadedFile::fake()->create('print-file.pdf', 50, 'application/pdf')],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $order = Order::sole();
        $this->assertSame(1, $order->files()->count());
        $this->assertSame('print-file.pdf', $order->files()->sole()->file_name);
    }

    public function test_checkout_shows_file_upload_field(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user);
        $this->post('/keranjang', ['service_id' => $service->id, 'quantity' => 1]);

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Upload file (opsional)', false)
            ->assertSee('name="files[]"', false);
    }

    public function test_owner_can_download_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('orders/1/test.pdf', 'pdf-content');

        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $user->id]);
        $file = OrderFile::factory()->create([
            'order_id' => $order->id,
            'storage_path' => 'orders/1/test.pdf',
            'file_name' => 'test.pdf',
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id.'/files/'.$file->id.'/download')
            ->assertOk()
            ->assertDownload('test.pdf');
    }

    public function test_admin_can_download_customer_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('orders/1/test.pdf', 'pdf-content');

        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();
        $file = OrderFile::factory()->create([
            'order_id' => $order->id,
            'storage_path' => 'orders/1/test.pdf',
            'file_name' => 'test.pdf',
        ]);

        $this->actingAs($admin)
            ->get('/pesanan/'.$order->id.'/files/'.$file->id.'/download')
            ->assertOk()
            ->assertDownload('test.pdf');
    }

    public function test_other_customer_cannot_download_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('orders/1/test.pdf', 'pdf-content');

        $user = User::factory()->create();
        $order = Order::factory()->create();
        $file = OrderFile::factory()->create([
            'order_id' => $order->id,
            'storage_path' => 'orders/1/test.pdf',
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id.'/files/'.$file->id.'/download')
            ->assertForbidden();
    }

    public function test_order_detail_shows_files_section(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->pendingPayment()->create(['customer_id' => $user->id]);
        OrderFile::factory()->create([
            'order_id' => $order->id,
            'file_name' => 'skripsi-bab1.pdf',
        ]);

        $this->actingAs($user)
            ->get('/pesanan/'.$order->id)
            ->assertOk()
            ->assertSee('File pekerjaan')
            ->assertSee('skripsi-bab1.pdf')
            ->assertSee('Tambah file');
    }

    public function test_admin_order_detail_shows_customer_files(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();
        OrderFile::factory()->create([
            'order_id' => $order->id,
            'file_name' => 'desain-banner.png',
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('File pelanggan')
            ->assertSee('desain-banner.png');
    }
}
