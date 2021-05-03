<?php

use Morgo\Mvc\AppRouter;

return [
    [['GET','POST'], '/admin/{controller}/{action}/[{id:\d+}]', [new AppRouter, 'route']],
    [['GET','POST'], '/{controller}/{action}', [new AppRouter, 'route']],
];
