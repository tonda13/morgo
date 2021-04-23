<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE);

require_once('vendor/autoload.php');

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$request = ServerRequest::fromGlobals();
pre_dump($request);

$queue = [
    new Morgo\Middleware\Test,
    function (ServerRequestInterface $request, callable $next) : ResponseInterface {
        return new Response(404, [], 'Not Found');
    }
];

$dispatcher = Morgo\Factory\MiddlewareDispatcherFactory::create($queue);
$response = $dispatcher->handle($request);

$resolver = new Morgo\App\ResponseResolver;
$resolver->resolve($response);
