<?php

declare(strict_types=1);

function __(string $text, string $domain = 'morgocms'): string
{
    global $morgoContainer;
    if ($morgoContainer && $morgoContainer->has(\Morgo\Core\I18n::class)) {
        return $morgoContainer->get(\Morgo\Core\I18n::class)->translate($text, $domain);
    }
    return $text;
}

function _e(string $text, string $domain = 'morgocms'): void
{
    echo __(esc_html($text), $domain);
}

function __f(string $text, string $domain, mixed ...$args): string
{
    global $morgoContainer;
    if ($morgoContainer && $morgoContainer->has(\Morgo\Core\I18n::class)) {
        return $morgoContainer->get(\Morgo\Core\I18n::class)->translateFormat($text, $domain, ...$args);
    }
    return sprintf($text, ...$args);
}

function _n(string $singular, string $plural, int $count, string $domain = 'morgocms'): string
{
    global $morgoContainer;
    if ($morgoContainer && $morgoContainer->has(\Morgo\Core\I18n::class)) {
        return $morgoContainer->get(\Morgo\Core\I18n::class)->translatePlural($singular, $plural, $count, $domain);
    }
    return $count === 1 ? $singular : $plural;
}

function sp_load_textdomain(string $domain, string $langPath): void
{
    global $morgoContainer;
    if ($morgoContainer && $morgoContainer->has(\Morgo\Core\I18n::class)) {
        $morgoContainer->get(\Morgo\Core\I18n::class)->loadDomain($domain, $langPath);
    }
}
