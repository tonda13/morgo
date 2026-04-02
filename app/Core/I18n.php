<?php

declare(strict_types=1);

namespace Morgo\Core;

class I18n
{
    /** @var array<string, array<string, string>> */
    private array $translations = [];

    private string $locale;

    public function __construct(string $locale = 'cs_CZ')
    {
        $this->locale = $locale;
    }

    public function loadDomain(string $domain, string $path): void
    {
        $file = rtrim($path, '/') . '/' . $this->locale . '.json';

        if (!file_exists($file)) {
            // Fallback na base locale (cs_CZ → cs)
            $base = explode('_', $this->locale)[0];
            $file = rtrim($path, '/') . '/' . $base . '.json';
        }

        if (!file_exists($file)) {
            return;
        }

        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            $this->translations[$domain] = array_merge(
                $this->translations[$domain] ?? [],
                $data
            );
        }
    }

    public function translate(string $string, string $domain = 'morgocms'): string
    {
        return $this->translations[$domain][$string] ?? $string;
    }

    public function translatePlural(string $singular, string $plural, int $count, string $domain = 'morgocms'): string
    {
        $key   = $singular . '|' . $plural;
        $forms = $this->translations[$domain][$key] ?? null;

        if ($forms === null) {
            return $count === 1 ? $singular : $plural;
        }

        $parts = explode('|', $forms);

        // Česká pluralizace: 1 / 2–4 / 5+
        if ($count === 1) {
            return sprintf($parts[0] ?? $singular, $count);
        } elseif ($count >= 2 && $count <= 4) {
            return sprintf($parts[1] ?? $parts[0] ?? $plural, $count);
        } else {
            return sprintf($parts[2] ?? $parts[1] ?? $parts[0] ?? $plural, $count);
        }
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
