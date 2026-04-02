# INSTRUCTION.md — MorgoCMS Implementační plán

> Tento soubor obsahuje podrobný plán implementace MorgoCMS.
> Vychází z CLAUDE.md (architektura a konvence).
> Postupuj fázi po fázi, každou fázi commituj do větve `wordpress_like`.

---

## Repozitář

- Remote: `git@github.com:tonda13/morgo.git`
- Branch: `wordpress_like`
- Jméno projektu: **Morgo** (používej všude kde je potřeba název)

---

## Přehled fází

| Fáze | Název | Priorita |
|------|-------|----------|
| 1 | Projektová kostra a infrastruktura | MVP |
| 2 | Bootstrap aplikace | MVP |
| 3 | Databázové migrace | MVP |
| 4 | Bezpečnost — middleware | MVP |
| 5 | Autentizace a uživatelé | MVP |
| 6 | Instalační wizard | MVP |
| 7 | Admin panel — layout | MVP |
| 8 | CRUD stránek | MVP |
| 9 | Blokový editor (EditorJS) | MVP |
| 10 | ThemeEngine + výchozí téma | MVP |
| 11 | Média manager | MVP |
| 12 | Menu manager | MVP |
| 13 | Widget systém | MVP |
| 14 | Plugin systém | MVP |
| 15 | CLI příkazy | MVP |
| 16 | Custom fields + SEO | POST-MVP |
| 17 | PHPUnit testy | POST-MVP |
| 18 | i18n — překladové API | POST-MVP |
| 19 | Logy a monitoring | POST-MVP |

---

## Fáze 1 — Projektová kostra a infrastruktura

### Soubory k vytvoření:
- `composer.json` — PHP závislosti (Slim 4, PHP-DI 7, Eloquent 10, Monolog 3, Symfony Console 6, League Flysystem 3, PHPUnit 11, PHP_CodeSniffer 3)
- `package.json` — npm závislosti (Vite, Tailwind CSS, EditorJS balíčky)
- `vite.config.js` — Vite konfigurace (vstup admin/resources/js/admin.js, výstup admin/dist/)
- `tailwind.config.js` — Tailwind CSS konfigurace (content: admin/templates/**/*.php)
- `phpunit.xml` — PHPUnit konfigurace
- `.env.example` — vzor konfigurace prostředí
- `.gitignore` — ignoruj vendor/, node_modules/, admin/dist/ NE, .env ANO, storage/logs/
- `docker-compose.yml` — PHP 8.2-FPM, Nginx, MySQL 8, phpMyAdmin
- `docker/php/Dockerfile` — PHP 8.2-FPM + rozšíření (pdo_mysql, mbstring, gd, fileinfo, opcache, xdebug)
- `docker/nginx/nginx.conf` — Nginx config, document root public/, blokování .env/.git/.md
- `public/index.php` — entry point (bootstrapuje Slim app)
- `public/.htaccess` — Apache fallback (všechny requesty → index.php)
- Adresářová struktura: `app/`, `config/`, `database/migrations/`, `themes/default/`, `plugins/`, `admin/resources/`, `admin/dist/`, `storage/logs/`, `storage/cache/`, `content/uploads/`, `tests/`, `lang/`, `bin/`

### Git commit: `feat: initial project scaffold`

---

## Fáze 2 — Bootstrap aplikace

### Soubory k vytvoření:
- `config/app.php` — hlavní konfigurace (env, debug, timezone, admin_prefix, site_url, DB settings)
- `config/container.php` — PHP-DI definice (interface → implementace, PSR-3 Logger, HookManager singleton)
- `config/routes.php` — Slim route definice (frontend + admin group + install)
- `app/Core/Application.php` — bootstrap (Slim app init, DI container, Eloquent connect, helper includes, plugin loader, theme loader)
- `app/Core/HookManager.php` — action/filter systém (addAction, doAction, addFilter, applyFilters, removeAction, removeFilter)
- `app/Core/Helpers/hooks.php` — globální sp_add_action, sp_do_action, sp_add_filter, sp_apply_filters atd.
- `app/Core/Helpers/url.php` — site_url, theme_url, parent_theme_url, plugin_url
- `app/Core/Helpers/template.php` — the_title, the_content, get_header, get_footer, get_sidebar, get_template_part, the_menu, widget_area
- `app/Core/Helpers/i18n.php` — __(), _e(), __f(), _n(), sp_load_textdomain
- `app/Core/Helpers/security.php` — esc_html(), esc_attr(), esc_url()
- `app/Domain/Shared/ValueObject.php` — base třída
- `app/Domain/Shared/DomainEvent.php` — base třída

### Git commit: `feat: application bootstrap, DI container, hook system`

---

## Fáze 3 — Databázové migrace (vlastní runner)

### Soubory k vytvoření:
- `app/Core/Migration/Migration.php` — abstraktní base (protected Schema $schema, abstract up/down)
- `app/Core/Migration/MigrationManager.php` — orchestrátor (spouští core i plugin migrace)
- `app/Core/Migration/CoreMigrationRunner.php` — core migrace s plnými právy
- `app/Core/Migration/PluginMigrationRunner.php` — sandboxovaný runner (ověřuje prefix tabulek)
- `app/Core/Migration/MigrationSandboxException.php` — výjimka pro sandbox porušení
- `database/migrations/2026_01_01_000001_CreateMigrationsTable.php` — tabulka migrations (id, context, migration, batch, executed_at)
- `database/migrations/2026_01_01_000002_CreateUsersTable.php` — users + remember_tokens + login_attempts
- `database/migrations/2026_01_01_000003_CreatePagesTable.php` — pages
- `database/migrations/2026_01_01_000004_CreateMediaTable.php` — media
- `database/migrations/2026_01_01_000005_CreateMenusTable.php` — menus + menu_items
- `database/migrations/2026_01_01_000006_CreateWidgetsTable.php` — widgets
- `database/migrations/2026_01_01_000007_CreateOptionsTable.php` — options
- `database/migrations/2026_01_01_000008_CreateCustomFieldsTable.php` — custom_fields
- `database/seeds/DemoSeeder.php` — demo data (admin user, homepage, ukázkový obsah)

