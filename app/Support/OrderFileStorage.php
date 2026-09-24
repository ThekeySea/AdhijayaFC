<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderFileStorage
{
    private const BLOB_API_URL = 'https://vercel.com/api/blob';

    private const BLOB_API_VERSION = '12';

    public function usesBlob(): bool
    {
        return config('filesystems.order_files.driver') === 'blob'
            && $this->resolveAuthToken() !== '';
    }

    public function storeUploadedFile(UploadedFile $file, string $directory): ?string
    {
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
        $extension = strtolower($file->getClientOriginalExtension());
        $pathname = $directory.'/'.Str::uuid().'-'.$safeName.'.'.$extension;

        if ($this->usesBlob()) {
            $this->blobPut($pathname, (string) $file->get(), $file->getMimeType() ?: 'application/octet-stream');

            return $pathname;
        }

        $path = $file->storeAs($directory, basename($pathname), 'local');

        return $path === false ? null : $path;
    }

    public function exists(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($this->usesBlob()) {
            return $this->blobExists($path);
        }

        return Storage::disk('local')->exists($path);
    }

    public function delete(string $path): void
    {
        if ($path === '') {
            return;
        }

        if ($this->usesBlob()) {
            $this->blobDelete($path);

            return;
        }

        Storage::disk('local')->delete($path);
    }

    public function move(string $from, string $to): bool
    {
        if ($from === '' || $to === '') {
            return false;
        }

        if ($this->usesBlob()) {
            $contents = $this->blobGet($from);
            $this->blobPut($to, $contents, 'application/octet-stream');
            $this->blobDelete($from);

            return true;
        }

        return (bool) Storage::disk('local')->move($from, $to);
    }

    public function download(string $path, string $downloadName, string $mime): StreamedResponse
    {
        if ($this->usesBlob()) {
            $contents = $this->blobGet($path);

            return response()->stream(function () use ($contents): void {
                echo $contents;
            }, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="'.addslashes($downloadName).'"',
                'Content-Length' => (string) strlen($contents),
            ]);
        }

        return Storage::disk('local')->download($path, $downloadName, ['Content-Type' => $mime]);
    }

    /**
     * Prioritas: BLOB_READ_WRITE_TOKEN, lalu VERCEL_OIDC_TOKEN (env), lalu header request OIDC (container runtime).
     */
    private function resolveAuthToken(): string
    {
        $readWrite = trim((string) config('filesystems.order_files.token'));

        if ($readWrite !== '') {
            return $readWrite;
        }

        $oidc = trim((string) config('filesystems.order_files.oidc_token'));

        if ($oidc !== '') {
            return $oidc;
        }

        return trim((string) request()->headers->get('x-vercel-oidc-token', ''));
    }

    private function blobAccess(): string
    {
        return (string) config('filesystems.order_files.access', 'private');
    }

    private function blobStoreId(): string
    {
        return (string) config('filesystems.order_files.store_id');
    }

    private function normalizedStoreId(): string
    {
        $storeId = trim($this->blobStoreId());

        return str_starts_with($storeId, 'store_')
            ? substr($storeId, 6)
            : $storeId;
    }

    private function blobPublicUrl(string $pathname): string
    {
        $storeId = $this->normalizedStoreId();
        $access = $this->blobAccess();

        if ($storeId === '') {
            return self::BLOB_API_URL;
        }

        return sprintf(
            'https://%s.%s.blob.vercel-storage.com/%s',
            $storeId,
            $access,
            $this->encodePath($pathname),
        );
    }

    /**
     * @return array<string, string>
     */
    private function blobControlHeaders(?string $mime = null): array
    {
        $storeId = $this->normalizedStoreId();

        $headers = [
            'Authorization' => 'Bearer '.$this->resolveAuthToken(),
            'x-api-version' => self::BLOB_API_VERSION,
            'x-api-blob-request-id' => $storeId.':'.now()->getTimestamp().':'.bin2hex(random_bytes(6)),
            'x-api-blob-request-attempt' => '0',
        ];

        if ($storeId !== '') {
            $headers['x-vercel-blob-store-id'] = $storeId;
        }

        if ($mime !== null) {
            $headers['x-content-type'] = $mime;
            $headers['x-vercel-blob-access'] = $this->blobAccess();
            $headers['Content-Type'] = $mime;
        }

        return $headers;
    }

    /**
     * @return array<string, string>
     */
    private function blobObjectHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->resolveAuthToken(),
        ];
    }

    private function blobPut(string $pathname, string $contents, string $mime): void
    {
        Http::withHeaders($this->blobControlHeaders($mime))
            ->withBody($contents, $mime)
            ->put(self::BLOB_API_URL.'/?'.http_build_query(['pathname' => $pathname]))
            ->throw();
    }

    private function blobGet(string $pathname): string
    {
        $url = $this->blobPublicUrl($pathname);
        $response = Http::withHeaders($this->blobObjectHeaders())
            ->get($url.'?'.http_build_query(['cache' => '0']));

        if ($response->successful()) {
            return $response->body();
        }

        throw new \RuntimeException('Blob download failed with status '.$response->status());
    }

    private function blobExists(string $pathname): bool
    {
        $control = Http::withHeaders($this->blobControlHeaders())
            ->get(self::BLOB_API_URL.'?'.http_build_query(['url' => $pathname]));

        if ($control->successful()) {
            return true;
        }

        $url = $this->blobPublicUrl($pathname);
        $head = Http::withHeaders($this->blobObjectHeaders())->head($url);

        return $head->successful();
    }

    private function blobDelete(string $pathname): void
    {
        Http::withHeaders($this->blobControlHeaders('application/json'))
            ->post(self::BLOB_API_URL.'/delete', [
                'urls' => [$this->blobPublicUrl($pathname)],
            ]);
    }

    private function encodePath(string $pathname): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $pathname)));
    }
}
