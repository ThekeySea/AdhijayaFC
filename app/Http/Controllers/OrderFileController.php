<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderFile;
use App\Support\OrderFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderFileController extends Controller
{
    /**
     * @var list<string>
     */
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'txt', 'zip'];

    public const MAX_FILE_KB = 4096;

    public const MAX_FILES = 5;

    public function __construct(
        private readonly OrderFileStorage $storage,
    ) {}

    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_if($request->user()->isAdmin(), 403);

        if (! $this->canUpload($order)) {
            return back()->with('status', 'File hanya bisa diunggah saat pesanan masih diproses.');
        }

        $validated = $request->validate([
            'files' => ['required', 'array', 'max:'.self::MAX_FILES],
            'files.*' => [
                'file',
                'max:'.self::MAX_FILE_KB,
                'mimes:'.implode(',', self::ALLOWED_EXTENSIONS),
            ],
        ], [
            'files.required' => 'Pilih minimal satu file untuk diunggah.',
            'files.array' => 'Format file tidak valid.',
            'files.max' => 'Maksimal '.self::MAX_FILES.' file per unggahan.',
        ]);

        $existing = $order->files()->count();
        $incoming = count($validated['files']);

        if ($existing + $incoming > 10) {
            return back()->with('status', 'Maksimal 10 file untuk satu pesanan.');
        }

        foreach ($validated['files'] as $file) {
            $stored = $this->storage->storeUploadedFile($file, 'orders/'.$order->id);

            if ($stored === null) {
                continue;
            }

            OrderFile::create([
                'order_id' => $order->id,
                'file_name' => $file->getClientOriginalName(),
                'storage_path' => $stored,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('status', $incoming.' file berhasil diunggah.');
    }

    public function download(Request $request, Order $order, OrderFile $file): Response|StreamedResponse
    {
        abort_unless($file->order_id === $order->id, 404);
        abort_unless(
            $order->customer_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        $exists = $file->existsOnDisk();
        $storage = $this->storage;

        if (! $exists) {
            $response = response('missing', 404, [
                'X-Dbg-Exists' => '0',
                'X-Dbg-Path' => (string) $file->storage_path,
                'X-Dbg-UsesBlob' => $storage->usesBlob() ? '1' : '0',
                'X-Dbg-Driver' => (string) config('filesystems.order_files.driver'),
                'X-Dbg-TokenLen' => (string) strlen((string) config('filesystems.order_files.token')),
                'X-Dbg-OidcLen' => (string) strlen((string) config('filesystems.order_files.oidc_token')),
                'X-Dbg-Store' => (string) config('filesystems.order_files.store_id'),
                'X-Dbg-Access' => (string) config('filesystems.order_files.access'),
                'X-Dbg-LocalExists' => Storage::disk('local')->exists((string) $file->storage_path) ? '1' : '0',
            ]);

            return $response;
        }

        return $this->storage->download($file->storage_path, $file->file_name, $file->mime_type ?: 'application/octet-stream');
    }

    private function canUpload(Order $order): bool
    {
        return in_array($order->status, [
            OrderStatus::PendingPayment,
            OrderStatus::Paid,
            OrderStatus::Processing,
            OrderStatus::PaymentFailed,
        ], true);
    }
}
