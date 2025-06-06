<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

use Illuminate\Support\Facades\Storage;
use Metafroliclabs\LaravelChat\Exceptions\ChatException;

class FileService
{
    protected string $disk;
    protected string $folder;
    protected string $prefix;

    public function __construct()
    {
        $this->disk = config('chat.file.disk');
        $this->folder = config('chat.file.upload_folder');
        $this->prefix = config('chat.file.default_prefix');
    }

    protected function success($data = []): array
    {
        return [
            'status' => true,
            'data' => $data,
        ];
    }

    protected function successMessage(string $message): array
    {
        return [
            'status' => true,
            'message' => $message,
        ];
    }

    protected function generateFilename(string $prefix, string $extension, int $key): string
    {
        $timestamp = now()->format('YmdHis');
        return "{$prefix}_{$timestamp}_{$key}.{$extension}";
    }

    public function uploadFile($file, ?string $prefix = null, ?string $folder = null, int $key = 0): array
    {
        $prefix = $prefix ?? $this->prefix;
        $folder = $folder ?? $this->folder;
        $directory = "{$folder}";

        if (!Storage::disk($this->disk)->exists($directory)) {
            Storage::disk($this->disk)->makeDirectory($directory);
        }

        $filename = $this->generateFilename($prefix, $file->extension(), $key);
        $path = $file->storeAs($directory, $filename, $this->disk);

        if ($path) {
            return $this->success(Storage::url($path));
        }

        throw new ChatException("File could not be uploaded");
    }

    public function uploadMultipleFiles(array $files, ?string $prefix = null, ?string $folder = null): array
    {
        $response = [];

        foreach ($files as $key => $file) {
            $uploaded = $this->uploadFile($file, $prefix, $folder, $key);
            if ($uploaded['status']) {
                $response[] = [
                    'path' => $uploaded['data'],
                    'type' => $file->extension()
                ];
            }
        }

        return $this->success($response);
    }
}
