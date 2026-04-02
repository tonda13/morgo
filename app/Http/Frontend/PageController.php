<?php

declare(strict_types=1);

namespace Morgo\Http\Frontend;

use Morgo\Core\ThemeEngine;
use Morgo\Domain\Page\PageRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PageController
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly ThemeEngine $themeEngine,
    ) {
    }

    public function home(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $homeSlug = get_option('homepage_slug', 'home');
        $page     = $this->pages->findBySlug($homeSlug);

        global $morgoPage;
        $morgoPage = $page;

        sp_do_action('page.query', $page);

        $templateFile = $this->themeEngine->resolveHomeTemplate();
        $html         = $this->themeEngine->render($templateFile, ['page' => $page]);

        $response->getBody()->write($html);
        return $response;
    }

    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? '';
        $page = $this->pages->findBySlug($slug);

        if ($page === null || (!$page->isPublished() && !$this->isAdmin())) {
            return $this->notFound($response);
        }

        global $morgoPage;
        $morgoPage = $page;

        sp_do_action('page.query', $page);

        $templateFile = $this->themeEngine->resolveTemplate($page);
        $html         = $this->themeEngine->render($templateFile, ['page' => $page]);

        $response->getBody()->write($html);
        return $response;
    }

    private function notFound(ResponseInterface $response): ResponseInterface
    {
        $themeDir = BASE_PATH . '/themes/' . active_theme_slug();
        $file404  = file_exists($themeDir . '/404.php') ? $themeDir . '/404.php' : null;

        $response = $response->withStatus(404);

        if ($file404) {
            ob_start();
            require $file404;
            $response->getBody()->write(ob_get_clean());
        } else {
            $response->getBody()->write('<h1>404 — Stránka nenalezena</h1>');
        }

        return $response;
    }

    private function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['_morgo_user_id']);
    }
}
