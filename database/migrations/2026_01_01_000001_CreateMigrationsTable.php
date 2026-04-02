<?php

declare(strict_types=1);

use Morgo\Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMigrationsTable extends Migration
{
    public function up(): void
    {
        if ($this->schema->hasTable('migrations')) {
            return;
        }

        $this->schema->create('migrations', function (Blueprint $table) {
            $table->id();
            $table->string('context')->default('core')->index();
            $table->string('migration');
            $table->integer('batch');
            $table->timestamp('executed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('migrations');
    }
}
