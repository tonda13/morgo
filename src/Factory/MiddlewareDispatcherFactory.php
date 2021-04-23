<?php

namespace Morgo\Factory;

use Relay\Relay;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareDispatcherFactory {

    public static function create(array $queue): RequestHandlerInterface  {
        return new Relay($queue);
    }
}
