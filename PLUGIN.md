# MorgoCMS — Průvodce tvorbou pluginu

Kompletní dokumentace pro vývojáře pluginů.

---

## 1. Základní struktura pluginu

Každý plugin je adresář v `plugins/` s následující strukturou:

```
plugins/muj-plugin/
├── plugin.php              # Hlavní soubor — třída pluginu
├── composer.json           # Metadata a registrace do autoloaderu
└── migrations/             # Databázové migrace (volitelné)
    └── 2026_01_01_000001_CreateMujPluginTable.php
```

---

## 2. Minimální plugin

Plugin musí rozšiřovat `AbstractPlugin` a implementovat čtyři povinné metody.

### plugin.php

```php
<?php

declare(strict_types=1);

namespace Morgo\Plugin\MujPlugin;

use Morgo\Core\AbstractPlugin;

class MujPlugin extends AbstractPlugin
{
    public static function getSlug(): string
    {
        return 'muj-plugin';
    }

    public static function getName(): string
    {
        return 'Můj Plugin';
    }

    public static function getVersion(): string
    {
        return '1.0.0';
    }

    public function register(): void
    {
        // Zaregistrujte hooky zde — volá se při každém požadavku
        sp_add_action('page.saved', [$this, 'onPageSaved']);
    }

    public function onPageSaved(object $page): void
    {
        // Logika po uložení stránky
    }
}
```

### Dostupné metody z AbstractPlugin (lze přepsat)

| Metoda | Výchozí chování | Popis |
|--------|----------------|-------|
| `boot(): void` | prázdná | Volá se po `register()`, po inicializaci DI kontejneru |
| `getMigrationsPath(): ?string` | vrátí `null` | Cesta k adresáři s migracemi |
| `activate(): void` | prázdná | Volá se při aktivaci pluginu v adminu |
| `deactivate(): void` | prázdná | Volá se při deaktivaci pluginu v adminu |

---

## 3. Registrace pluginu

### Přes composer.json projektu

Přidejte plně kvalifikovaný název třídy do `extra.morgocms-plugins` v kořenovém `composer.json`:

```json
{
    "extra": {
        "morgocms-plugins": [
            "Morgo\\Plugin\\MujPlugin\\MujPlugin"
        ]
    }
}
```

Po přidání spusťte `composer dump-autoload`.

### composer.json pluginu

```json
{
    "name": "morgo/muj-plugin",
    "description": "Popis pluginu",
    "type": "morgo-plugin",
    "autoload": {
        "psr-4": {
            "Morgo\\Plugin\\MujPlugin\\": ""
        }
    }
}
```

### Dynamická registrace přes options tabulku

Pluginy lze také aktivovat uložením jejich třídy do záznamu `active_plugins` v tabulce `options`. Tuto cestu používá admin UI — při kliknutí na „Aktivovat" se třída zapíše do DB.

---

## 4. Hook systém

Hooky jsou hlavní způsob, jak plugin komunikuje se zbytkem aplikace. Nikdy nevolejte `HookManager` přímo — vždy používejte `sp_*` helpers.

### Actions (akce)

```php
// Zaregistrovat callback na hook
sp_add_action('page.saved', function (object $page): void {
    // Volá se vždy po uložení stránky
}, priority: 10);

// Spustit hook (z jádra nebo jiného pluginu)
sp_do_action('page.saved', $page);

// Odebrat callback
sp_remove_action('page.saved', $myCallback);
```

### Filters (filtry)

```php
// Zaregistrovat filtr — callback musí vrátit upravenou hodnotu
sp_add_filter('the_content', function (string $html): string {
    return $html . '<p>Přidáno pluginem.</p>';
});

// Aplikovat filtr a získat výsledek
$html = sp_apply_filters('the_content', $rawHtml);
```

### Přehled dostupných hooků aplikace

