<?php

use Morgo\Mvc\AppRouter;

return [
    [
        ['GET','POST', 'DELETE', 'PUT', 'PATCH', 'HEAD'],
        '/rest/{version}/{controller}/{action}',
        [new AppRouter, 'route']
    ],
    [['GET','POST'], '/admin/{controller}/{action}/[{id:\d+}]', [new AppRouter, 'route']],
    [['GET','POST'], '/{controller}/{action}', [new AppRouter, 'route']],
];
