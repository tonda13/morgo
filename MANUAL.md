# MorgoCMS — Uživatelská příručka

Základní příručka pro správu webu postaveného na MorgoCMS.

---

## 1. Spuštění a instalace

### Požadavky

- Docker a Docker Compose
- PHP 8.2+ (pokud spouštíte mimo Docker)
- Node.js 18+ a npm (pro sestavení assets)

### Spuštění vývojového prostředí

```bash
docker-compose up -d
```

Po spuštění jsou dostupné tyto adresy:

- **Web:** http://localhost
- **Adminer (správa DB):** http://localhost:8080

### Průvodce instalací (5 kroků)

Pokud MorgoCMS ještě není nainstalováno, http://localhost vás automaticky přesměruje na `/install`.

Průvodce vás provede těmito kroky:

1. **Kontrola požadavků** — ověří PHP verzi, rozšíření a oprávnění adresářů
2. **Připojení k databázi** — zadejte host, název DB, uživatele a heslo
3. **Spuštění migrací** — vytvoří všechny potřebné tabulky
4. **Vytvoření admin účtu** — jméno, e-mail, heslo
5. **Základní nastavení webu** — název webu, URL, časové pásmo

Po dokončení instalace budete přesměrováni na http://localhost.

> **Tip:** Instalaci lze spustit i z příkazové řádky: `php bin/sp install`

---

## 2. Administrace

### Přístup do adminu

Otevřete http://localhost/admin a přihlaste se svým e-mailem a heslem.

> Bezpečnost: po 5 neúspěšných pokusech o přihlášení je účet zablokován na 15 minut.

### Přehled sekcí administrace

| Sekce | Popis |
|-------|-------|
| **Přehled** | Dashboard s přehledem obsahu a stavu webu |
| **Stránky** | Správa stránek — vytváření, editace, mazání |
| **Média** | Nahrávání a správa obrázků a souborů |
| **Menu** | Tvorba navigačních menu a jejich přiřazení k lokacím tématu |
| **Widgety** | Správa widgetů v postranním panelu a patičce |
| **Uživatelé** | Správa uživatelských účtů a rolí |
| **Pluginy** | Přehled, aktivace a deaktivace pluginů |
| **Témata** | Výběr a aktivace grafických témat |
| **Nastavení** | Obecná nastavení webu (název, homepage, jazyk…) |

---

## 3. Správa stránek

### Vytvoření nové stránky

1. V adminu přejděte na **Stránky → Přidat novou**
2. Zadejte titulek stránky
3. Napište obsah pomocí blokového editoru
4. Nastavte slug, nadřazenou stránku a stav
5. Klikněte na **Uložit**

### Blokový editor (EditorJS)

Editor pracuje s bloky. Dostupné typy bloků:

| Blok | Popis |
|------|-------|
| **Odstavec** | Běžný text |
| **Nadpis** | H1–H6 |
| **Obrázek** | Obrázek z média nebo URL |
| **Seznam** | Odrážkový nebo číslovaný |
| **Citát** | Zvýrazněný citát s autorem |
| **Oddělovač** | Horizontální linka |
| **Tabulka** | Tabulka s libovolným počtem sloupců a řádků |
| **Embed** | Vložení videa (YouTube, Vimeo…) nebo jiného obsahu |

Nový blok přidáte kliknutím na tlačítko `+` mezi bloky nebo stisknutím klávesy **Enter** na konci bloku.

### Nahrání obrázku přes Media Picker

V bloku obrázku klikněte na tlačítko **Vybrat z médií** — otevře se Media Picker, kde vyberete nebo nahrajete soubor.

### Nastavení stránky

- **Slug (URL):** krátký identifikátor stránky v adrese (automaticky generován z titulku, lze upravit)
- **Nadřazená stránka:** hierarchické zařazení stránky
- **Pořadí v menu:** číslo pro řazení stránek při programatickém výpisu (ORDER BY menu_order). Používá se v šablonách tématu při volání `findAll()` nebo iteraci podstránek — **nesouvisí s navigačními menu** vytvořenými přes Admin → Menu, která mají vlastní pořadí položek nastavitelné přetahováním.
- **Šablona:** pojmenovaná šablona tématu (pokud je dostupná)

### Stavy stránky

| Stav | Popis |
|------|-------|
| **Koncept** | Stránka není veřejně dostupná |
| **Publikováno** | Stránka je veřejně přístupná |
| **Soukromé** | Vidí pouze přihlášení uživatelé s příslušnými právy |

### Automatické ukládání

Editor automaticky ukládá koncept každých **30 sekund**. Při neočekávaném zavření prohlížeče se neuloží pouze poslední změny z posledních 30 sekund.

### Custom Fields (vlastní pole)

Každá stránka může mít vlastní klíč-hodnota pole (Custom Fields). Slouží pro libovolná metadata, která lze v šablonách čerpat pomocí:

```php
get_custom_field('nazev_pole');
```

