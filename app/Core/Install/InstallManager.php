<?php

declare(strict_types=1);

namespace Morgo\Core\Install;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Core\Migration\CoreMigrationRunner;
use Psr\Log\LoggerInterface;

class InstallManager
{
    public function __construct(
        private readonly Capsule $capsule,
        private readonly LoggerInterface $logger
    ) {
    }

    public function testDbConnection(array $dbConfig): bool
    {
        try {
            $capsule = new Capsule();
            $capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => $dbConfig['host'],
                'port'      => (int) $dbConfig['port'],
                'database'  => $dbConfig['database'],
                'username'  => $dbConfig['username'],
                'password'  => $dbConfig['password'],
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $capsule->getConnection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function runMigrations(): array
    {
        $runner = new CoreMigrationRunner($this->capsule, $this->logger);
        return $runner->run();
    }

    public function createAdminUser(string $email, string $password, string $displayName): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Smazat existujícího admina se stejným emailem
        $this->capsule->table('users')->where('email', strtolower($email))->delete();

        return (int) $this->capsule->table('users')->insertGetId([
            'email'         => strtolower($email),
            'password_hash' => $hash,
            'display_name'  => $displayName,
            'role'          => 'admin',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function saveOptions(string $siteName, string $siteUrl, string $adminEmail): void
    {
        $options = [
            'site_name'        => $siteName,
            'site_url'         => $siteUrl,
            'admin_email'      => $adminEmail,
            'site_language'    => 'cs_CZ',
            'active_theme'     => 'default',
        ];

        foreach ($options as $key => $value) {
            $this->capsule->table('options')->updateOrInsert(
                ['option_key' => $key],
                ['option_value' => $value]
            );
        }
    }

    public function writeConfigFile(array $dbConfig, string $siteUrl): void
    {
        $configContent = <<<PHP
<?php
// Tento soubor byl vygenerován instalačním wizardem Morgo.
// Neupravujte ručně — použijte admin UI nebo .env soubor.

define('MORGO_INSTALLED', true);
PHP;

        file_put_contents(BASE_PATH . '/config/installed.php', $configContent);

        // Aktualizovat .env pokud existuje, nebo vytvořit nový
        $envContent = $this->generateEnvContent($dbConfig, $siteUrl);
        file_put_contents(BASE_PATH . '/.env', $envContent);
    }

    public function isInstalled(): bool
    {
        return file_exists(BASE_PATH . '/config/installed.php');
    }

    private function generateEnvContent(array $dbConfig, string $siteUrl): string
    {
        $appKey = bin2hex(random_bytes(32));

        return <<<ENV
APP_NAME=Morgo
APP_ENV=production
APP_DEBUG=false
APP_URL={$siteUrl}
APP_KEY={$appKey}

DB_CONNECTION=mysql
DB_HOST={$dbConfig['host']}
DB_PORT={$dbConfig['port']}
DB_DATABASE={$dbConfig['database']}
DB_USERNAME={$dbConfig['username']}
DB_PASSWORD={$dbConfig['password']}

ADMIN_PREFIX=admin
SESSION_LIFETIME=120
SESSION_SECURE=false
LOG_LEVEL=warning
ENV;
    }
}
