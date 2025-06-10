<?php

namespace Metafroliclabs\LaravelChat\Services\Core;

use Illuminate\Support\Facades\Storage;
use Metafroliclabs\LaravelChat\Exceptions\ChatException;
use Illuminate\Http\UploadedFile;

class FileService
{
    /**
     * The configured filesystem disk to store files.
     *
     * @var string
     */
    protected string $disk;

    /**
     * The default upload folder.
     *
     * @var string
     */
    protected string $folder;

    /**
     * The default prefix for file names.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Initialize the FileService with configuration values.
     */
    public function __construct()
    {
        $this->disk = config('chat.file.disk');
        $this->folder = config('chat.file.upload_folder');
        $this->prefix = config('chat.file.default_prefix');
    }

    /**
     * Generate a unique filename using prefix, timestamp, and key.
     *
     * @param  string  $prefix
     * @param  string  $extension
     * @param  int  $key
     * @return string
     */
    protected function generateFilename(string $prefix, string $extension, int $key): string
    {
        $timestamp = now()->format('YmdHis');
        return "{$prefix}_{$timestamp}_{$key}.{$extension}";
    }

    /**
     * Detect the file type based on file extension.
     * Falls back to 'file' if the type is unknown.
     *
     * @param  string  $extension
     * @return string
     */
    protected function detectFileType(string $extension): string
    {
        $extension = strtolower($extension);

        $defaultTypes = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm'],
            'audio' => ['mp3', 'wav', 'ogg', 'aac'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        ];

        $customTypes = config('chat.file.types', []);
        $mergedTypes = array_merge_recursive($defaultTypes, $customTypes);

        foreach ($mergedTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return 'file';
    }

    /**
     * Upload a single file to the configured storage disk.
     *
     * @param  UploadedFile  $file
     * @param  string|null  $prefix
     * @param  string|null  $folder
     * @param  int  $key
     * @return array
     *
     * @throws ChatException
     */
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
            return [
                'status' => true,
                'data' => Storage::url($path)
            ];
        }

        throw new ChatException("File could not be uploaded");
    }

    /**
     * Upload multiple files and return their URLs with type info.
     *
     * @param  UploadedFile[]  $files
     * @param  string|null  $prefix
     * @param  string|null  $folder
     * @return array
     *
     * @throws ChatException
     */
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

        return [
            'status' => true,
            'data' => $response
        ];
    }
}
