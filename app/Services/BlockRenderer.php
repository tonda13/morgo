<?php

declare(strict_types=1);

namespace Morgo\Services;

use Morgo\Core\HookManager;

class BlockRenderer
{
    public function __construct(private readonly HookManager $hooks)
    {
    }

    public function render(array $data): string
    {
        $html = '';

        foreach ($data['blocks'] ?? [] as $block) {
            $type    = $block['type'] ?? 'unknown';
            $blockHtml = match ($type) {
                'paragraph' => $this->paragraph($block['data'] ?? []),
                'header'    => $this->header($block['data'] ?? []),
                'image'     => $this->image($block['data'] ?? []),
                'list'      => $this->list($block['data'] ?? []),
                'quote'     => $this->quote($block['data'] ?? []),
                'delimiter' => '<hr class="sp-delimiter">',
                'raw'       => $this->raw($block['data'] ?? []),
                'table'     => $this->table($block['data'] ?? []),
                'embed'     => $this->embed($block['data'] ?? []),
                default     => $this->hooks->applyFilters('render_block_' . $type, '', $block),
            };

            $html .= $blockHtml;
        }

        return $this->hooks->applyFilters('the_content', $html);
    }

    private function paragraph(array $data): string
    {
        $text = $data['text'] ?? '';
        // EditorJS paragraph může obsahovat povolené inline HTML tagy (<b>, <i>, <a>, <code>)
        $text = $this->allowInlineTags($text);
        return "<p>{$text}</p>\n";
    }

    private function header(array $data): string
    {
        $text  = htmlspecialchars($data['text'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $level = max(1, min(6, (int) ($data['level'] ?? 2)));
        return "<h{$level}>{$text}</h{$level}>\n";
    }

    private function image(array $data): string
    {
        $url     = htmlspecialchars($data['url'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $caption = htmlspecialchars($data['caption'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $alt     = htmlspecialchars($data['alt'] ?? $data['caption'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $classes = 'sp-image';
        if (!empty($data['withBorder'])) {
            $classes .= ' sp-image--border';
        }
        if (!empty($data['stretched'])) {
            $classes .= ' sp-image--stretched';
        }
        if (!empty($data['withBackground'])) {
            $classes .= ' sp-image--background';
        }

        $html = '<figure class="' . $classes . '">';
        $html .= '<img src="' . $url . '" alt="' . $alt . '" loading="lazy">';
        if ($caption) {
            $html .= '<figcaption>' . $caption . '</figcaption>';
        }
        $html .= "</figure>\n";

        return $html;
    }

    private function list(array $data): string
    {
        $style = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
        $items = $data['items'] ?? [];

        $html = "<{$style} class=\"sp-list sp-list--{$style}\">\n";
        foreach ($items as $item) {
            $text  = $this->allowInlineTags(is_array($item) ? ($item['content'] ?? '') : $item);
            $html .= "<li>{$text}</li>\n";
        }
        $html .= "</{$style}>\n";

        return $html;
    }

    private function quote(array $data): string
    {
        $text    = $this->allowInlineTags($data['text'] ?? '');
        $caption = htmlspecialchars($data['caption'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $align   = in_array($data['alignment'] ?? '', ['left', 'center'], true) ? $data['alignment'] : 'left';

        $html  = "<blockquote class=\"sp-quote sp-quote--{$align}\">";
        $html .= "<p>{$text}</p>";
        if ($caption) {
            $html .= "<cite>{$caption}</cite>";
        }
        $html .= "</blockquote>\n";

        return $html;
    }

    private function raw(array $data): string
    {
        // Raw HTML — pouze pro admin/editor role, nikdy pro veřejnost bez kontroly
        // Vrátí html tak jak je — caller odpovídá za kontrolu role
        return ($data['html'] ?? '') . "\n";
    }

    private function table(array $data): string
    {
        $rows       = $data['content'] ?? [];
        $withHeader = $data['withHeadings'] ?? false;

        if (empty($rows)) {
            return '';
        }

        $html = "<div class=\"sp-table-wrapper\"><table class=\"sp-table\">\n";

        foreach ($rows as $i => $row) {
            $tag   = ($withHeader && $i === 0) ? 'th' : 'td';
            $html .= "<tr>";
            foreach ($row as $cell) {
                $cell  = htmlspecialchars($cell, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $html .= "<{$tag}>{$cell}</{$tag}>";
            }
            $html .= "</tr>\n";
        }

        $html .= "</table></div>\n";
        return $html;
    }

    private function embed(array $data): string
    {
        $service = htmlspecialchars($data['service'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $embed   = htmlspecialchars($data['embed'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $width   = (int) ($data['width'] ?? 560);
        $height  = (int) ($data['height'] ?? 315);
        $caption = htmlspecialchars($data['caption'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (!$embed) {
            return '';
        }

        $html  = "<figure class=\"sp-embed sp-embed--{$service}\">";
        $html .= "<iframe src=\"{$embed}\" width=\"{$width}\" height=\"{$height}\" frameborder=\"0\" allowfullscreen loading=\"lazy\"></iframe>";
        if ($caption) {
            $html .= "<figcaption>{$caption}</figcaption>";
        }
        $html .= "</figure>\n";

        return $html;
    }

    /**
     * Povolí pouze bezpečné inline HTML tagy (b, i, em, strong, a, code, mark, u, s).
     * Vše ostatní escapuje.
     */
    private function allowInlineTags(string $html): string
    {
        return strip_tags($html, '<b><i><em><strong><a><code><mark><u><s><br>');
    }
}
