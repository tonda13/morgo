<?php

declare(strict_types=1);

namespace Morgo\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MethodOverrideMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $body   = (array) $request->getParsedBody();
            $method = strtoupper($body['_method'] ?? '');

            if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true)) {
                $request = $request->withMethod($method);
            }
        }

        return $handler->handle($request);
    }
}
