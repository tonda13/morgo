<?php

declare(strict_types=1);

namespace Morgo\Core\Install;

class RequirementsChecker
{
    /** @return array<array{name: string, ok: bool, message: string}> */
    public function check(): array
    {
        $results = [];

        // PHP verze
        $results[] = [
            'name'    => 'PHP >= 8.2',
            'ok'      => version_compare(PHP_VERSION, '8.2.0', '>='),
            'message' => 'Aktuální verze: ' . PHP_VERSION,
        ];

        // Rozšíření
        $extensions = ['pdo_mysql', 'mbstring', 'gd', 'fileinfo', 'json', 'zip'];
        foreach ($extensions as $ext) {
            $results[] = [
                'name'    => "Rozšíření: {$ext}",
                'ok'      => extension_loaded($ext),
                'message' => extension_loaded($ext) ? 'Načteno' : 'Chybí — nainstalujte php8.2-' . $ext,
            ];
        }

        // Zapisovatelné adresáře
        $dirs = [
            'config/'          => BASE_PATH . '/config',
            'storage/logs/'    => BASE_PATH . '/storage/logs',
            'storage/cache/'   => BASE_PATH . '/storage/cache',
            'content/uploads/' => BASE_PATH . '/content/uploads',
        ];

        foreach ($dirs as $label => $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            $results[] = [
                'name'    => "Zapisovatelný: {$label}",
                'ok'      => is_writable($path),
                'message' => is_writable($path) ? 'Zapisovatelný' : "Nenastaveno: chmod 755 {$path}",
            ];
        }

        return $results;
    }

    public function allPassed(array $results): bool
    {
        return !in_array(false, array_column($results, 'ok'), true);
    }
}
