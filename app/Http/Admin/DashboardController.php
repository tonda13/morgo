<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardController
{
    public function __construct(private readonly Capsule $capsule)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user  = $request->getAttribute('currentUser');
        $stats = [
            'pages'   => $this->capsule->table('pages')->count(),
            'media'   => $this->capsule->table('media')->count(),
            'users'   => $this->capsule->table('users')->count(),
        ];

        $recentPages = $this->capsule->table('pages')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        $contentTemplate = BASE_PATH . '/admin/templates/dashboard/index.php';

        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }
}