### Git commit: `feat: database migrations, custom migration runner with plugin sandbox`

---

## Fáze 4 — Bezpečnostní middleware

### Soubory k vytvoření:
- `app/Http/Middleware/SecurityHeadersMiddleware.php` — X-Frame-Options, X-Content-Type-Options, CSP, Referrer-Policy, Permissions-Policy
- `app/Http/Middleware/CsrfMiddleware.php` — token generování (vázán na session), ověření POST/PUT/DELETE, rotace po submitu
- Přidat CSRF helper funkce do security helpers: `csrf_token()`, `csrf_field()` (HTML hidden input)

### Git commit: `feat: security middleware (CSRF, security headers)`

---

## Fáze 5 — Autentizace a role

### Soubory k vytvoření:
- `app/Domain/User/User.php` — entita
- `app/Domain/User/Email.php` — value object (validace formátu)
- `app/Domain/User/Role.php` — enum (admin|editor|viewer)
- `app/Domain/User/UserRepositoryInterface.php` — interface
- `app/Infrastructure/Persistence/EloquentUserRepository.php` — Eloquent implementace
- `app/Core/Auth/AuthManager.php` — login (bcrypt verify, session regenerate, brute force check), logout, getCurrentUser, isLoggedIn, remember me token
- `app/Core/Auth/RoleManager.php` — statická capability mapa, can($user, $capability) metoda
- `app/Core/Auth/Capabilities.php` — konstanty všech capabilities
- `app/Http/Middleware/AuthMiddleware.php` — ověří session, redirect na login pokud není přihlášen
- `app/Http/Middleware/CapabilityMiddleware.php` — ověří capability aktuálního uživatele
- `app/Http/Admin/AuthController.php` — GET/POST login, POST logout
- `admin/templates/auth/login.php` — přihlašovací stránka (Tailwind, bez externích závislostí)
- Routy: GET /admin/login, POST /admin/login, POST /admin/logout

### Git commit: `feat: authentication, sessions, role/capability system, brute force protection`

---

## Fáze 6 — Instalační wizard

### Soubory k vytvoření:
- `app/Http/Install/InstallController.php` — krokový controller (step 1-6), redirect na /admin po instalaci
- `app/Core/Install/InstallManager.php` — logika instalace (check requirements, test DB, run migrations, create admin, save options, write config)
- `app/Core/Install/RequirementsChecker.php` — PHP verze, rozšíření, zapisovatelné adresáře
- Šablony `install/templates/`:
  - `layout.php` — základní layout wizardu
  - `step1-requirements.php` — výsledky kontroly prostředí
  - `step2-database.php` — DB formulář + test připojení (AJAX)
  - `step3-migrate.php` — progress spuštění migrací
  - `step4-admin.php` — admin účet (email, heslo, display_name)
  - `step5-settings.php` — název webu, URL
  - `step6-done.php` — shrnutí, tlačítka do adminu/webu
- AJAX endpoint: POST /install/test-db
- Po instalaci: zápis `config/app.php`, blokování /install (404)

### Git commit: `feat: installation wizard (6 steps)`

---

## Fáze 7 — Admin panel — layout a navigace

### Soubory k vytvoření:
- `admin/resources/css/admin.css` — Tailwind direktivy + vlastní komponenty (@layer components)
- `admin/resources/js/admin.js` — vanilla JS entry point (flash messages, confirm dialogs, dropdown menus)
- `admin/templates/layout.php` — hlavní admin layout (sidebar nav, header, content area, flash zprávy, breadcrumbs)
- `admin/templates/partials/nav.php` — postranní navigace s ikonami a aktivním stavem
- `admin/templates/partials/header.php` — horní lišta (jméno uživatele, odhlášení)
- `admin/templates/partials/flash.php` — flash zprávy (success/error/warning)
- `app/Http/Admin/DashboardController.php` — GET /admin (statistiky: počet stránek, médií, pluginů)
- `admin/templates/dashboard/index.php` — dashboard šablona
- `app/Core/Flash.php` — flash zprávy přes session ($_SESSION['flash'])
- Spustit první `npm run build` a ověřit Tailwind output

### Git commit: `feat: admin panel layout, navigation, dashboard, flash messages`

---

## Fáze 8 — CRUD stránek

### Soubory k vytvoření:
- `app/Domain/Page/Page.php` — entita (id, slug, title, status, content_blocks, template, parent_id, menu_order, created_at, updated_at, created_by)
- `app/Domain/Page/PageBlock.php` — entita (blok v content_blocks)
- `app/Domain/Page/Slug.php` — value object (validace, sanitizace)
- `app/Domain/Page/PageStatus.php` — enum (draft|published|private)
- `app/Domain/Page/PageRepositoryInterface.php` — interface (findById, findBySlug, findAll, save, delete)
- `app/Domain/Page/PagePublisher.php` — domain service (publish, unpublish)
- `app/Infrastructure/Persistence/EloquentPageRepository.php` — Eloquent implementace
- `app/Application/Page/CreatePage/CreatePageCommand.php` + `CreatePageHandler.php`
- `app/Application/Page/UpdatePage/UpdatePageCommand.php` + `UpdatePageHandler.php`
- `app/Application/Page/DeletePage/DeletePageCommand.php` + `DeletePageHandler.php`
- `app/Application/Page/PublishPage/PublishPageCommand.php` + `PublishPageHandler.php`
- `app/Http/Admin/PageController.php` — index, create, store, edit, update, destroy (tenké, max 20 řádků)
- Šablony `admin/templates/pages/`:
  - `index.php` — seznam stránek (tabulka, stavy, akce, prázdný stav s CTA)
  - `create.php` — formulář nové stránky
  - `edit.php` — formulář editace stránky (s EditorJS holder)
