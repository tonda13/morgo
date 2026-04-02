<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Morgo\Application\Page\CreatePage\CreatePageCommand;
use Morgo\Application\Page\CreatePage\CreatePageHandler;
use Morgo\Application\Page\DeletePage\DeletePageCommand;
use Morgo\Application\Page\DeletePage\DeletePageHandler;
use Morgo\Application\Page\UpdatePage\UpdatePageCommand;
use Morgo\Application\Page\UpdatePage\UpdatePageHandler;
use Morgo\Core\Flash;
use Morgo\Domain\Page\PageRepositoryInterface;
use Morgo\Domain\Page\PageStatus;
use Morgo\Domain\Page\Slug;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PageController
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly CreatePageHandler $createHandler,
        private readonly UpdatePageHandler $updateHandler,
        private readonly DeletePageHandler $deleteHandler,
    ) {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user    = $request->getAttribute('currentUser');
        $params  = $request->getQueryParams();
        $filters = ['status' => $params['status'] ?? null, 'search' => $params['search'] ?? null];
        $pages   = $this->pages->findAll(array_filter($filters));
        $contentTemplate = BASE_PATH . '/admin/templates/pages/index.php';

        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        return $this->html($response, ob_get_clean());
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user    = $request->getAttribute('currentUser');
        $page    = null;
        $allPages = $this->pages->findAll();
        $contentTemplate = BASE_PATH . '/admin/templates/pages/form.php';

        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        return $this->html($response, ob_get_clean());
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('currentUser');
        $body = (array) $request->getParsedBody();

        try {
            $slug = $this->resolveSlug($body['slug'] ?? '', $body['title'] ?? '');
            $cmd  = new CreatePageCommand(
                title:           trim($body['title'] ?? ''),
                slug:            $slug,
                status:          $body['status'] ?? PageStatus::Draft->value,
                contentBlocks:   $body['content_blocks'] ?: null,
                template:        $body['template'] ?: null,
                metaTitle:       trim($body['meta_title'] ?? '') ?: null,
                metaDescription: trim($body['meta_description'] ?? '') ?: null,
                parentId:        ($body['parent_id'] ?? '') ? (int) $body['parent_id'] : null,
                menuOrder:       (int) ($body['menu_order'] ?? 0),
                createdBy:       $user->id,
            );
            $page = $this->createHandler->handle($cmd);
            Flash::success('Stránka byla vytvořena.');
            return $this->redirect($response, admin_url("pages/{$page->id}/edit"));
        } catch (\Throwable $e) {
            Flash::error('Chyba: ' . $e->getMessage());
            return $this->redirect($response, admin_url('pages/new'));
        }
    }

    public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = $request->getAttribute('currentUser');
        $page = $this->pages->findById((int) $args['id']);
        if ($page === null) {
            return $response->withStatus(404);
        }
        $allPages = $this->pages->findAll();
        $contentTemplate = BASE_PATH . '/admin/templates/pages/form.php';

        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        return $this->html($response, ob_get_clean());
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $id   = (int) $args['id'];

        try {
            $slug = $this->resolveSlug($body['slug'] ?? '', $body['title'] ?? '', $id);
            $cmd  = new UpdatePageCommand(
                id:              $id,
                title:           trim($body['title'] ?? ''),
                slug:            $slug,
                status:          $body['status'] ?? PageStatus::Draft->value,
                contentBlocks:   $body['content_blocks'] ?: null,
                template:        $body['template'] ?: null,
                metaTitle:       trim($body['meta_title'] ?? '') ?: null,
                metaDescription: trim($body['meta_description'] ?? '') ?: null,
                parentId:        ($body['parent_id'] ?? '') ? (int) $body['parent_id'] : null,
                menuOrder:       (int) ($body['menu_order'] ?? 0),
            );
            $this->updateHandler->handle($cmd);
            Flash::success('Stránka byla uložena.');
        } catch (\Throwable $e) {
            Flash::error('Chyba: ' . $e->getMessage());
        }

        return $this->redirect($response, admin_url("pages/{$id}/edit"));
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->deleteHandler->handle(new DeletePageCommand((int) $args['id']));
        Flash::success('Stránka byla smazána.');
        return $this->redirect($response, admin_url('pages'));
    }

    public function draft(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Autosave endpoint — vrátí JSON
        $body   = (array) $request->getParsedBody();
        $pageId = (int) ($body['id'] ?? 0);
        if ($pageId > 0) {
            $page = $this->pages->findById($pageId);
            if ($page) {
                $page->content_blocks = $body['content_blocks'] ?? $page->content_blocks;
                $this->pages->save($page);
            }
        }
        $response->getBody()->write(json_encode(['ok' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function resolveSlug(string $slugInput, string $title, ?int $excludeId = null): string
    {
        $slug = $slugInput ? (new Slug($slugInput))->value : Slug::fromTitle($title)->value;
        // Zajistit unikátnost
        $original = $slug;
        $i = 1;
        while ($this->pages->exists($slug, $excludeId)) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    private function redirect(ResponseInterface $response, string $url): ResponseInterface
    {
        return $response->withHeader('Location', $url)->withStatus(302);
    }

    private function html(ResponseInterface $response, string $html): ResponseInterface
    {
        $response->getBody()->write($html);
        return $response;
    }
}
