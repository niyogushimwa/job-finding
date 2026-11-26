<?php
declare(strict_types=1);

namespace App\Core;

/** Validated, randomised file uploads. Never trusts the browser-supplied MIME type. */
final class Upload
{
    public static function handle(?array $file, string $directory, array $allowedExtensions, ?int $maxSize = null): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('The file could not be uploaded (error ' . $file['error'] . ').');
        }
        $maxSize ??= (int) config('upload.max_size');
        if (($file['size'] ?? 0) > $maxSize || ($file['size'] ?? 0) === 0) {
            throw new \RuntimeException('The file must be smaller than ' . round($maxSize / 1048576, 1) . ' MB and not empty.');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload request.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($file['tmp_name']);
        $mimeMap = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'],
            'png' => ['image/png'], 'webp' => ['image/webp'],
        ];
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true) || !in_array($mime, $mimeMap[$extension] ?? [], true)) {
            throw new \RuntimeException('Unsupported file type. Only ' . implode(', ', $allowedExtensions) . ' files are accepted.');
        }
        $targetDir = rtrim(config('upload.dir'), '/') . '/' . trim($directory, '/');
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Could not create the upload directory.');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
            throw new \RuntimeException('Could not save the uploaded file.');
        }
        @chmod($targetDir . '/' . $filename, 0644);
        return $directory . '/' . $filename;
    }

    public static function url(?string $path): string
    {
        return $path ? url('uploads/' . $path) : '';
    }
}