- Routy: GET/POST/PUT/DELETE /admin/pages...
- Hooky: page.beforeSave, page.saved, page.deleted

### Git commit: `feat: page CRUD with domain layer, hooks, admin UI`

---

## Fáze 9 — Blokový editor (EditorJS)

### Soubory k vytvoření:
- `package.json` — přidat EditorJS balíčky (@editorjs/editorjs, header, paragraph, image, list, quote, delimiter, raw, embed, table, attaches)
- `admin/resources/js/editor.js` — inicializace EditorJS, MorgoImage wrapper (otevírá media picker modal), autosave do localStorage každých 30s, uložení před odesláním formuláře
- `admin/resources/js/media-picker.js` — modal s výběrem médií z manageru
- `app/Services/BlockRenderer.php` — render(array $data): string, match na block typy, filter render_block_{type} pro neznámé bloky, the_content filter
- `app/Http/Admin/PageController.php` — přidání draftSave API endpointu (POST /admin/pages/draft)
- `admin/templates/pages/edit.php` — doplnit EditorJS holder #editorjs, hidden input content_blocks, JS inicializaci

### Git commit: `feat: EditorJS block editor, BlockRenderer, media picker integration`

---

## Fáze 10 — ThemeEngine + výchozí téma

### Soubory k vytvoření:
- `app/Core/ThemeEngine.php` — loader, hierarchie šablon (6 kroků viz CLAUDE.md 8d), child theme fallback, functions.php načítání (parent → child), pojmenované šablony z templates/ adresáře
- `app/Http/Frontend/PageController.php` — home, page (resolves template, renders přes ThemeEngine)
- Výchozí téma `themes/default/`:
  - `theme.php` — metadata (name: Morgo Default, slug: default)
  - `functions.php` — registrace menu, widget oblastí, enqueue styles
  - `index.php` — fallback šablona
  - `page.php` — šablona stránky (s volitelným sidebarem)
  - `home.php` — homepage šablona
  - `header.php` — hlavička s menu
  - `footer.php` — patička s widget oblastí
  - `sidebar.php` — postranní panel
  - `templates/full-width.php` — layout celou šířku
  - `templates/landing.php` — landing page layout
  - `assets/css/style.css` — čisté CSS + CSS custom properties (design tokeny viz CLAUDE.md 13d)
  - `assets/css/style.css` — typografie, layout, responzivita (mobile-first), hamburger menu JS
  - `screenshot.png` — náhled 1200×900px (placeholder)
- `admin/templates/themes/` — šablona pro správu témat

### Git commit: `feat: ThemeEngine, template hierarchy, default theme`

---

## Fáze 11 — Média manager

### Soubory k vytvoření:
- `app/Domain/Media/Media.php` — entita
- `app/Domain/Media/MimeType.php` — value object (whitelist: image/jpeg, image/png, image/gif, image/webp, application/pdf)
- `app/Domain/Media/MediaRepositoryInterface.php`
- `app/Infrastructure/Persistence/EloquentMediaRepository.php`
- `app/Infrastructure/Storage/LocalMediaStorage.php` — League Flysystem, nahrávání do content/uploads/YYYY/MM/, přejmenování na hash
- `app/Application/Media/UploadMedia/UploadMediaCommand.php` + `UploadMediaHandler.php`
- `app/Http/Admin/MediaController.php` — index (grid view), upload (POST), delete, picker (AJAX JSON pro modal)
- `admin/templates/media/`:
  - `index.php` — grid médií, upload dropzone, prázdný stav
  - `picker.php` — modal pro výběr z EditorJS
- Routy: GET /admin/media, POST /admin/media, DELETE /admin/media/{id}, GET /admin/media/picker

### Git commit: `feat: media manager with upload, browse, delete, EditorJS picker`

---

## Fáze 12 — Menu manager

### Soubory k vytvoření:
- `app/Domain/Menu/Menu.php`, `MenuItem.php`, `MenuRepositoryInterface.php`
- `app/Infrastructure/Persistence/EloquentMenuRepository.php`
- `app/Http/Admin/MenuController.php` — CRUD menu + drag&drop položek (AJAX)
- `admin/templates/menus/`:
  - `index.php` — seznam menu
  - `edit.php` — editor menu s drag&drop položkami (vanilla JS)
- Helper `the_menu(string $location)` — renderuje HTML navigaci z DB
- Routy: GET/POST/PUT/DELETE /admin/menus...

### Git commit: `feat: menu manager with drag&drop`

---

## Fáze 13 — Widget systém

### Soubory k vytvoření:
- `app/Domain/Widget/Widget.php` — entita (id, area_slug, widget_type, title, config JSON, widget_order)
- `app/Domain/Widget/WidgetRepositoryInterface.php`
- `app/Infrastructure/Persistence/EloquentWidgetRepository.php`
- `app/Core/WidgetManager.php` — registrace widget typů (registerType), render (renderArea), drag&drop order
- Vestavěné widget typy: TextWidget, RecentPagesWidget, MenuWidget, HtmlWidget
- `app/Http/Admin/WidgetController.php` — správa widgetů v oblastech (drag&drop AJAX)
- `admin/templates/widgets/index.php` — drag&drop rozhraní (widget oblasti + dostupné widgety)
- Helper `widget_area(string $slug)` — renderuje widgety oblasti
- Hook `register_widget_areas` pro témata
- Routy: GET /admin/widgets, POST /admin/widgets, PUT/DELETE /admin/widgets/{id}

### Git commit: `feat: widget system with areas, built-in widgets`

---

## Fáze 14 — Plugin systém

