<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE);

require_once('vendor/autoload.php');

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Morgo\Mvc\AppRouter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$request = ServerRequest::fromGlobals();

//Create the router dispatcher
$router = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $collector) {
    $collector->addRoute(['GET','POST'], '/', function ($request, $b) {
        _d(get_class($request), get_class($b));
//        return $response->getBody()->write('Hello World!');
        return 'Hello World!';
    });
    $routes = require('config/routes.php');
    foreach ($routes as $route) {
        $collector->addRoute(...$route);
    }
});


$queue = [
    new Middlewares\FastRoute($router),
    new Morgo\Middleware\Test,
    new Middlewares\RequestHandler,
    function (ServerRequestInterface $request, callable $next) : ResponseInterface {
        return new Response(404, [], 'Not Found');
    }
];

$dispatcher = Morgo\Factory\MiddlewareDispatcherFactory::create($queue);
$response = $dispatcher->handle($request);

$resolver = new Morgo\App\ResponseResolver;
$resolver->resolve($response);
