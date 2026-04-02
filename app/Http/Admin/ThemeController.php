<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Morgo\Core\Flash;
use Morgo\Core\ThemeEngine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ThemeController
{
    public function __construct(private readonly ThemeEngine $themeEngine)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user            = $request->getAttribute('currentUser');
        $themes          = $this->themeEngine->getAllThemes();
        $active          = get_option('active_theme', 'default');
        $contentTemplate = BASE_PATH . '/admin/templates/themes/index.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function activate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $slug   = $args['slug'];
        $themes = $this->themeEngine->getAllThemes();

        if (!isset($themes[$slug])) {
            Flash::error('Téma nenalezeno.');
            return $response->withHeader('Location', admin_url('themes'))->withStatus(302);
        }

        update_option('active_theme', $slug);
        Flash::success("Téma \"{$themes[$slug]['name']}\" bylo aktivováno.");
        return $response->withHeader('Location', admin_url('themes'))->withStatus(302);
    }
}
