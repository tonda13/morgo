<?php

declare(strict_types=1);

namespace Morgo\Core\Migration;

use Illuminate\Database\Schema\Builder as Schema;

abstract class Migration
{
    protected Schema $schema;

    public function setSchema(Schema $schema): void
    {
        $this->schema = $schema;
    }

    abstract public function up(): void;

    abstract public function down(): void;
}