### Soubory k vytvoření:
- `app/Core/PluginInterface.php` — kompletní interface (getSlug, getName, getVersion, register, boot, getMigrationsPath, activate, deactivate)
- `app/Core/AbstractPlugin.php` — výchozí implementace (prázdné boot, getMigrationsPath null, prázdné activate/deactivate)
- `app/Core/PluginLoader.php` — načítání z composer.json extra.morgocms-plugins, volání register() + boot(), správa aktivních pluginů v DB options
- `app/Http/Admin/PluginController.php` — seznam pluginů, aktivace/deaktivace
- `admin/templates/plugins/index.php` — přehled pluginů (aktivní/neaktivní, verze, popis)
- `plugins/example-plugin/` — ukázkový plugin demonstrující API
  - `composer.json`
  - `src/ExamplePlugin.php` — extends AbstractPlugin, ukázka hooků
- Routy: GET /admin/plugins, POST /admin/plugins/{slug}/activate, POST /admin/plugins/{slug}/deactivate

### Git commit: `feat: plugin system (PluginInterface, loader, activation, example plugin)`

---

## Fáze 15 — CLI příkazy (Symfony Console)

### Soubory k vytvoření:
- `bin/sp` — CLI entry point (spustitelný, Symfony Console Application)
- `app/Console/` — adresář pro příkazy
- `app/Console/Commands/`:
  - `InstallCommand.php` — php bin/sp install (interaktivní instalace)
  - `MigrateCommand.php` — php bin/sp migrate
  - `MigrateRollbackCommand.php` — php bin/sp migrate:rollback [--steps=N]
  - `MigrateStatusCommand.php` — php bin/sp migrate:status
  - `UserCreateCommand.php` — php bin/sp user:create
  - `UserResetPasswordCommand.php` — php bin/sp user:reset-password <email>
  - `PluginListCommand.php` — php bin/sp plugin:list
  - `PluginEnableCommand.php` — php bin/sp plugin:enable <slug>
  - `PluginDisableCommand.php` — php bin/sp plugin:disable <slug>
  - `PluginMigrateCommand.php` — php bin/sp plugin:migrate <slug>
  - `ThemeListCommand.php` — php bin/sp theme:list
  - `ThemeActivateCommand.php` — php bin/sp theme:activate <slug>
  - `ThemeActiveCommand.php` — php bin/sp theme:active
  - `CacheClearCommand.php` — php bin/sp cache:clear
  - `MaintenanceCommand.php` — php bin/sp maintenance:on / :off
  - `TestCommand.php` — php bin/sp test (alias pro phpunit)
- Hook `cli.commands` pro pluginy — mohou přidat vlastní příkazy

### Git commit: `feat: CLI commands (bin/sp) with Symfony Console`

---

## Fáze 16 — Custom fields + SEO meta tagy

### Soubory k vytvoření:
- `app/Domain/Page/CustomField.php` — value object
- Přidat `get_custom_field()` helper do template helpers
- Admin UI: přidat Custom Fields panel do edit.php stránky (dynamické páry key-value v JS)
- `app/Services/SeoService.php` — generování meta title, description, og tagy
- Helper `the_seo_tags()` pro šablony
- Přidat SEO panel do edit.php stránky (meta_title, meta_description)
- Hook `sp_head` pro vkládání SEO tagů do `<head>`

### Git commit: `feat: custom fields, SEO meta tags`

---

## Fáze 17 — PHPUnit testy

### Soubory k vytvoření:
- `tests/bootstrap.php` — PHPUnit bootstrap (autoload, .env.testing načtení, SQLite setup)
- `tests/Unit/Core/HookManagerTest.php` — testy hook systému (viz CLAUDE.md 16c)
- `tests/Unit/Migration/PluginMigrationSandboxTest.php` — testy sandboxu (viz CLAUDE.md 16c)
- `tests/Unit/Core/BlockRendererTest.php` — testy BlockRenderer (viz CLAUDE.md 16c)
- `tests/Unit/Auth/RoleManagerTest.php` — testy capability systému (viz CLAUDE.md 16c)
- `tests/Integration/PageRepositoryTest.php` — SQLite in-memory CRUD testy
- `tests/Integration/MigrationRunnerTest.php` — testy migration runneru
- `tests/Feature/Admin/LoginTest.php` — HTTP test přihlašování
- `tests/Feature/Admin/PageCrudTest.php` — HTTP testy CRUD stránek
- `tests/Feature/Frontend/PageRenderTest.php` — HTTP test renderování stránek

### Git commit: `test: PHPUnit unit, integration and feature tests`

---

## Fáze 18 — i18n API

### Soubory k vytvoření:
- `app/Core/I18n.php` — načítání JSON překladů, domény, locale fallback (cs_CZ → cs → english key)
- Implementovat `app/Core/Helpers/i18n.php` helper funkce (__)(), _e(), __f(), _n(), sp_load_textdomain)
- `lang/cs_CZ.json` — české překlady všech admin řetězců
- Hooky: `sp_locale` (filter), `sp_load_textdomain` (action)
- Aktualizovat všechny admin šablony — pevné texty → __() volání

### Git commit: `feat: i18n API with JSON translations, Czech locale`

---

## Fáze 19 — Logy a monitoring

### Soubory k vytvoření:
- `app/Infrastructure/Logging/MonologLogger.php` — PSR-3 wrapper, různé kanály (app, security, migration, plugin)
- `config/container.php` — aktualizovat binding PSR-3 LoggerInterface → MonologLogger
- Log rotace: RotatingFileHandler (30 dní) v produkci
- Logování přihlášení/odhlášení do 'security' kanálu
- Logování brute force lockoutů
- Logování migrací
- `app/Http/Middleware/ErrorMiddleware.php` — zachycení neošetřených výjimek, logování, vrácení čisté error stránky (ne stack trace v produkci)

### Git commit: `feat: Monolog logging with channels, error middleware`

---

## Závěrečné kroky po všech fázích

