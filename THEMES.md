# MorgoCMS — Průvodce tvorbou tématu

Kompletní dokumentace pro vývojáře témat.

---

## 1. Struktura tématu

Každé téma je adresář v `themes/` s následující strukturou:

```
themes/moje-tema/
├── theme.php           # Metadata tématu (povinné)
├── functions.php       # Bootstrap: registrace menu, widgetů, CSS/JS
├── header.php          # Hlavička stránky (od <!DOCTYPE> do otevřeného <body>)
├── footer.php          # Patička stránky (uzavření </body></html>)
├── home.php            # Šablona úvodní stránky
├── page.php            # Výchozí šablona stránky
├── index.php           # Povinný fallback (musí existovat)
├── 404.php             # Stránka nenalezena
├── sidebar.php         # Postranní panel (volitelné)
├── templates/          # Pojmenované šablony volitelné z admin UI
│   ├── full-width.php  # Template Name: Celá šířka
│   └── landing.php     # Template Name: Landing Page
└── assets/
    ├── css/
    │   └── style.css
    └── js/
        └── main.js
```

Jediný povinný soubor je `index.php`. Bez něj ThemeEngine vyhodí výjimku.

---

## 2. theme.php — metadata tématu

Soubor `theme.php` vrací pole s metadaty. Je povinný pro načtení tématu.

```php
<?php

return [
    'name'        => 'Moje Téma',
    'version'     => '1.0.0',
    'author'      => 'Jméno Autora',
    'description' => 'Stručný popis tématu.',
    'parent'      => null,  // null = samostatné téma; 'default' = child theme
];
```

---

## 3. functions.php — bootstrap tématu

Soubor `functions.php` se načítá při každém požadavku. Slouží k registraci navigací, widget oblastí, CSS/JS assetů a zapnutí volitelných funkcí jádra.

### Registrace navigačních menu

```php
sp_add_action('sp_init', function () {
    sp_register_nav_menus([
        'primary' => 'Hlavní navigace',
        'footer'  => 'Patičková navigace',
    ]);
});
```

Klíče pole (`primary`, `footer`) jsou identifikátory lokací — ty se pak používají při volání `the_menu('primary')` v šablonách a v admin UI při přiřazování menu.

### Registrace widget oblastí

```php
sp_add_action('sp_init', function () {
    sp_register_widget_area('sidebar',        'Postranní panel');
    sp_register_widget_area('footer-widgets', 'Widgety v patičce');
});
```

### Načítání CSS a JS

Styly se registrují a tisknou v hooku `sp_head`, skripty v hooku `sp_footer`:

```php
// CSS — načíst v <head>
sp_add_action('sp_head', function () {
    sp_enqueue_style('moje-tema', theme_url('assets/css/style.css'));
    sp_print_styles();
});

// JS — načíst před </body>
sp_add_action('sp_footer', function () {
    sp_enqueue_script('moje-tema-js', theme_url('assets/js/main.js'));
    sp_print_scripts(true);
});
```

### Zapnutí podpory volitelných funkcí

```php
sp_add_theme_support('custom-fields');    // Custom Fields na stránkách
sp_add_theme_support('page-thumbnails'); // Náhledové obrázky
```

---

## 4. Hierarchie šablon

ThemeEngine hledá soubor šablony v tomto pevném pořadí (nejdříve v child tématu, pak v parent):

### Pro běžné stránky

1. `page-{slug}.php` — šablona pro konkrétní slug (např. `page-kontakt.php`)
2. `page-{id}.php` — šablona pro konkrétní ID (např. `page-5.php`)
3. Pojmenovaná šablona z admin UI (např. `templates/full-width.php`)
4. `page.php` — výchozí šablona stránky
5. `index.php` — povinný fallback

### Pro homepage

1. `home.php`
2. `page.php`
3. `index.php`

### Pojmenované šablony

Soubor v `templates/` se zobrazí v admin UI jako volitelná šablona, pokud obsahuje komentář `Template Name:`:

```php
<?php
// Template Name: Celá šířka
?>
```

Název za dvojtečkou se zobrazí v selectboxu při editaci stránky.

---

## 5. Dostupné helper funkce

### Obsah stránky

| Funkce | Popis |
|--------|-------|
| `the_title()` | Vypíše titulek aktuální stránky (přes `echo`) |
| `get_the_title()` | Vrátí titulek jako string |
| `the_content()` | Vypíše obsah aktuální stránky (vyrendrovaný HTML) |
| `get_the_content()` | Vrátí obsah jako string |
| `the_seo_tags()` | Vypíše `<title>`, meta description a Open Graph tagy |
| `get_custom_field(string $key)` | Vrátí hodnotu vlastního pole stránky |

### Navigace a struktura

| Funkce | Popis |
|--------|-------|
| `get_header()` | Načte a vykreslí `header.php` |
| `get_footer()` | Načte a vykreslí `footer.php` |
| `the_menu(string $location)` | Vykreslí menu přiřazené k dané lokaci |

