<?php

declare(strict_types=1);

/**
 * Vygeneruje kompletní SEO meta tagy pro aktuální stránku.
 * Volej v <head> tématu místo ručního <title> tagu.
 */
function the_seo_tags(): void
{
    global $morgoPage;

    if (!$morgoPage) {
        $siteName = sp_bloginfo('name');
        echo '<title>' . esc_html($siteName) . '</title>' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($siteName) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<link rel="canonical" href="' . esc_attr(site_url('/')) . '">' . "\n";
        return;
    }

    $siteName = sp_bloginfo('name');
    $title    = (!empty($morgoPage->meta_title))
        ? $morgoPage->meta_title
        : ($morgoPage->title . ' — ' . $siteName);
    $desc = $morgoPage->meta_description ?? '';
    $url  = site_url('/' . ltrim($morgoPage->slug, '/'));

    echo '<title>' . esc_html($title) . '</title>' . "\n";

    if ($desc !== '') {
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    }

    // Open Graph
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_attr($url) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($siteName) . '">' . "\n";

    if ($desc !== '') {
        echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    }

    // Canonical
    echo '<link rel="canonical" href="' . esc_attr($url) . '">' . "\n";
}
