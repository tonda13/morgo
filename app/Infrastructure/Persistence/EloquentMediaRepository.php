<?php

declare(strict_types=1);

namespace Morgo\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as Capsule;
use Morgo\Domain\Media\Media;
use Morgo\Domain\Media\MediaRepositoryInterface;

class EloquentMediaRepository implements MediaRepositoryInterface
{
    public function __construct(private readonly Capsule $capsule)
    {
    }

    public function findById(int $id): ?Media
    {
        $row = $this->capsule->table('media')->find($id);
        return $row ? Media::fromArray((array) $row) : null;
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->capsule->table('media')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn($row) => Media::fromArray((array) $row))
            ->all();
    }

    public function count(): int
    {
        return $this->capsule->table('media')->count();
    }

    public function save(Media $media): Media
    {
        $data = [
            'filename'    => $media->filename,
            'filepath'    => $media->filepath,
            'mime_type'   => $media->mime_type,
            'file_size'   => $media->file_size,
            'alt_text'    => $media->alt_text,
            'caption'     => $media->caption,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if (isset($media->id) && $media->id > 0) {
            $this->capsule->table('media')->where('id', $media->id)->update($data);
        } else {
            $data['uploaded_by'] = $media->uploaded_by;
            $data['created_at']  = date('Y-m-d H:i:s');
            $media->id = (int) $this->capsule->table('media')->insertGetId($data);
        }

        return $media;
    }

    public function delete(int $id): void
    {
        $this->capsule->table('media')->where('id', $id)->delete();
    }
}
