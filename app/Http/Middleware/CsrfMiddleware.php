<?php

declare(strict_types=1);

namespace Morgo\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    private const EXEMPT_PATHS = [
        '/install',
    ];

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();
        foreach (self::EXEMPT_PATHS as $exempt) {
            if (str_starts_with($path, $exempt)) {
                return $handler->handle($request);
            }
        }

        if (!$this->validateToken($request)) {
            $response = $this->responseFactory->createResponse(403);
            $response->getBody()->write('CSRF token validation failed.');
            return $response;
        }

        // Token rotace po úspěšném submitu
        csrf_rotate();

        return $handler->handle($request);
    }

    private function validateToken(ServerRequestInterface $request): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['_csrf_token'] ?? null;
        if ($sessionToken === null) {
            return false;
        }

        // Zkusit z body nebo z hlavičky
        $body        = (array) $request->getParsedBody();
        $bodyToken   = $body['_csrf_token'] ?? null;
        $headerToken = $request->getHeaderLine('X-CSRF-Token') ?: null;

        $submitted = $bodyToken ?? $headerToken;
        if ($submitted === null) {
            return false;
        }

        return hash_equals($sessionToken, $submitted);
    }
}
