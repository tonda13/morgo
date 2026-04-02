<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Illuminate\Database\Capsule\Manager as DB;
use Morgo\Core\Flash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class CustomFieldController
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $pageId = (int) $args['id'];
        $user   = $request->getAttribute('currentUser');

        $page = DB::table('pages')->find($pageId);
        if ($page === null) {
            return $response->withStatus(404);
        }

        $fields = DB::table('custom_fields')
            ->where('page_id', $pageId)
            ->orderBy('id')
            ->get();

        $pageTitle   = 'Custom Fields: ' . $page->title;
        $breadcrumbs = [
            ['label' => 'Stránky', 'url' => admin_url('pages')],
            ['label' => $page->title, 'url' => admin_url("pages/{$pageId}/edit")],
            ['label' => 'Custom Fields'],
        ];
        $contentTemplate = BASE_PATH . '/admin/templates/custom-fields/index.php';

        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $pageId = (int) $args['id'];
        $data   = (array) $request->getParsedBody();
        $key    = trim($data['field_key'] ?? '');
        $value  = $data['field_value'] ?? '';

        if ($key === '') {
            Flash::error('Klíč custom field nesmí být prázdný.');
            return $this->redirect($response, admin_url("pages/{$pageId}/fields"));
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $key)) {
            Flash::error('Klíč custom field smí obsahovat pouze alfanumerické znaky, podtržítko a pomlčku.');
            return $this->redirect($response, admin_url("pages/{$pageId}/fields"));
        }

        $page = DB::table('pages')->find($pageId);
        if ($page === null) {
            return $response->withStatus(404);
        }

        $existing = DB::table('custom_fields')
            ->where('page_id', $pageId)
            ->where('field_key', $key)
            ->first();

        if ($existing !== null) {
            DB::table('custom_fields')
                ->where('id', $existing->id)
                ->update(['field_value' => $value]);
            Flash::success("Custom field '{$key}' byl aktualizován.");
        } else {
            DB::table('custom_fields')->insert([
                'page_id'     => $pageId,
                'field_key'   => $key,
                'field_value' => $value,
            ]);
            Flash::success("Custom field '{$key}' byl přidán.");
        }

        $this->logger->info('Custom field saved', ['page_id' => $pageId, 'key' => $key]);

        return $this->redirect($response, admin_url("pages/{$pageId}/fields"));
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $pageId  = (int) $args['id'];
        $fieldId = (int) $args['fieldId'];

        $field = DB::table('custom_fields')
            ->where('id', $fieldId)
            ->where('page_id', $pageId)
            ->first();

        if ($field === null) {
            Flash::error('Custom field nebyl nalezen.');
            return $this->redirect($response, admin_url("pages/{$pageId}/fields"));
        }

        DB::table('custom_fields')->where('id', $fieldId)->delete();

        $this->logger->info('Custom field deleted', ['page_id' => $pageId, 'field_id' => $fieldId]);

        Flash::success('Custom field byl smazán.');
        return $this->redirect($response, admin_url("pages/{$pageId}/fields"));
    }

    private function redirect(Response $response, string $url): Response
    {
        return $response->withHeader('Location', $url)->withStatus(302);
    }
}
