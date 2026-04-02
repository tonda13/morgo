<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Morgo\Core\Flash;
use Morgo\Core\PluginLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PluginController
{
    public function __construct(private readonly PluginLoader $pluginLoader)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user            = $request->getAttribute('currentUser');
        $plugins         = $this->pluginLoader->getAllPlugins();
        $contentTemplate = BASE_PATH . '/admin/templates/plugins/index.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function activate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $ok = $this->pluginLoader->activate($args['slug']);
        Flash::success($ok ? 'Plugin byl aktivován.' : 'Plugin nebyl nalezen.');
        return $response->withHeader('Location', admin_url('plugins'))->withStatus(302);
    }

    public function deactivate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $ok = $this->pluginLoader->deactivate($args['slug']);
        Flash::success($ok ? 'Plugin byl deaktivován.' : 'Chyba.');
        return $response->withHeader('Location', admin_url('plugins'))->withStatus(302);
    }
}