1. Spustit `composer install` a `npm install`
2. Spustit `npm run build` (Tailwind + Vite)
3. Ověřit instalaci přes wizard
4. Spustit `php vendor/bin/phpunit` — všechny testy musí projít
5. Spustit `php vendor/bin/phpcs --standard=PSR12 app/` — žádné chyby
6. Vytvořit tag `v0.1.0-mvp`

---

## Pravidla pro commitování

- Každou fázi commituj zvlášť (viz commit zprávy výše)
- V rámci fáze mohou být sub-commity pro větší celky
- Commit message formát: `type(scope): popis` kde type = feat/fix/test/refactor/docs
- Nikdy necommitovat: `.env`, `vendor/`, `node_modules/`, `storage/logs/`
- Vždy commitovat: `admin/dist/` (Vite výstup), `composer.lock`, `package-lock.json`

---

*Plán připraven: 2026-04-02*

---

# ARCHITEKTONICKÁ REFERENCE

> Kompletní architektura MorgoCMS — původní obsah CLAUDE.md.
> Čti tuto sekci vždy když potřebuješ detaily implementace.

---

## A1. Co je MorgoCMS

MorgoCMS je lehký CMS framework inspirovaný WordPressem, postavený na moderním PHP stacku. Cíl je stejná jednoduchost použití jako WP, ale čistá architektura bez historického balastu.

**Klíčové rozdíly oproti WordPressu:**
- Žádné blog posty — pouze **stránky** (Pages)
- Framework: **PHP Slim 4** místo vlastního WP kernelu
- Šablony: **čisté PHP šablony** (žádný Twig ani Blade)
- Pluginy načítány přes **Composer + PSR-4** (ne wp-content/plugins)
- Blokový editor obsahu — jednodušší alternativa Gutenbergu

---

## A2. Technologický stack

| Vrstva | Technologie |
|---|---|
| Backend framework | PHP Slim 4 |
| Databáze | MySQL 8 |
| Šablony | Čisté PHP (.php soubory) |
| DI Container | PHP-DI 7 (autowiring) |
| ORM | Eloquent standalone (illuminate/database) |
| Logger | Monolog 3 (PSR-3) |
| Dependency management | Composer (PSR-4 autoload) |
| Dev prostředí | Docker (docker-compose) |
| Produkce | LAMP / LEMP server |
| Admin UI — JS | Vanilla JS |
| Admin UI — CSS | Tailwind CSS (přes Vite build, žádný runtime) |
| Editor obsahu | EditorJS |
| Asset pipeline (admin) | Vite |
| Výchozí téma — CSS | Čisté CSS + CSS custom properties |
| Ostatní témata — CSS | Svoboda vývojáře (jádro nic nevynucuje) |

**Klíčové Composer závislosti:**
```json
{
  "require": {
    "php":                     "^8.2",
    "slim/slim":               "^4.0",
    "slim/psr7":               "^1.0",
    "php-di/php-di":           "^7.0",
    "illuminate/database":     "^10.0",
    "monolog/monolog":         "^3.0",
    "psr/log":                 "^3.0",
    "symfony/console":         "^6.0",
    "league/flysystem":        "^3.0"
  },
  "require-dev": {
    "phpunit/phpunit":         "^11.0",
    "squizlabs/php_codesniffer": "^3.0"
  }
}
```

---

## A3. Adresářová struktura

```
morgocms/
├── public/                        # Document root — jediný veřejný adresář
│   ├── index.php                  # Entry point — bootstrapuje Slim app
│   └── assets/                    # Compiled admin assets (Vite output)
│
├── app/                           # Aplikační kód (namespace: Morgo\)
│   │
│   ├── Domain/                    # Čistá doménová logika — žádné závislosti na Slim/Eloquent
│   │   ├── Page/
│   │   │   ├── Page.php
│   │   │   ├── PageBlock.php
│   │   │   ├── Slug.php
│   │   │   ├── PageStatus.php
│   │   │   ├── PageRepositoryInterface.php
│   │   │   └── PagePublisher.php
│   │   ├── User/
│   │   │   ├── User.php
│   │   │   ├── Email.php
│   │   │   ├── Role.php
│   │   │   └── UserRepositoryInterface.php
│   │   ├── Media/
│   │   │   ├── Media.php
│   │   │   ├── MimeType.php
│   │   │   └── MediaRepositoryInterface.php
│   │   ├── Menu/
│   │   │   ├── Menu.php
│   │   │   ├── MenuItem.php
│   │   │   └── MenuRepositoryInterface.php
│   │   └── Shared/
│   │       ├── DomainEvent.php
│   │       └── ValueObject.php
│   │
│   ├── Application/               # Use Cases — orchestrace domény a infrastruktury
│   │   ├── Page/
│   │   │   ├── CreatePage/
│   │   │   │   ├── CreatePageCommand.php
│   │   │   │   └── CreatePageHandler.php
│   │   │   ├── PublishPage/
│   │   │   │   ├── PublishPageCommand.php
│   │   │   │   └── PublishPageHandler.php
│   │   │   └── DeletePage/
│   │   │       ├── DeletePageCommand.php
│   │   │       └── DeletePageHandler.php
│   │   ├── Media/
│   │   │   └── UploadMedia/
│   │   │       ├── UploadMediaCommand.php
│   │   │       └── UploadMediaHandler.php
│   │   └── User/
│   │       └── CreateUser/
│   │           ├── CreateUserCommand.php
│   │           └── CreateUserHandler.php
│   │
│   ├── Infrastructure/            # Konkrétní implementace interfaces
│   │   ├── Persistence/
│   │   │   ├── EloquentPageRepository.php
│   │   │   ├── EloquentUserRepository.php
│   │   │   └── EloquentMediaRepository.php
│   │   ├── Storage/
│   │   │   └── LocalMediaStorage.php
│   │   └── Logging/
│   │       └── MonologLogger.php
│   │
│   ├── Http/                      # Slim vrstva — Controllers a Middleware
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── PageController.php
│   │   │   ├── MediaController.php
│   │   │   ├── MenuController.php
│   │   │   ├── WidgetController.php
│   │   │   ├── UserController.php
│   │   │   ├── PluginController.php
│   │   │   ├── ThemeController.php
│   │   │   └── SettingsController.php
│   │   ├── Frontend/
│   │   │   └── PageController.php
│   │   └── Middleware/
│   │       ├── AuthMiddleware.php
│   │       ├── CsrfMiddleware.php
│   │       ├── SecurityHeadersMiddleware.php
│   │       └── CapabilityMiddleware.php
│   │
│   └── Core/
│       ├── Application.php
│       ├── HookManager.php
│       ├── PluginLoader.php
│       ├── ThemeEngine.php
│       ├── I18n.php
│       ├── Migration/
│       │   ├── MigrationManager.php
│       │   ├── CoreMigrationRunner.php
│       │   └── PluginMigrationRunner.php
│       └── Helpers/
│           ├── hooks.php
│           ├── template.php
│           ├── url.php
│           └── i18n.php
│
├── content/uploads/
├── themes/default/
├── plugins/
├── config/
│   ├── app.php
│   ├── container.php
│   └── routes.php
├── database/migrations/
├── database/seeds/
├── storage/logs/
├── storage/cache/
├── admin/resources/
├── admin/dist/
├── docker/
├── tests/
├── lang/
└── bin/sp
```

