<?php

declare(strict_types=1);

namespace Morgo\Http\Middleware;

use Morgo\Core\Auth\RoleManager;
use Morgo\Domain\User\User;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CapabilityMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $capability,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var User|null $user */
        $user = $request->getAttribute('currentUser');

        if ($user === null || !RoleManager::can($user, $this->capability)) {
            $response = $this->responseFactory->createResponse(403);
            $response->getBody()->write('<h1>403 — Přístup odepřen</h1>');
            return $response;
        }

        return $handler->handle($request);
    }
}
