<?php
declare(strict_types=1);

namespace Morgo\Mvc;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class AppRouter
{
//    public function route(ServerRequestInterface $request) {
//        $controllerInstance = $this->getControllerInstance($request);
//
//        $action = $request->getAttribute('action');
//        $methodName = str_replace(['-', '_', ' '], '', $action);
//        if (is_callable([$controllerInstance, $methodName])) {
//            return $controllerInstance->$methodName();
//        }
//    }
//
//    public function detail(ServerRequestInterface $request) {
//
//    }
//
//    public function create(ServerRequestInterface $request) {
//
//    }
//
//    private function getControllerInstance(ServerRequestInterface $request) : AbstractController {
//        $controller = $request->getAttribute('controller');
//        $controllerClassName = __NAMESPACE__ . '\\Controller\\' . $controller;
//        if (class_exists($controllerClassName)) {
//            return new $controllerClassName($request);
//        }
//    }
    
    public function route(ServerRequestInterface $request) {
        $controller = $request->getAttribute('controller');
        $action = $request->getAttribute('action');
        $arguments[] = $request->getAttribute('id');

//        $responseBody = sprintf('Controller = %s, action = %s, ID = %s', $controller, $action, $id);

        $controllerClassName = __NAMESPACE__ . '\\Controller\\' . $controller;
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