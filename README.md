# Morgo

![Morgo logo](public/assets/images/logo.png)

PHP webová aplikace postavená na Slim 4 frameworku, běžící v Dockeru.

## Technologie

- **PHP 8.4** + Apache
- **Slim 4** — micro framework
- **PHP-DI 7** — dependency injection s autowiring
- **slim/php-view** — PHP šablony s layoutem
- **MySQL 8.4**
- **Adminer** — správa databáze

## Požadavky

- Docker
- Docker Compose

## Spuštění

**1. Klonování repozitáře**
```bash
git clone git@github.com:tonda13/morgo.git
cd morgo
```

**2. Konfigurace prostředí**
```bash
cp .env.example .env
```

**3. Sestavení a spuštění kontejnerů**
```bash
docker compose up --build
```

Aplikace je dostupná na [http://localhost:8080](http://localhost:8080).

## Přístupové údaje

| Služba   | URL                                         |
|----------|---------------------------------------------|
| Aplikace | http://localhost:8080                       |
| Adminer  | http://localhost:8081                       |
| MySQL    | localhost:3306                              |

Výchozí přihlašovací údaje k databázi jsou definované v `.env` souboru (viz `.env.example`).

## Pomocné skripty

Skripty jsou umístěny ve složce `bin/`:

```bash
# Přihlášení do kontejneru (výchozí uživatel: www-data)
./bin/app
./bin/app root

# Spuštění Composer příkazů
./bin/composer install
./bin/composer require <balicek>
```

## Struktura projektu

```
├── bin/                  # Pomocné shell skripty
├── config/
│   └── container.php     # PHP-DI definice závislostí
├── docker/
│   ├── apache/           # Konfigurace Apache virtual hostu
│   └── php/              # Konfigurace PHP (php.ini)
├── public/               # Document root (entry point)
│   ├── assets/
│   │   ├── css/
│   │   └── images/
│   └── index.php
├── src/
│   └── Controller/       # Controllery (PSR-4: App\)
├── templates/            # PHP šablony
│   ├── layout.php        # Společný layout
│   └── home.php
├── .env.example
├── composer.json
├── docker-compose.yml
└── Dockerfile
```