### Hooky pro head a footer

| Funkce | Popis |
|--------|-------|
| `sp_head()` | Spustí hook `sp_head` — vykreslí zaregistrované styly a meta tagy |
| `sp_footer()` | Spustí hook `sp_footer` — vykreslí zaregistrované skripty |

Tyto funkce volejte v `header.php` a `footer.php`:

```php
// header.php — v <head>
<?php sp_head(); ?>

// footer.php — před </body>
<?php sp_footer(); ?>
```

### URL a informace o webu

| Funkce | Popis |
|--------|-------|
| `sp_bloginfo(string $key)` | Vrátí info o webu: `'name'`, `'url'`, `'description'` |
| `site_url(string $path = '')` | Absolutní URL webu, volitelně s cestou |
| `theme_url(string $path = '')` | Absolutní URL adresáře aktivního tématu |

### Nastavení

| Funkce | Popis |
|--------|-------|
| `get_option(string $key, mixed $default = null)` | Načte volbu z DB |
| `update_option(string $key, mixed $value)` | Uloží volbu do DB |

### Bezpečnostní escape funkce

Vždy escapujte výstup do HTML:

| Funkce | Použití |
|--------|---------|
| `esc_html(string $text)` | Escapuje HTML entity — pro textový obsah |
| `esc_attr(string $text)` | Escapuje atributy HTML tagů |
| `esc_url(string $url)` | Sanitizuje URL |

### Hook systém

| Funkce | Popis |
|--------|-------|
| `sp_add_action(string $hook, callable $cb, int $priority = 10)` | Zaregistruje callback na action hook |
| `sp_do_action(string $hook, mixed ...$args)` | Spustí action hook |
| `sp_add_filter(string $hook, callable $cb, int $priority = 10)` | Zaregistruje callback na filter hook |
| `sp_apply_filters(string $hook, mixed $value, mixed ...$args)` | Aplikuje filter hook a vrátí upravenou hodnotu |
| `sp_remove_action(string $hook, callable $cb)` | Odebere callback z action hooku |
| `sp_remove_filter(string $hook, callable $cb)` | Odebere callback z filter hooku |

---

## 6. Aktivace tématu

### Přes administraci

1. Přejděte na **Admin → Témata**
2. Klikněte na **Aktivovat** u zvoleného tématu

### Přes CLI

```bash
php bin/sp theme:activate moje-tema
```

---

## 7. Child témata

Child téma přepisuje vybrané soubory parent tématu, aniž byste museli kopírovat celé téma.

### Nastavení

V `theme.php` child tématu uveďte slug parent tématu:

```php
<?php
return [
    'name'   => 'Moje Child Téma',
    'parent' => 'default',   // slug parent tématu
];
```

### Pořadí načítání

- **functions.php:** nejprve se načte `functions.php` parent tématu, pak child tématu
- **Šablony:** ThemeEngine hledá soubor nejdříve v child tématu, teprve pak v parent

Takže pokud v child tématu vytvoříte `page.php`, přepíše `page.php` z parent tématu. Soubory, které v child tématu neexistují, se automaticky berou z parent tématu.

---

## 8. Kompletní minimální příklad

Funkční minimální téma s pěti soubory.

### theme.php

```php
<?php

return [
    'name'        => 'Minimální Téma',
    'version'     => '1.0.0',
    'author'      => 'Váš Název',
    'description' => 'Minimální funkční téma pro MorgoCMS.',
    'parent'      => null,
];
```

### functions.php

```php
<?php

declare(strict_types=1);

sp_add_action('sp_init', function () {
    sp_register_nav_menus([
        'primary' => 'Hlavní navigace',
    ]);
});

sp_add_action('sp_head', function () {
    sp_enqueue_style('minimal-theme', theme_url('assets/css/style.css'));
    sp_print_styles();
});

sp_add_action('sp_footer', function () {
    sp_print_scripts(true);
});

sp_add_theme_support('custom-fields');
```

### header.php

```php
<!DOCTYPE html>
<html lang="<?php echo esc_attr(get_option('site_language', 'cs')); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php the_seo_tags(); ?>
    <?php sp_head(); ?>
</head>
<body>
    <header>
        <a href="<?php echo esc_url(site_url()); ?>">
            <?php echo esc_html(sp_bloginfo('name')); ?>
        </a>
        <nav>
            <?php the_menu('primary'); ?>
        </nav>
    </header>
```

### footer.php

```php
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(sp_bloginfo('name')); ?></p>
    </footer>

    <?php sp_footer(); ?>
</body>
</html>
```

### index.php

```php
<?php get_header(); ?>

<main>
    <h1><?php the_title(); ?></h1>

    <div class="content">
        <?php the_content(); ?>
    </div>
</main>

<?php get_footer(); ?>
```

> **Poznámka k bezpečnosti:** Vždy používejte `esc_html()`, `esc_attr()` a `esc_url()` při výpisu dat do HTML. Nikdy nevypisujte uživatelská data přímo.
