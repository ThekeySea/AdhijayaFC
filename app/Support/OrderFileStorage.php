<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderFileStorage
{
    private const BLOB_URL = 'https://blob.vercel-storage.com';

    private const BLOB_API_VERSION = '7';

    public function usesBlob(): bool
    {
        return config('filesystems.order_files.driver') === 'blob'
            && (bool) config('filesystems.order_files.token');
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

        return Storage::disk('local')->download($path, $downloadName);
    }

    private function blobToken(): string
    {
        return (string) config('filesystems.order_files.token');
    }

    private function blobAccess(): string
    {
        return (string) config('filesystems.order_files.access', 'private');
    }

    private function blobStoreId(): string
    {
        return (string) config('filesystems.order_files.store_id');
    }

    private function blobPublicUrl(string $pathname): string
    {
        $storeId = $this->blobStoreId();
        $access = $this->blobAccess();

        if ($storeId === '') {
            return self::BLOB_URL.'/'.$this->encodePath($pathname);
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
    private function blobHeaders(?string $mime = null): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->blobToken(),
            'x-api-version' => self::BLOB_API_VERSION,
            'access' => $this->blobAccess(),
        ];

        if ($mime !== null) {
            $headers['x-content-type'] = $mime;
            $headers['Content-Type'] = $mime;
        }

        return $headers;
    }

    private function blobPut(string $pathname, string $contents, string $mime): void
    {
        Http::withHeaders($this->blobHeaders($mime))
            ->withBody($contents, $mime)
            ->put(self::BLOB_URL.'/'.$this->encodePath($pathname))
            ->throw();
    }

    private function blobGet(string $pathname): string
    {
        $response = Http::withHeaders($this->blobHeaders())
            ->get($this->blobPublicUrl($pathname))
            ->throw();

        return $response->body();
    }

    private function blobExists(string $pathname): bool
    {
        $url = $this->blobPublicUrl($pathname);

        if (str_starts_with($url, self::BLOB_URL.'/')) {
            $response = Http::withHeaders($this->blobHeaders())
                ->head($url);

            return $response->successful();
        }

        $response = Http::withHeaders($this->blobHeaders())
            ->get(self::BLOB_URL, ['url' => $url]);

        return $response->successful();
    }

    private function blobDelete(string $pathname): void
    {
        Http::withHeaders($this->blobHeaders('application/json'))
            ->post(self::BLOB_URL.'/delete', [
                'urls' => [$this->blobPublicUrl($pathname)],
            ]);
    }

    private function encodePath(string $pathname): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $pathname)));
    }
}
