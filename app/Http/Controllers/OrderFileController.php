<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderFileController extends Controller
{
    /**
     * @var list<string>
     */
    public const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'application/zip',
    ];

    /**
     * @var list<string>
     */
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'txt', 'zip'];

    public const MAX_FILE_KB = 5120;

    public const MAX_FILES = 5;

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
            $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
            $extension = strtolower($file->getClientOriginalExtension());
            $stored = $file->storeAs(
                'orders/'.$order->id,
                Str::uuid().'-'.$safeName.'.'.$extension,
                'local'
            );

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

    public function download(Request $request, Order $order, OrderFile $file): StreamedResponse
    {
        abort_unless($file->order_id === $order->id, 404);
        abort_unless(
            $order->customer_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );

        abort_unless($file->existsOnDisk(), 404);

        return Storage::disk('local')->download($file->storage_path, $file->file_name);
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
