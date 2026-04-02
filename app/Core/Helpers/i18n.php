<?php

declare(strict_types=1);

function __(string $string, string $domain = 'morgocms'): string
{
    global $morgoContainer;
    if ($morgoContainer === null) {
        return $string;
    }
    try {
        $i18n = $morgoContainer->get(\Morgo\Core\I18n::class);
        return $i18n->translate($string, $domain);
    } catch (\Throwable) {
        return $string;
    }
}

function _e(string $string, string $domain = 'morgocms'): void
{
    echo esc_html(__($string, $domain));
}

function __f(string $string, string $domain, mixed ...$args): string
{
    return sprintf(__($string, $domain), ...$args);
}

function _n(string $singular, string $plural, int $count, string $domain = 'morgocms'): string
{
    global $morgoContainer;
    if ($morgoContainer === null) {
        return $count === 1 ? $singular : $plural;
    }
    try {
        $i18n = $morgoContainer->get(\Morgo\Core\I18n::class);
        return $i18n->translatePlural($singular, $plural, $count, $domain);
    } catch (\Throwable) {
        return $count === 1 ? $singular : $plural;
    }
}

function sp_load_textdomain(string $domain, string $path): void
{
    global $morgoContainer;
    if ($morgoContainer === null) {
        return;
    }
    try {
        $i18n = $morgoContainer->get(\Morgo\Core\I18n::class);
        $i18n->loadDomain($domain, $path);
        sp_do_action('sp_load_textdomain', $domain, $path);
    } catch (\Throwable) {
        // Tiché selhání — i18n není kritické
    }
}
