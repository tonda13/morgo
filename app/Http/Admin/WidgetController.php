<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Core\Flash;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WidgetController
{
    public function __construct(private readonly Capsule $capsule)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user         = $request->getAttribute('currentUser');
        $widgetAreas  = $GLOBALS['_morgo_widget_areas'] ?? [];
        $widgets      = $this->capsule->table('widgets')->orderBy('area_slug')->orderBy('widget_order')->get()->toArray();
        $contentTemplate = BASE_PATH . '/admin/templates/widgets/index.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $this->capsule->table('widgets')->insert([
            'area_slug'    => $body['area_slug'] ?? '',
            'widget_type'  => $body['widget_type'] ?? 'text',
            'title'        => $body['title'] ?? '',
            'config'       => json_encode($body['config'] ?? []),
            'widget_order' => (int) ($body['widget_order'] ?? 0),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        Flash::success('Widget přidán.');
        return $response->withHeader('Location', admin_url('widgets'))->withStatus(302);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $this->capsule->table('widgets')->where('id', (int) $args['id'])->update([
            'title'      => $body['title'] ?? '',
            'config'     => json_encode($body['config'] ?? []),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        Flash::success('Widget uložen.');
        return $response->withHeader('Location', admin_url('widgets'))->withStatus(302);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->capsule->table('widgets')->where('id', (int) $args['id'])->delete();
        Flash::success('Widget smazán.');
        return $response->withHeader('Location', admin_url('widgets'))->withStatus(302);
    }

    public function reorder(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $order = json_decode($body['order'] ?? '[]', true) ?: [];
        foreach ($order as $i => $id) {
            $this->capsule->table('widgets')->where('id', (int) $id)->update(['widget_order' => $i]);
        }
        $response->getBody()->write(json_encode(['ok' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
