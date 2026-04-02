<?php

declare(strict_types=1);

namespace Morgo\Http\Install;

use Morgo\Core\Install\InstallManager;
use Morgo\Core\Install\RequirementsChecker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class InstallController
{
    public function __construct(
        private readonly InstallManager $installManager,
        private readonly RequirementsChecker $requirementsChecker
    ) {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($this->installManager->isInstalled()) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $response->withHeader('Location', '/install/step-1')->withStatus(302);
    }

    public function step(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ($this->installManager->isInstalled()) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $step = $args['step'] ?? 'step-1';
        return $this->renderStep($response, $step);
    }

    public function process(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ($this->installManager->isInstalled()) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $step = $args['step'] ?? 'step-1';
        $body = (array) $request->getParsedBody();

        // Uložit data do session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return match ($step) {
            'step-2' => $this->processDatabase($request, $response, $body),
            'step-3' => $this->processMigrations($request, $response),
            'step-4' => $this->processAdmin($request, $response, $body),
            'step-5' => $this->processSettings($request, $response, $body),
            default  => $response->withHeader('Location', '/install/step-1')->withStatus(302),
        };
    }

    public function testDb(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $ok   = $this->installManager->testDbConnection([
            'host'     => $body['db_host']     ?? '127.0.0.1',
            'port'     => $body['db_port']     ?? '3306',
            'database' => $body['db_database'] ?? '',
            'username' => $body['db_username'] ?? '',
            'password' => $body['db_password'] ?? '',
        ]);

        $response->getBody()->write(json_encode(['ok' => $ok]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function processDatabase(ServerRequestInterface $request, ResponseInterface $response, array $body): ResponseInterface
    {
        $dbConfig = [
            'host'     => $body['db_host']     ?? '127.0.0.1',
            'port'     => $body['db_port']     ?? '3306',
            'database' => $body['db_database'] ?? '',
            'username' => $body['db_username'] ?? '',
            'password' => $body['db_password'] ?? '',
        ];

        if (!$this->installManager->testDbConnection($dbConfig)) {
            $_SESSION['install_error'] = 'Nepodařilo se připojit k databázi. Zkontrolujte přihlašovací údaje.';
            return $response->withHeader('Location', '/install/step-2')->withStatus(302);
        }

        $_SESSION['install_db'] = $dbConfig;
        return $response->withHeader('Location', '/install/step-3')->withStatus(302);
    }

    private function processMigrations(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $ran = $this->installManager->runMigrations();
            $_SESSION['install_migrations'] = $ran;
        } catch (\Throwable $e) {
            $_SESSION['install_error'] = 'Chyba migrace: ' . $e->getMessage();
            return $response->withHeader('Location', '/install/step-3')->withStatus(302);
        }

        return $response->withHeader('Location', '/install/step-4')->withStatus(302);
    }

    private function processAdmin(ServerRequestInterface $request, ResponseInterface $response, array $body): ResponseInterface
    {
        $email       = trim($body['admin_email']   ?? '');
        $password    = $body['admin_password']      ?? '';
        $displayName = trim($body['display_name']  ?? '');

        if (empty($email) || empty($password) || empty($displayName)) {
            $_SESSION['install_error'] = 'Vyplňte všechna pole.';
            return $response->withHeader('Location', '/install/step-4')->withStatus(302);
        }

        if (strlen($password) < 8) {
            $_SESSION['install_error'] = 'Heslo musí mít alespoň 8 znaků.';
            return $response->withHeader('Location', '/install/step-4')->withStatus(302);
        }

        $_SESSION['install_admin'] = compact('email', 'password', 'displayName');
        return $response->withHeader('Location', '/install/step-5')->withStatus(302);
    }

    private function processSettings(ServerRequestInterface $request, ResponseInterface $response, array $body): ResponseInterface
    {
        $siteName = trim($body['site_name'] ?? 'Morgo');
        $siteUrl  = rtrim(trim($body['site_url'] ?? site_url()), '/');

        $admin    = $_SESSION['install_admin'] ?? null;
        $dbConfig = $_SESSION['install_db'] ?? null;

        if ($admin === null || $dbConfig === null) {
            return $response->withHeader('Location', '/install/step-1')->withStatus(302);
        }

        try {
            $adminId = $this->installManager->createAdminUser(
                $admin['email'],
                $admin['password'],
                $admin['displayName']
            );

            $this->installManager->saveOptions($siteName, $siteUrl, $admin['email']);
            $this->installManager->writeConfigFile($dbConfig, $siteUrl);

            $_SESSION['install_done'] = true;
            return $response->withHeader('Location', '/install/step-6')->withStatus(302);
        } catch (\Throwable $e) {
            $_SESSION['install_error'] = 'Chyba při uložení: ' . $e->getMessage();
            return $response->withHeader('Location', '/install/step-5')->withStatus(302);
        }
    }

    private function renderStep(ResponseInterface $response, string $step): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = $_SESSION['install_error'] ?? null;
        unset($_SESSION['install_error']);

        $templateFile = BASE_PATH . '/install/templates/' . $step . '.php';
        if (!file_exists($templateFile)) {
            return $response->withHeader('Location', '/install/step-1')->withStatus(302);
        }

        $checker = $this->requirementsChecker;
        ob_start();
        require BASE_PATH . '/install/templates/layout.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }
}
