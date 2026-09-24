<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceOption;
use App\Support\Cart;
use App\Support\OrderFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly OrderFileStorage $storage,
    ) {}

    public function index(): View
    {
        $lines = Cart::lines();

        return view('cart.index', [
            'lines' => $lines,
            'subtotal' => (float) $lines->sum('subtotal'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'detail' => ['nullable', 'string', 'max:500'],
            'options' => ['nullable'],
            ...$this->fileRules(),
        ]);

        $service = Service::query()
            ->active()
            ->with(['activeOptionGroups.options', 'activeOptions'])
            ->findOrFail($validated['service_id']);

        $quantity = (int) $validated['quantity'];
        $optionIds = $this->flattenOptionIds($request);
        $files = $request->file('files') ?? [];

        $this->assertMinQuantity($service, $quantity);
        $this->assertRequiredOptionGroups($service, $optionIds);
        $this->assertFileRequirement($service, $files);

        Cart::add(
            $service,
            $quantity,
            trim((string) ($validated['detail'] ?? '')),
            $optionIds,
            $this->storeFiles($files),
        );

        return redirect()
            ->route('cart.index')
            ->with('status', $service->name.' ditambahkan ke keranjang.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->load(['activeOptionGroups.options', 'activeOptions']);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'detail' => ['nullable', 'string', 'max:500'],
            'options' => ['nullable'],
            ...$this->fileRules(),
        ]);

        $quantity = (int) $validated['quantity'];
        $hasOptionsKey = $request->has('options') || $request->has('options[]');
        $optionIds = $hasOptionsKey ? $this->flattenOptionIds($request) : null;
        $incomingFiles = $request->file('files') ?? [];

        $this->assertMinQuantity($service, $quantity);

        if ($optionIds !== null) {
            $this->assertRequiredOptionGroups($service, $optionIds);
        }

        $files = null;

        if ($incomingFiles !== []) {
            $existing = Cart::items()[$service->id]['files'] ?? [];
            $files = array_values(array_merge($existing, $this->storeFiles($incomingFiles)));
            $this->assertFileRequirement($service, [], $files);
        } elseif ($request->has('files')) {
            $this->assertFileRequirement($service, []);
        }

        Cart::update(
            $service,
            $quantity,
            trim((string) ($validated['detail'] ?? '')),
            $optionIds,
            $files,
        );

        return redirect()
            ->route('cart.index')
            ->with('status', 'Keranjang diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        Cart::remove($service->id);

        return redirect()
            ->route('cart.index')
            ->with('status', 'Item dihapus dari keranjang.');
    }

    private function assertMinQuantity(Service $service, int $quantity): void
    {
        if ($service->min_quantity !== null && $quantity < $service->min_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Minimal pembelian untuk '.$service->name.' adalah '.$service->min_quantity.' '.$service->unit.'.',
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function fileRules(): array
    {
        return [
            'files' => ['nullable', 'array', 'max:'.OrderFileController::MAX_FILES],
            'files.*' => [
                'file',
                'max:'.OrderFileController::MAX_FILE_KB,
                'mimes:'.implode(',', OrderFileController::ALLOWED_EXTENSIONS),
            ],
        ];
    }

    /**
     * Flatten radio (options[groupId]) and checkbox (options[]) payloads into id list.
     *
     * @return list<int>
     */
    private function flattenOptionIds(Request $request): array
    {
        $raw = $request->input('options', []);

        if (! is_array($raw)) {
            return [];
        }

        $ids = [];

        foreach ($raw as $value) {
            if (is_array($value)) {
                foreach ($value as $nested) {
                    $ids[] = (int) $nested;
                }

                continue;
            }

            $ids[] = (int) $value;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * @param  list<int>  $selectedIds
     */
    private function assertRequiredOptionGroups(Service $service, array $selectedIds): void
    {
        $selected = array_map('intval', $selectedIds);

        foreach ($service->activeOptionGroups as $group) {
            if (! $group->is_required) {
                continue;
            }

            $chosen = $group->options->filter(fn (ServiceOption $option) => in_array($option->id, $selected, true));

            if ($chosen->isEmpty()) {
                throw ValidationException::withMessages([
                    'options' => 'Pilihan "'.$group['name'].'" wajib diisi.',
                ]);
            }

            if ($group->isSingle() && $chosen->count() > 1) {
                throw ValidationException::withMessages([
                    'options' => 'Pilihan "'.$group['name'].'" hanya boleh satu.',
                ]);
            }
        }
    }

    /**
     * @param  array<int, UploadedFile>  $incoming
     * @param  list<array{path: string, name: string, mime: string, size: int}>|null  $existing
     */
    private function assertFileRequirement(Service $service, array $incoming, ?array $existing = null): void
    {
        if ($service->requiresFile() && $incoming === [] && ($existing === null || $existing === [])) {
            throw ValidationException::withMessages([
                'files' => 'File wajib diunggah untuk layanan ini.',
            ]);
        }

        if (! $service->allowsFile() && $incoming !== []) {
            throw ValidationException::withMessages([
                'files' => 'Layanan ini tidak menerima file unggahan.',
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return list<array{path: string, name: string, mime: string, size: int}>
     */
    private function storeFiles(array $files): array
    {
        $stored = [];

        foreach ($files as $file) {
            $path = $this->storage->storeUploadedFile($file, 'cart-uploads');

            if ($path === null) {
                continue;
            }

            $stored[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize() ?: 0,
            ];
        }

        return $stored;
    }
}
