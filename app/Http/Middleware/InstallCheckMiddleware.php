<?php

declare(strict_types=1);

namespace Morgo\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InstallCheckMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        // Přeskočit pro /install* routy (aby nedošlo k nekonečné smyčce)
        if (str_starts_with($path, '/install')) {
            return $handler->handle($request);
        }

        // Přeskočit pro statické soubory (CSS, JS, obrázky, fonty)
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|map)$/i', $path)) {
            return $handler->handle($request);
        }

        if (!file_exists(BASE_PATH . '/config/installed.php')) {
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', '/install');
        }

        return $handler->handle($request);
    }
}