| Hook | Typ | Předávané argumenty | Popis |
|------|-----|---------------------|-------|
| `app.boot` | action | — | Aplikace je inicializována |
| `page.saved` | action | `object $page` | Stránka byla uložena |
| `page.query` | filter | `array $queryArgs` | Úprava parametrů dotazu na stránky |
| `routes.register` | action | `\Slim\App $app` | Registrace vlastních rout |
| `admin.menu` | action | — | Přidání položek do admin sidebaru |
| `render_block_{type}` | filter | `string $html, array $block` | Úprava HTML konkrétního bloku při renderingu |
| `the_content` | filter | `string $html` | Úprava HTML obsahu stránky |
| `sp_head` | action | — | Vykreslení obsahu do `<head>` |
| `sp_footer` | action | — | Vykreslení obsahu před `</body>` |
| `cli.commands` | action | `\Symfony\Component\Console\Application $cli` | Registrace CLI příkazů |
| `theme.loaded` | action | — | Téma bylo načteno |
| `render.before` | action | `string $templateFile` | Před renderováním šablony |
| `render.after` | action | `string $templateFile` | Po renderování šablony |

---

## 5. Databázové migrace

### Vytvoření migrace

Vytvořte soubor v `plugins/muj-plugin/migrations/` s názvem ve formátu `YYYY_MM_DD_NNNNNN_NazevMigrace.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMujPluginTable
{
    public function up(): void
    {
        Schema::create('muj_plugin_zaznamy', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->text('zprava');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muj_plugin_zaznamy');
    }
}
```

### Sandbox prefixů tabulek

`PluginMigrationRunner` vynucuje, že všechny tabulky pluginu musí začínat prefixem odvozeným od slugu pluginu (pomlčky jsou nahrazeny podtržítky).

Plugin se slugem `muj-plugin` smí vytvářet pouze tabulky s prefixem `muj_plugin_`. Pokud se pokusíte vytvořit tabulku bez správného prefixu, migrace selže s chybou.

| Slug pluginu | Povolený prefix tabulek |
|-------------|------------------------|
| `contact-form` | `contact_form_` |
| `muj-plugin` | `muj_plugin_` |
| `seo-tools` | `seo_tools_` |

### Propojení migrací s pluginem

Přepište metodu `getMigrationsPath()` v třídě pluginu:

```php
public function getMigrationsPath(): ?string
{
    return __DIR__ . '/migrations';
}
```

---

## 6. Registrace vlastních rout

Použijte hook `routes.register`, který předává instanci Slim aplikace:

```php
use Slim\App;

public function register(): void
{
    sp_add_action('routes.register', function (App $app): void {
        $app->get('/muj-plugin', function ($request, $response) {
            $response->getBody()->write('<h1>Stránka pluginu</h1>');
            return $response;
        });

        $app->post('/muj-plugin/submit', [$this, 'handleSubmit']);
    });
}
```

---

## 7. Admin stránka v menu

Přidejte položku do admin sidebaru pomocí hooku `admin.menu`:

```php
public function register(): void
{
    sp_add_action('admin.menu', function (): void {
        // Funkce pro přidání menu položky závisí na implementaci admin UI
        // Typicky přidáte odkaz do globálního pole _morgo_admin_menu
        $GLOBALS['_morgo_admin_menu'][] = [
            'slug'  => 'muj-plugin',
            'label' => 'Můj Plugin',
            'url'   => '/admin/muj-plugin',
            'icon'  => 'puzzle',
        ];
    });
}
```

---

## 8. Aktivace a deaktivace

### Metoda activate()

Volá se při aktivaci pluginu v adminu nebo přes CLI. Typické použití:

- Spuštění databázových migrací
- Nastavení výchozích options

```php
public function activate(): void
{
    // Nastavit výchozí hodnoty
    if (get_option('muj_plugin_enabled') === null) {
        update_option('muj_plugin_enabled', true);
    }
    // Migrace se spouštějí automaticky přes getMigrationsPath()
}
```

### Metoda deactivate()

Volá se při deaktivaci. Důležité: **nesmazávejte tabulky** — data zůstanou pro případ opětovné aktivace.

