<?php

declare(strict_types=1);

namespace Morgo\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly bool $debug = false
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $this->logger->error('Unhandled exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'url'     => (string) $request->getUri(),
                'method'  => $request->getMethod(),
            ]);

            $response = $this->responseFactory->createResponse(500);

            if ($this->debug) {
                $body = '<h1>Error 500</h1><pre>'
                    . htmlspecialchars($e->getMessage())
                    . "\n"
                    . htmlspecialchars($e->getTraceAsString())
                    . '</pre>';
            } else {
                $body = '<h1>Interní chyba serveru</h1><p>Prosím zkuste to znovu později.</p>';
            }

            $response->getBody()->write($body);
            return $response;
        }
    }
}
