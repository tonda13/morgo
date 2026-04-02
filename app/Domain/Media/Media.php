<?php

declare(strict_types=1);

namespace Morgo\Domain\Media;

class Media
{
    public int $id;
    public string $filename;
    public string $filepath;
    public string $mime_type;
    public int $file_size;
    public ?string $alt_text;
    public ?string $caption;
    public ?int $uploaded_by;
    public string $created_at;
    public string $updated_at;

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getPublicUrl(): string
    {
        return site_url('content/uploads/' . $this->filepath);
    }

    public function getFormattedSize(): string
    {
        $size = $this->file_size;
        if ($size < 1024) return $size . ' B';
        if ($size < 1048576) return round($size / 1024, 1) . ' KB';
        return round($size / 1048576, 1) . ' MB';
    }

    public static function fromArray(array $data): self
    {
        $m = new self();
        $m->id          = (int) $data['id'];
        $m->filename    = $data['filename'];
        $m->filepath    = $data['filepath'];
        $m->mime_type   = $data['mime_type'];
        $m->file_size   = (int) ($data['file_size'] ?? 0);
        $m->alt_text    = $data['alt_text'] ?? null;
        $m->caption     = $data['caption'] ?? null;
        $m->uploaded_by = isset($data['uploaded_by']) ? (int) $data['uploaded_by'] : null;
        $m->created_at  = $data['created_at'] ?? date('Y-m-d H:i:s');
        $m->updated_at  = $data['updated_at'] ?? date('Y-m-d H:i:s');
        return $m;
    }
}
