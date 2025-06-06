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

    protected function detectFileType(string $extension): string
    {
        $extension = strtolower($extension);

        $defaultTypes = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm'],
            'audio' => ['mp3', 'wav', 'ogg', 'aac'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        ];

        // Merge with user-defined extensions from config
        $customTypes = config('chat.media_types', []);

        $mergedTypes = array_merge_recursive($defaultTypes, $customTypes);

        foreach ($mergedTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return 'file'; // fallback
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
                $ext = strtolower($file->extension());
                $type = $this->detectFileType($ext);

                $response[] = [
                    'file' => $uploaded['data'],
                    'ext' => $ext,
                    'type' => $type,
                ];
            }
        }

        return $this->success($response);
    }
}