---

## A4. Databázové schéma

### `pages`
```sql
id, slug, title, status (draft|published),
template, content_blocks (JSON),
meta_title, meta_description,
parent_id, menu_order,
created_at, updated_at, created_by
```

### `users`
```sql
id, email, password_hash, display_name,
role (admin|editor|viewer),
created_at, last_login
```

### `media`
```sql
id, filename, filepath, mime_type,
alt_text, caption,
uploaded_by, created_at
```

### `menus` / `menu_items`
```sql
-- menus: id, name, slug
-- menu_items: id, menu_id, label, url, page_id (nullable), parent_id, menu_order
```

### `widgets`
```sql
id, area_slug, widget_type, title, config (JSON), widget_order
```

### `custom_fields`
```sql
id, page_id, field_key, field_value
```

### `options`
```sql
option_key, option_value
```

### `login_attempts` / `remember_tokens`
```sql
-- login_attempts: id, email, ip, attempted_at
-- remember_tokens: id, user_id, token_hash, expires_at
```

---

## A5. Hook systém

`HookManager` je singleton v DI. **Nikdy volat přímo** — vždy přes `sp_*` helpers.

```php
sp_add_action(string $hook, callable $callback, int $priority = 10): void
sp_do_action(string $hook, mixed ...$args): void
sp_add_filter(string $hook, callable $callback, int $priority = 10): void
sp_apply_filters(string $hook, mixed $value, mixed ...$args): mixed
sp_remove_action(string $hook, callable $callback): void
sp_remove_filter(string $hook, callable $callback): void
```

**Klíčové hooky jádra:**

| Hook | Typ | Kdy |
|---|---|---|
| `app.boot` | action | Po init aplikace |
| `plugins.loaded` | action | Po načtení pluginů |
| `theme.loaded` | action | Po načtení functions.php tématu |
| `routes.register` | action | Registrace rout z pluginů |
| `page.beforeSave` | action | Před uložením stránky |
| `page.saved` | action | Po uložení stránky |
| `page.deleted` | action | Po smazání stránky |
| `page.query` | filter | Úprava DB dotazu |
| `page.content` | filter | HTML obsah před výstupem |
| `page.title` | filter | Titulek před výstupem |
| `admin.menu` | action | Přidání admin menu položek |
| `render.before` | action | Před renderem šablony |
| `render.after` | action | Po renderu šablony |
| `sp_head` | action | Uvnitř `<head>` |
| `sp_footer` | action | Před `</body>` |
| `widget.areas` | action | Registrace widget oblastí |
| `blocks.register` | action | Registrace EditorJS bloků |
| `sp_locale` | filter | Aktivní locale |
| `cli.commands` | action | Přidání CLI příkazů |
| `render_block_{type}` | filter | Render vlastního bloku |
| `the_content` | filter | Finální HTML obsahu |

---

## A6. Plugin systém

### PluginInterface (kompletní)
```php
namespace Morgo\Core;
interface PluginInterface
{
    public static function getSlug(): string;
    public static function getName(): string;
    public static function getVersion(): string;
    public function register(): void;
    public function boot(): void;
    public function getMigrationsPath(): ?string;
    public function activate(): void;
    public function deactivate(): void;
}
```

### AbstractPlugin (výchozí implementace)
```php
abstract class AbstractPlugin implements PluginInterface
{
    public function boot(): void {}
    public function getMigrationsPath(): ?string { return null; }
    public function activate(): void {}
    public function deactivate(): void {}
}
```

### Registrace v composer.json
```json
{
    "extra": {
        "morgocms-plugins": ["MyVendor\\MyPlugin\\MyPlugin"]
    }
}
```

### Migration sandbox
- Plugin slug `contact-form` → povolený prefix `contact_form_`
- Sandbox blokuje operace na tabulkách jiných pluginů i core tabulkách
- Zachycené operace: create, table, drop, dropIfExists, rename (hasTable/hasColumn povoleny)

---

## A7. Blokový editor (EditorJS)

### Vestavěné nástroje
| Tool | Balíček | Block type |
|---|---|---|
| Odstavec | @editorjs/paragraph | paragraph |
| Nadpis | @editorjs/header | header |
| Obrázek | vlastní MorgoImage | image |
| Seznam | @editorjs/list | list |
| Citace | @editorjs/quote | quote |
| Oddělovač | @editorjs/delimiter | delimiter |
| Raw HTML | @editorjs/raw | raw |
| Tabulka | @editorjs/table | table |
| Embed | @editorjs/embed | embed |