```php
public function deactivate(): void
{
    // Čistit options, dočasné záznamy apod.
    // NIKDY: Schema::dropIfExists(...)
}
```

### Přes administraci

1. Přejděte na **Admin → Pluginy**
2. Klikněte na **Aktivovat** / **Deaktivovat**

### Přes CLI

```bash
php bin/sp plugin:enable muj-plugin
php bin/sp plugin:disable muj-plugin
```

---

## 9. Kompletní příklad — Contact Form plugin

Ukázkový plugin přidávající kontaktní formulář na stránce `/contact`.

### plugins/contact-form/plugin.php

```php
<?php

declare(strict_types=1);

namespace Morgo\Plugin\ContactForm;

use Morgo\Core\AbstractPlugin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

class ContactFormPlugin extends AbstractPlugin
{
    public static function getSlug(): string
    {
        return 'contact-form';
    }

    public static function getName(): string
    {
        return 'Contact Form';
    }

    public static function getVersion(): string
    {
        return '1.0.0';
    }

    public function getMigrationsPath(): ?string
    {
        return __DIR__ . '/migrations';
    }

    public function register(): void
    {
        sp_add_action('routes.register', function (App $app): void {
            $app->get('/contact', [$this, 'showForm']);
            $app->post('/contact', [$this, 'handleForm']);
        });
    }

    public function activate(): void
    {
        update_option('contact_form_recipient', get_option('admin_email', ''));
    }

    public function showForm(Request $request, Response $response): Response
    {
        $html = $this->renderForm();
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function handleForm(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $email   = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $message = trim($data['message'] ?? '');

        if (!$email || $message === '') {
            $response->getBody()->write($this->renderForm('Vyplňte prosím všechna pole.'));
            return $response->withStatus(422)->withHeader('Content-Type', 'text/html');
        }

        // Uložit do DB
        \Illuminate\Support\Facades\DB::table('contact_form_submissions')->insert([
            'email'      => $email,
            'message'    => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response->getBody()->write($this->renderForm(null, 'Zpráva byla odeslána. Děkujeme!'));
        return $response->withHeader('Content-Type', 'text/html');
    }

    private function renderForm(?string $error = null, ?string $success = null): string
    {
        $errorHtml   = $error   ? '<p style="color:red">'  . esc_html($error)   . '</p>' : '';
        $successHtml = $success ? '<p style="color:green">' . esc_html($success) . '</p>' : '';

        return <<<HTML
        <!DOCTYPE html>
        <html lang="cs">
        <head><meta charset="UTF-8"><title>Kontakt</title></head>
        <body>
            <h1>Kontaktní formulář</h1>
            {$errorHtml}
            {$successHtml}
            <form method="post" action="/contact">
                <label>E-mail:
                    <input type="email" name="email" required>
                </label><br>
                <label>Zpráva:
                    <textarea name="message" required></textarea>
                </label><br>
                <button type="submit">Odeslat</button>
            </form>
        </body>
        </html>
        HTML;
    }
}
```

### plugins/contact-form/migrations/2026_01_01_000001_CreateContactFormSubmissionsTable.php

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactFormSubmissionsTable
{
    public function up(): void
    {
        // Prefix 'contact_form_' odpovídá slugu 'contact-form' — sandbox vyžaduje tento prefix
        Schema::create('contact_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_form_submissions');
    }
}
```

### plugins/contact-form/composer.json

```json
{
    "name": "morgo/contact-form",
    "description": "Jednoduchý kontaktní formulář pro MorgoCMS",
    "type": "morgo-plugin",
    "autoload": {
        "psr-4": {
            "Morgo\\Plugin\\ContactForm\\": ""
        }
    }
}
```

### Registrace do kořenového composer.json

```json
{
    "extra": {
        "morgocms-plugins": [
            "Morgo\\Plugin\\ContactForm\\ContactFormPlugin"
        ]
    }
}
```
