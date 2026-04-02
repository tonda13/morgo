<?php

declare(strict_types=1);

function esc_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function esc_attr(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function esc_url(string $url): string
{
    $url = trim($url);
    // Povolené protokoly
    if (!preg_match('/^(https?:\/\/|\/|#|\?)/i', $url)) {
        return '';
    }
    return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function esc_js(string $value): string
{
    return addslashes($value);
}

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . esc_attr(csrf_token()) . '">';
}

function csrf_rotate(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}