**MorgoImage** — wrapper, otevírá Media Picker modal místo přímého uploadu.

### BlockRenderer
```php
class BlockRenderer
{
    public function render(array $data): string
    {
        $html = '';
        foreach ($data['blocks'] ?? [] as $block) {
            $html .= match($block['type']) {
                'paragraph' => $this->paragraph($block['data']),
                'header'    => $this->header($block['data']),
                'image'     => $this->image($block['data']),
                'list'      => $this->list($block['data']),
                'quote'     => $this->quote($block['data']),
                'delimiter' => '<hr class="sp-delimiter">',
                'raw'       => $block['data']['html'],
                'table'     => $this->table($block['data']),
                'embed'     => $this->embed($block['data']),
                default     => sp_apply_filters('render_block_' . $block['type'], '', $block),
            };
        }
        return sp_apply_filters('the_content', $html);
    }
}
```

### Formát dat v DB (LONGTEXT JSON)
```json
{
  "time": 1712000000000,
  "version": "2.29.0",
  "blocks": [
    {"id": "abc", "type": "header",    "data": {"text": "Nadpis", "level": 2}},
    {"id": "def", "type": "paragraph", "data": {"text": "Text s <b>tučným</b> písmem."}},
    {"id": "ghi", "type": "image",     "data": {"media_id": 42, "url": "/uploads/foto.jpg", "caption": ""}}
  ]
}
```

---

## A8. Theme systém

### Hierarchie šablon (první nalezený vyhraje)
1. `page-{slug}.php`
2. `page-{id}.php`
3. Pojmenovaná šablona z admin UI (`templates/*.php`)
4. `page.php`
5. `index.php` ← povinný fallback

Pro homepage: `home.php` → `page.php` → `index.php`

### Child themes
- `parent` klíč v `theme.php`
- Soubory: child → parent fallback
- `functions.php`: parent se načte první, pak child

### Pojmenované šablony
```php
<?php
/**
 * Template Name: Celá šířka
 */
```

### Helper funkce v šablonách
```php
the_title()             get_the_title()         the_content()
get_header()            get_footer()            get_sidebar()
get_template_part()     the_menu($location)     widget_area($slug)
site_url($path)         theme_url($path)        parent_theme_url($path)
get_option($key)        sp_bloginfo($key)       get_custom_field($key)
sp_enqueue_style()      sp_enqueue_script()     the_seo_tags()
```

### CSS custom properties výchozího tématu
```css
:root {
  --color-primary: #2563eb;  --color-text: #1f2937;
  --color-bg: #ffffff;       --color-muted: #6b7280;
  --color-border: #e5e7eb;
  --font-sans: system-ui, sans-serif;
  --container-width: 72rem;  --radius: 0.375rem;
}
```

---

## A9. Admin panel

### Role a oprávnění
| Akce | Admin | Editor | Viewer |
|---|---|---|---|
| Správa stránek | ✓ | ✓ | ✗ |
| Publikování | ✓ | ✓ | ✗ |
| Média | ✓ | ✓ | ✗ |
| Menu / Widgety | ✓ | ✗ | ✗ |
| Uživatelé | ✓ | ✗ | ✗ |
| Nastavení / Pluginy | ✓ | ✗ | ✗ |
| Čtení obsahu | ✓ | ✓ | ✓ |

### Admin UI pravidla
- Pouze Tailwind utility třídy, žádné inline styly
- Vlastní komponenty v `admin.css` přes `@layer components`
- Čistý vanilla JS, žádné frameworky
- Inline validace formulářů
- Autosave draftu do localStorage každých 30s
- Potvrzení před destruktivní akcí
- Flash zprávy po každé akci
- Breadcrumbs v navigaci
- Prázdné stavy s CTA

---

## A10. Routing

```php
// Frontend
$app->get('/', [FrontendController::class, 'home']);
$app->get('/{slug:.*}', [FrontendController::class, 'page']);

// Admin (za AuthMiddleware)
$app->group('/admin', function ($group) {
    $group->get('', [DashboardController::class, 'index']);
    $group->get('/pages', [PageController::class, 'index']);
    $group->get('/pages/new', [PageController::class, 'create']);
    $group->post('/pages', [PageController::class, 'store']);
    $group->get('/pages/{id}/edit', [PageController::class, 'edit']);
    $group->put('/pages/{id}', [PageController::class, 'update']);
    $group->delete('/pages/{id}', [PageController::class, 'destroy']);
})->add(AuthMiddleware::class);
```

---

## A11. Bezpečnost

### Autentizace
- Hesla: `password_hash(PASSWORD_BCRYPT)`, cost 12
- `session_regenerate_id(true)` po přihlášení
- Cookie: `HttpOnly`, `SameSite=Strict`, `Secure` (prod)
- Timeout: 2h inaktivity
- Brute force: 5 pokusů → 15min lockout (tabulka `login_attempts`)
- Remember me: bezpečný token v DB, nikdy session ID do cookie

### CSRF
- Hidden `_csrf_token` ve všech admin formulářích
- Token vázán na session, rotuje po submitu
- CsrfMiddleware ověřuje všechny POST/PUT/DELETE v `/admin`
- AJAX: hlavička `X-CSRF-Token`

### Vstup a výstup
- Veškerý výstup do HTML: `htmlspecialchars()` / `esc_html()`
- Výjimka: blok `custom_html` — pouze admin
- Upload: whitelist MIME typů, přejmenování na hash, mimo `public/` pokud možno

