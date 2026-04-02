<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Core\Flash;
use Morgo\Domain\User\Role;
use Morgo\Domain\User\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly Capsule $capsule,
    ) {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user            = $request->getAttribute('currentUser');
        $users           = $this->users->findAll();
        $contentTemplate = BASE_PATH . '/admin/templates/users/index.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user            = $request->getAttribute('currentUser');
        $editUser        = null;
        $contentTemplate = BASE_PATH . '/admin/templates/users/form.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        if (empty($body['email']) || empty($body['password'])) {
            Flash::error('Vyplňte e-mail a heslo.');
            return $response->withHeader('Location', admin_url('users/new'))->withStatus(302);
        }

        $newUser               = new \Morgo\Domain\User\User();
        $newUser->email        = strtolower(trim($body['email']));
        $newUser->password_hash = password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $newUser->display_name = trim($body['display_name'] ?? $body['email']);
        $newUser->role         = $body['role'] ?? Role::Editor->value;

        $this->users->save($newUser);
        Flash::success('Uživatel byl vytvořen.');
        return $response->withHeader('Location', admin_url('users'))->withStatus(302);
    }

    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user            = $request->getAttribute('currentUser');
        $editUser        = $this->users->findById((int) $args['id']);
        if (!$editUser) {
            return $response->withStatus(404);
        }
        $contentTemplate = BASE_PATH . '/admin/templates/users/form.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body     = (array) $request->getParsedBody();
        $editUser = $this->users->findById((int) $args['id']);
        if (!$editUser) {
            return $response->withStatus(404);
        }

        $editUser->display_name = trim($body['display_name'] ?? $editUser->display_name);
        $editUser->role         = $body['role'] ?? $editUser->role;
        if (!empty($body['password'])) {
            $editUser->password_hash = password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        $this->users->save($editUser);
        Flash::success('Uživatel byl uložen.');
        return $response->withHeader('Location', admin_url('users'))->withStatus(302);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $currentUser = $request->getAttribute('currentUser');
        if ((int) $args['id'] === $currentUser->id) {
            Flash::error('Nemůžete smazat sami sebe.');
            return $response->withHeader('Location', admin_url('users'))->withStatus(302);
        }
        $this->users->delete((int) $args['id']);
        Flash::success('Uživatel byl smazán.');
        return $response->withHeader('Location', admin_url('users'))->withStatus(302);
    }
}
