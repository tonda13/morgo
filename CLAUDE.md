# CLAUDE.md — MorgoCMS

> Zkrácený průvodce pro AI asistenta. Plná architektura a implementační plán je v `INSTRUCTION.md`.

---

## Projekt

- **Název:** Morgo (MorgoCMS) — lehký CMS inspirovaný WordPressem, PHP Slim 4
- **Repozitář:** `git@github.com:tonda13/morgo.git` | Branch: `wordpress_like`
- **Pracovní adresář:** `/home/tonda/work/morgoCMS`

---

## Stack

| Vrstva | Technologie |
|--------|-------------|
| Backend | PHP 8.2, Slim 4 |
| DI | PHP-DI 7 (autowiring) |
| ORM | Eloquent (illuminate/database 10) |
| Admin CSS | Tailwind CSS via Vite |
| Admin JS | Vanilla JS |
| Editor | EditorJS |
| Logger | Monolog 3 (PSR-3) |
| DB | MySQL 8 (test: SQLite in-memory) |
| CLI | Symfony Console 6 |
| Storage | League Flysystem 3 |
| Testy | PHPUnit 11 |

---

## Vrstvená architektura

```
Http/          ← tenké Controllers (max ~20 řádků), Middleware
Application/   ← Command Handlers (orchestrace)
Domain/        ← Entity, Value Objects, Repository Interfaces, Domain Services
Infrastructure/← Eloquent repos, Flysystem, Monolog
Core/          ← HookManager, PluginLoader, ThemeEngine, I18n, Migration
```

**Pravidla:** `Domain/` nesmí importovat Slim/Eloquent. Controllers jen: parsuj → Handler → response.

---

## Hook systém — vždy přes sp_* helpers

```php
sp_add_action('page.saved', fn(Page $p) => ...);
sp_do_action('page.saved', $page);
sp_add_filter('page.content', fn(string $html) => $html);
$html = sp_apply_filters('page.content', $raw);
```

Nikdy nevolat `HookManager` přímo z pluginů/témat.

---

## Namespace a klíčové cesty

- Root namespace: `Morgo\`
- Entry point: `public/index.php`
- CLI: `bin/sp`
- Konfigurace: `config/app.php`, `config/container.php`, `config/routes.php`
- Migrace: `database/migrations/`
- Admin assets: `admin/resources/` → `admin/dist/` (commitovat)
- Témata: `themes/`, Pluginy: `plugins/`

---

## Bezpečnost (povinné)

- Hesla: `password_hash(PASSWORD_BCRYPT)` cost 12
- `session_regenerate_id(true)` po přihlášení
- CSRF token v každém admin formuláři, rotuje po submitu
- Veškerý výstup do HTML: `htmlspecialchars()` / `esc_html()`
- Prepared statements všude (Eloquent query builder)
- Upload: whitelist MIME typů, soubory → hash název
- SecurityHeadersMiddleware: X-Frame-Options, CSP, X-Content-Type-Options...
- Brute force: 5 pokusů → 15min lockout (tabulka `login_attempts`)

---

## Plugin systém

```php
// Minimální plugin (extends AbstractPlugin):
class MyPlugin extends AbstractPlugin {
    public static function getSlug(): string    { return 'my-plugin'; }
    public static function getName(): string    { return 'My Plugin'; }
    public static function getVersion(): string { return '1.0.0'; }
    public function register(): void { sp_add_action('page.saved', [$this, 'onSave']); }
}
// Registrace v composer.json extra.morgocms-plugins
```

**Migration sandbox:** plugin `contact-form` smí jen tabulky s prefixem `contact_form_`.

---

## ThemeEngine — hierarchie šablon

`page-{slug}.php` → `page-{id}.php` → named template → `page.php` → `index.php`
Homepage: `home.php` → `page.php` → `index.php`
Child theme: soubory child → parent, `functions.php` parent → child pořadí.

---

## Konvence kódu

- `declare(strict_types=1)` na každém souboru
- PSR-12 (`phpcs --standard=PSR12 app/`)
- Value Objects: `final` + `readonly`
- PSR-3 Logger: vždy `LoggerInterface`, nikdy `new Logger()` v business kódu
- Žádné `$_GET`/`$_POST` přímo v Controllers (PSR-7)
- Žádné globální proměnné (výjimka: `sp_*` helpers)

---

## Git workflow

```bash
git add <specifické soubory>
git commit -m "feat(scope): popis"
git push origin wordpress_like
```

Nikdy: `.env`, `vendor/`, `node_modules/`, `storage/logs/`
Vždy: `admin/dist/`, `composer.lock`, `package-lock.json`

---

## Příkazy

```bash
docker-compose up -d              # Dev prostředí (web: :8080, phpMyAdmin: :8081)
php bin/sp install                # Interaktivní instalace
php bin/sp migrate                # Spustit migrace
php bin/sp migrate:status         # Stav migrací
php bin/sp test                   # PHPUnit testy
npm run dev                       # Vite watch
npm run build                     # Vite produkční build
```

---

## Aktuální stav implementace

Viz `INSTRUCTION.md` — sekce "Přehled fází" a "Pořadí implementace (MVP)".

**Dokončené fáze:** žádná — vývoj teprve začíná.

---

*Aktualizuj "Dokončené fáze" po každé dokončené fázi.*