### HTTP hlavičky (SecurityHeadersMiddleware)
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
Content-Security-Policy: default-src 'self'; ...
```

---

## A12. Migrační systém

### Architektura
```
MigrationManager
├── CoreMigrationRunner     # plná práva, database/migrations/
└── PluginMigrationRunner   # sandbox, plugins/{slug}/Migrations/
```

### Tabulka migrations
```sql
id, context VARCHAR, migration VARCHAR, batch INT, executed_at TIMESTAMP
```

### Base třída Migration
```php
abstract class Migration
{
    protected Schema $schema;
    abstract public function up(): void;
    abstract public function down(): void;
}
```

### Plugin sandbox — povolený prefix
- Slug `contact-form` → prefix `contact_form_`
- Zachycené metody: create, table, drop, dropIfExists, rename
- MigrationSandboxException při porušení

### Životní cyklus
| Událost | Akce |
|---|---|
| Aktivace | up() nových migrací |
| Deaktivace | migrace se nespouštějí |
| Odinstalace | volitelně down() (admin musí potvrdit) |
| Update | up() nových migrací |

---

## A13. Dependency Injection (PHP-DI 7)

```php
// config/container.php
$builder->addDefinitions([
    PageRepositoryInterface::class  => \DI\autowire(EloquentPageRepository::class),
    UserRepositoryInterface::class  => \DI\autowire(EloquentUserRepository::class),
    MediaRepositoryInterface::class => \DI\autowire(EloquentMediaRepository::class),
    \Psr\Log\LoggerInterface::class => \DI\factory(function () { /* Monolog */ }),
    \Morgo\Core\HookManager::class  => \DI\create()->constructor(),
]);
```

---

## A14. Vrstvená architektura (taktické DDD)

```
Http/          ← přijímá HTTP, deleguje dál (tenké controllers)
Application/   ← orchestruje use case (Command Handlers)
Domain/        ← čistá byznys logika, žádné framework závislosti
Infrastructure/← implementace interfaces (Eloquent, Flysystem, Monolog)
Core/          ← HookManager, PluginLoader, ThemeEngine, I18n
```

**Pravidla:**
- `Domain/` nesmí importovat nic z `Infrastructure/`, `Http/`, Slim, Eloquent
- `Application/` smí importovat `Domain/`, ne `Http/` ani `Infrastructure/` přímo
- `Http/` Controllers max ~20 řádků
- `Infrastructure/` implementuje interfaces z `Domain/`

**Tok požadavku:**
```
POST /admin/pages
→ PageController::store()         # Http/ — parsuje request, vytvoří Command
→ CreatePageHandler::handle()     # Application/ — orchestruje
→ Page::create()                  # Domain/ — byznys logika
→ PageRepositoryInterface::save() # Domain/ interface
→ EloquentPageRepository::save()  # Infrastructure/ — DB
→ sp_do_action('page.saved')      # Core/ — hook
→ redirect()                      # Http/ — response
```

---

## A15. i18n API

```php
__('Save changes', 'morgocms')           // překlad
_e('Save changes', 'morgocms')           // překlad + echo
__f('Hello, %s!', 'morgocms', $name)     // překlad s proměnnou
_n('%d item', '%d items', $count, 'morgocms')  // pluralizace
sp_load_textdomain('domain', $path)      // registrace překladů
```

- JSON soubory: `lang/cs_CZ.json`, `themes/X/lang/cs_CZ.json`, `plugins/X/lang/cs_CZ.json`
- Fallback: cs_CZ → cs → původní anglický řetězec
- Jádro psáno anglicky — anglické řetězce = překladové klíče
- Multijazyčnost obsahu = plugin `morgocms-multilang` (není v MVP jádru)

---

## A16. PSR standardy

| PSR | Použití |
|---|---|
| PSR-1 | Všechny PHP soubory |
| PSR-3 | Logger (Monolog) |
| PSR-4 | Composer autoload, namespace `Morgo\` |
| PSR-7 | Slim request/response |
| PSR-11 | PHP-DI container |
| PSR-12 | Coding standard (phpcs) |
| PSR-15 | Slim middleware |

---

## A17. Konvence kódu

- `declare(strict_types=1)` na každém souboru
- PSR-12 standard (CI: `phpcs --standard=PSR12 app/`)
- Namespace root: `Morgo\`
- Value Objects: `final`, `readonly` kde možné
- Interfaces first: Domain definuje, Infrastructure implementuje
- PSR-3 Logger: vždy `LoggerInterface`, nikdy `new Logger()` v business kódu
- PSR-7 HTTP: Slim request/response, nikdy `$_GET`/`$_POST` v Controllers
- Žádné `static` metody v Domain/Application (kromě named constructors)
- Žádné globální proměnné — výjimka jen `sp_*` helpers (wrappery nad DI)

---

## A18. Testování (PHPUnit 11)

### Struktura
```
tests/
├── Unit/          # bez DB, bez HTTP — rychlé
├── Integration/   # SQLite in-memory
├── Feature/       # HTTP testy přes Slim test client
└── bootstrap.php
```

### Konfigurace (phpunit.xml)
- APP_ENV=testing, DB_CONNECTION=sqlite, DB_DATABASE=:memory:

### Pokrývat:
- HookManager (addAction, addFilter, priorita, neexistující hook)
- PluginMigrationSandbox (povolený prefix, core tabulka → výjimka)
- BlockRenderer (paragraph, header, unknown type filter, XSS escape)
- RoleManager (admin vše, editor ne plugins/users, viewer jen read)
- PageRepository (CRUD, SQLite)
- Login (brute force, session regenerate)

---

## A19. Instalační wizard (6 kroků)

1. Kontrola prostředí (PHP >= 8.2, rozšíření, zapisovatelné adresáře)
2. DB přihlašovací údaje + test připojení (AJAX)
3. Spuštění migrací (progress)
4. Vytvoření admin účtu
5. Nastavení webu (název, URL)
6. Hotovo (tlačítka: Admin / Web)

Po instalaci: `config/app.php` s `installed = true`, blokování `/install` (404).

---

## A20. Otevřené otázky

- Cache strategie: file cache vs. Redis — nerozhodnuto
- Admin URL prefix: výchozí `/admin`, konfigurovatelné — zvážit náhodný prefix při instalaci
- Plugin `morgocms-multilang`: součást MVP nebo až po něm?
