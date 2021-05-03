<?php
declare(strict_types=1);

namespace Morgo\Mvc;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class AppRouter
{
    public function route(ServerRequestInterface $request) {
        $controller = $request->getAttribute('controller');
        $action = $request->getAttribute('action');
        $id = $request->getAttribute('id');

//        $responseBody = sprintf('Controller = %s, action = %s, ID = %s', $controller, $action, $id);

        $controllerClassName = __NAMESPACE__ . '\\Controller\\' . $controller;
        if (class_exists($controllerClassName)) {
            $controllerInstance = new $controllerClassName();
            $methodName = str_replace(['-', '_', ' '], '', $action);
            if (is_callable([$controllerInstance, $methodName])) {
                $controllerInstance->$methodName();

            }
        }

        return new Response(200, [], $responseBody ?? '');
    }
}