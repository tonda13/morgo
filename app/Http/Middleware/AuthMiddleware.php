<?php

declare(strict_types=1);

namespace Morgo\Http\Middleware;

use Morgo\Core\Auth\AuthManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthManager $auth,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->auth->isLoggedIn()) {
            $loginUrl = '/' . trim(config('app.admin_prefix', 'admin'), '/') . '/login';
            $response = $this->responseFactory->createResponse(302);
            return $response->withHeader('Location', $loginUrl);
        }

        // Přidat aktuálního uživatele do request attributes
        $user    = $this->auth->getCurrentUser();
        $request = $request->withAttribute('currentUser', $user);

        return $handler->handle($request);
    }
}
