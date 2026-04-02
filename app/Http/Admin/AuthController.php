<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Morgo\Core\Auth\AuthManager;
use Morgo\Core\Flash;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController
{
    public function __construct(
        private readonly AuthManager $auth
    ) {
    }

    public function loginForm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($this->auth->isLoggedIn()) {
            $adminUrl = '/' . trim(config('app.admin_prefix', 'admin'), '/');
            return $response->withHeader('Location', $adminUrl)->withStatus(302);
        }

        return $this->renderLogin($response);
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body     = (array) $request->getParsedBody();
        $email    = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';
        $remember = !empty($body['remember']);

        if (empty($email) || empty($password)) {
            Flash::error('Zadejte e-mail a heslo.');
            return $this->renderLogin($response, $email);
        }

        if ($this->auth->isLockedOut($email)) {
            Flash::error('Příliš mnoho neúspěšných pokusů. Zkuste to za 15 minut.');
            return $this->renderLogin($response, $email);
        }

        if (!$this->auth->login($email, $password, $remember)) {
            $remaining = $this->auth->getRemainingAttempts($email);
            Flash::error("Nesprávné přihlašovací údaje. Zbývá pokusů: {$remaining}");
            return $this->renderLogin($response, $email);
        }

        $adminUrl = '/' . trim(config('app.admin_prefix', 'admin'), '/');
        return $response->withHeader('Location', $adminUrl)->withStatus(302);
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->auth->logout();
        $loginUrl = '/' . trim(config('app.admin_prefix', 'admin'), '/') . '/login';
        return $response->withHeader('Location', $loginUrl)->withStatus(302);
    }

    private function renderLogin(ResponseInterface $response, string $email = ''): ResponseInterface
    {
        $flashes = Flash::get();
        ob_start();
        require BASE_PATH . '/admin/templates/auth/login.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }
}
