<?php

declare(strict_types=1);

namespace Morgo\Http\Admin;

use Morgo\Core\Flash;
use Morgo\Domain\Media\Media;
use Morgo\Domain\Media\MediaRepositoryInterface;
use Morgo\Domain\Media\MimeType;
use Morgo\Infrastructure\Storage\LocalMediaStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MediaController
{
    public function __construct(
        private readonly MediaRepositoryInterface $media,
        private readonly LocalMediaStorage $storage,
    ) {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user    = $request->getAttribute('currentUser');
        $page    = max(1, (int) ($request->getQueryParams()['page'] ?? 1));
        $limit   = 24;
        $offset  = ($page - 1) * $limit;
        $items   = $this->media->findAll($limit, $offset);
        $total   = $this->media->count();
        $pages   = (int) ceil($total / $limit);
        $contentTemplate = BASE_PATH . '/admin/templates/media/index.php';

        ob_start();
        require BASE_PATH . '/admin/templates/layout.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user  = $request->getAttribute('currentUser');
        $files = $request->getUploadedFiles();
        $file  = $files['file'] ?? null;

        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return $this->jsonError($response, 'Soubor nebyl nahrán.');
        }

        $mime = $file->getClientMediaType();
        try {
            new MimeType($mime);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($response, $e->getMessage());
        }

        try {
            $tmpPath = sys_get_temp_dir() . '/' . bin2hex(random_bytes(8));
            $file->moveTo($tmpPath);

            $stored = $this->storage->storeFromPath($tmpPath, $file->getClientFilename(), $mime);

            $media             = new Media();
            $media->filename   = $stored['filename'];
            $media->filepath   = $stored['filepath'];
            $media->mime_type  = $mime;
            $media->file_size  = $stored['file_size'];
            $media->alt_text   = null;
            $media->caption    = null;
            $media->uploaded_by = $user->id;

            $media = $this->media->save($media);

            $response->getBody()->write(json_encode([
                'ok'  => true,
                'id'  => $media->id,
                'url' => $media->getPublicUrl(),
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->jsonError($response, $e->getMessage());
        }
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $media = $this->media->findById((int) $args['id']);
        if ($media) {
            $this->storage->delete($media->filepath);
            $this->media->delete($media->id);
            Flash::success('Soubor byl smazán.');
        }
        return $response->withHeader('Location', admin_url('media'))->withStatus(302);
    }

    public function picker(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $items = $this->media->findAll(48, 0);
        ob_start();
        require BASE_PATH . '/admin/templates/media/picker.php';
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response;
    }

    private function jsonError(ResponseInterface $response, string $message): ResponseInterface
    {
        $response->getBody()->write(json_encode(['ok' => false, 'error' => $message]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
}
