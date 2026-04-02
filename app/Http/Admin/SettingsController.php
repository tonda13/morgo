<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Core\Flash;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SettingsController
{
    public function __construct(private readonly Capsule $capsule)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user            = $request->getAttribute('currentUser');
        $options         = $this->capsule->table('options')->get()->pluck('option_value', 'option_key')->toArray();
        $contentTemplate = BASE_PATH . '/admin/templates/settings/index.php';
        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $response->getBody()->write(ob_get_clean());
        return $response;
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body    = (array) $request->getParsedBody();
        $allowed = ['site_name', 'site_description', 'admin_email', 'site_language'];

        foreach ($allowed as $key) {
            if (isset($body[$key])) {
                $this->capsule->table('options')->updateOrInsert(
                    ['option_key' => $key],
                    ['option_value' => $body[$key]]
                );
            }
        }

        Flash::success('Nastavení bylo uloženo.');
        return $response->withHeader('Location', admin_url('settings'))->withStatus(302);
    }
}
