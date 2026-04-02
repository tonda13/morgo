<?php

declare(strict_types=1);

namespace Morgo\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Throwable;

class ErrorHandler extends SlimErrorHandler
{
    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory
    ) {
        parent::__construct($callableResolver, $responseFactory);
    }

    protected function respond(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->statusCode);
        $response->getBody()->write($this->renderErrorPage());
        return $response->withHeader('Content-Type', 'text/html');
    }

    private function renderErrorPage(): string
    {
        $code = $this->statusCode;
        $message = match ($code) {
            404 => 'Stránka nenalezena',
            403 => 'Přístup odepřen',
            500 => 'Chyba serveru',
            default => 'Nastala chyba',
        };

        return <<<HTML
        <!DOCTYPE html>
        <html lang="cs">
        <head><meta charset="UTF-8"><title>{$code} — {$message}</title>
        <style>body{font-family:system-ui,sans-serif;text-align:center;padding:4rem;color:#374151}
        h1{font-size:4rem;color:#9ca3af;margin-bottom:0}p{color:#6b7280}</style>
        </head>
        <body><h1>{$code}</h1><p>{$message}</p><a href="/">Zpět na hlavní stránku</a></body>
        </html>
        HTML;
    }
}
