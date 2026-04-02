<?php

declare(strict_types=1);

namespace Morgo\Infrastructure\Storage;

class LocalMediaStorage
{
    private string $uploadDir;

    public function __construct()
    {
        $this->uploadDir = BASE_PATH . '/content/uploads';
    }

    /**
     * Uloží soubor z dočasné cesty a vrátí relativní cestu (pro DB).
     */
    public function storeFromPath(string $tmpPath, string $originalName, string $mimeType): array
    {
        $yearMonth = date('Y/m');
        $dir       = $this->uploadDir . '/' . $yearMonth;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext      = $this->extensionForMime($mimeType);
        $hash     = bin2hex(random_bytes(16));
        $filename = $hash . '.' . $ext;
        $filepath = $yearMonth . '/' . $filename;
        $fullPath = $dir . '/' . $filename;

        if (!rename($tmpPath, $fullPath)) {
            copy($tmpPath, $fullPath);
            unlink($tmpPath);
        }

        return [
            'filename'  => $originalName,
            'filepath'  => $filepath,
            'file_size' => filesize($fullPath),
        ];
    }

    /**
     * Uloží nahraný soubor (move_uploaded_file) a vrátí relativní cestu (pro DB).
     */
    public function store(array $uploadedFile, string $mimeType): array
    {
        $yearMonth = date('Y/m');
        $dir       = $this->uploadDir . '/' . $yearMonth;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Přejmenovat na hash
        $ext      = $this->extensionForMime($mimeType);
        $hash     = bin2hex(random_bytes(16));
        $filename = $hash . '.' . $ext;
        $filepath = $yearMonth . '/' . $filename;
        $fullPath = $dir . '/' . $filename;

        if (!move_uploaded_file($uploadedFile['tmp_name'], $fullPath)) {
            throw new \RuntimeException("Nepodařilo se uložit soubor.");
        }

        return [
            'filename'  => $uploadedFile['name'],
            'filepath'  => $filepath,
            'file_size' => filesize($fullPath),
        ];
    }

    public function delete(string $filepath): void
    {
        $fullPath = $this->uploadDir . '/' . $filepath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    private function extensionForMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
            'image/webp'      => 'webp',
            'application/pdf' => 'pdf',
            default           => 'bin',
        };
    }
}