Pole spravujete v sekci **Custom Fields** na stránce editace.

### SEO nastavení

Na stránce editace najdete sekci **SEO**:

- **Meta titulek** — titulek pro prohlížeče a vyhledávače (pokud prázdný, použije se titulek stránky)
- **Meta popis** — krátký popis pro vyhledávače a sdílení na sítích (Open Graph)

---

## 4. Menu

### Vytvoření menu

1. Přejděte na **Menu → Přidat nové**
2. Zadejte název menu (např. *Hlavní menu*)
3. Klikněte na **Uložit**

### Přidání položek

Na stránce editace menu:

- **Ze stránek:** vyberte stránky ze seznamu a klikněte na **Přidat do menu**
- **Vlastní URL:** zadejte libovolnou URL a popisek, klikněte na **Přidat**

Pořadí položek měníte přetahováním.

### Přiřazení menu k lokaci tématu

Aby se menu zobrazilo na webu, musíte ho přiřadit k lokaci tématu:

1. Otevřete menu v editaci
2. Najděte sekci **Umístění v tématu**
3. Zaškrtněte požadovanou lokaci (viz níže)
4. Klikněte na **Uložit**

### Dostupné lokace v default tématu

| Identifikátor | Název | Kde se zobrazuje |
|---------------|-------|-----------------|
| `primary` | Hlavní navigace | Horní lišta webu |
| `footer` | Patičková navigace | Patička webu |

---

## 5. Média

### Nahrání souborů

1. Přejděte na **Média → Nahrát**
2. Přetáhněte soubory nebo klikněte a vyberte ze systému

### Povolené typy souborů

Povoleny jsou pouze obrázky:

- `jpg` / `jpeg`
- `png`
- `gif`
- `webp`
- `svg`

Soubory jsou po nahrání přejmenovány na hashovaný název pro bezpečnost.

### Media Picker v editoru

Při přidávání bloku obrázku v editoru stránek klikněte na **Vybrat z médií** a vyberte existující soubor nebo nahrajte nový.

---

## 6. Uživatelé a role

### Role

| Role | Oprávnění |
|------|-----------|
| **admin** | Plný přístup — vše |
| **editor** | Správa stránek a médií |
| **viewer** | Přístup do administrace pouze pro čtení |

### Vytvoření nového uživatele

1. Přejděte na **Uživatelé → Přidat nového**
2. Vyplňte jméno, e-mail, heslo a vyberte roli
3. Klikněte na **Uložit**

---

## 7. Pluginy

### Přehled pluginů

Přejděte na **Pluginy** — zobrazí se seznam všech dostupných pluginů s informací, zda jsou aktivní nebo neaktivní.

### Aktivace a deaktivace

- Klikněte na **Aktivovat** / **Deaktivovat** u příslušného pluginu

Aktivace může spustit databázové migrace pluginu. Deaktivace migrace nesmaže.

---

## 8. Témata

### Přehled témat

Přejděte na **Témata** — zobrazí se seznam dostupných grafických témat.

### Aktivace tématu

Klikněte na **Aktivovat** u zvoleného tématu. Web okamžitě začne používat nové téma.

---

## 9. Časté problémy

**Q: Jak přidat menu aby bylo vidět na webu?**

A: Vytvoř menu v Admin → Menu. Ve formuláři editace menu je sekce „Umístění v tématu" — zaškrtni požadovanou lokaci (Hlavní navigace = `primary`) a ulož. Menu se pak zobrazí v navigaci tématu.

---

**Q: Po instalaci se zobrazuje prázdná nebo ošklivá stránka**

A: Je třeba mít výchozí stránku. Nová instalace automaticky vytváří stránku „Vítejte" (slug: `home`). Pokud chybí, vytvoř ji v Admin → Stránky a nastav ji jako homepage v Admin → Nastavení → Homepage slug.

---

**Q: CSS se nenačítá na instalační stránce nebo admin stránkách**

A: Zkontroluj zda existuje `public/build/style.css`. Pokud ne, spusť `npm run build`. Tento soubor je commitnutý do repozitáře a měl by vždy existovat.

---

**Q: Při přístupu na /admin dostanu 403 Forbidden**

A: Ujisti se že v `public/` neexistuje adresář `admin/`. Pokud existuje, smaž ho — Vite assets patří do `public/build/`, ne `public/admin/`.

---

**Q: Instalační průvodce hlásí „Zapisovatelný: config/ ✗"**

A: Adresář `config/` musí být zapisovatelný pro webserver. V Dockeru se to řeší automaticky přes entrypoint. Mimo Docker: `chmod 777 config/`

---

**Q: Po instalaci je adresář config/ vlastněný www-data a nelze ho editovat z hostu**

A: Docker Compose entrypoint používá `chmod 777 config/` místo `chown`, takže vlastnictví zůstává hostitelskému uživateli a oprávnění k zápisu má i webserver.
