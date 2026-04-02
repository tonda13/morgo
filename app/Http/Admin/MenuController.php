<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Morgo\Core\Flash;
use Morgo\Domain\Menu\Menu;
use Morgo\Domain\Menu\MenuRepositoryInterface;
use Morgo\Domain\Page\PageRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MenuController
{
    public function __construct(
        private readonly MenuRepositoryInterface $menus,
        private readonly PageRepositoryInterface $pages,
    ) {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user    = $request->getAttribute('currentUser');
        $menus   = $this->menus->findAll();
        $contentTemplate = BASE_PATH . '/admin/templates/menus/index.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user    = $request->getAttribute('currentUser');
        $menu    = null;
        $allPages = $this->pages->findAll();
        $contentTemplate = BASE_PATH . '/admin/templates/menus/form.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $menu = new Menu();
        $menu->name = trim($body['name'] ?? '');
        $menu->slug = trim($body['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $menu->name)));
        $menu = $this->menus->save($menu);
        Flash::success('Menu bylo vytvořeno.');
        return $response->withHeader('Location', admin_url("menus/{$menu->id}/edit"))->withStatus(302);
    }

    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user    = $request->getAttribute('currentUser');
        $menu    = $this->menus->findById((int) $args['id']);
        if (!$menu) return $response->withStatus(404);
        $allPages = $this->pages->findAll();
        $contentTemplate = BASE_PATH . '/admin/templates/menus/form.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body  = (array) $request->getParsedBody();
        $menu  = $this->menus->findById((int) $args['id']);
        if (!$menu) return $response->withStatus(404);
        $menu->name = trim($body['name'] ?? $menu->name);
        $this->menus->save($menu);
        $items = json_decode($body['items'] ?? '[]', true) ?: [];
        $this->menus->saveItems($menu->id, $items);

        // Uložit přiřazení lokací
        $allLocations = array_keys($GLOBALS['_morgo_nav_menus'] ?? []);
        $selectedLocations = (array) ($body['locations'] ?? []);
        foreach ($allLocations as $locationSlug) {
            if (in_array($locationSlug, $selectedLocations, true)) {
                update_option('menu_location_' . $locationSlug, (string) $menu->id);
            } elseif ((int) get_option('menu_location_' . $locationSlug, 0) === $menu->id) {
                update_option('menu_location_' . $locationSlug, '0');
            }
        }

        Flash::success('Menu bylo uloženo.');
        return $response->withHeader('Location', admin_url("menus/{$menu->id}/edit"))->withStatus(302);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->menus->delete((int) $args['id']);
        Flash::success('Menu bylo smazáno.');
        return $response->withHeader('Location', admin_url('menus'))->withStatus(302);
    }
}
