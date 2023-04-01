<?php
declare(strict_types=1);

namespace Morgo\Mvc;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AppRouter
{
    public function singlePage(ServerRequestInterface $request): ResponseInterface {
        $controller = 'page';
        $action = $request->getAttribute('page');
        $arguments[] = $request->getAttribute('id');

        $controllerClassName = __NAMESPACE__ . '\\Controller\\' . ucfirst($controller);
        if (class_exists($controllerClassName)) {
            $controllerInstance = new $controllerClassName($request);
            $methodName = str_replace(['-', '_', ' '], '', $action);
            if (is_callable([$controllerInstance, $methodName])) {
                return $controllerInstance->$methodName(...$arguments);
            } else {
                // TODO return 404 because the method (action) does not exist
            }
        } else {
            // TODO return 404 because class (controller) does not exist
        }
    }
    
    public function route(ServerRequestInterface $request): ResponseInterface {
        $controller = $request->getAttribute('controller');
        $action = $request->getAttribute('action');
        $arguments[] = $request->getAttribute('id');

        $responseBody = sprintf('Controller = %s, action = %s, ID = %s', $controller, $action, implode(', ', $arguments));

        $controllerClassName = __NAMESPACE__ . '\\Controller\\' . ucfirst($controller);
        if (class_exists($controllerClassName)) {
            $controllerInstance = new $controllerClassName($request);
            $methodName = str_replace(['-', '_', ' '], '', $action);
            if (is_callable([$controllerInstance, $methodName])) {
                return $controllerInstance->$methodName(...$arguments);
            }
        }

        return new Response(200, [], $responseBody ?? '');
    }
}