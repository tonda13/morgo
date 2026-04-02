<?php

declare(strict_types=1);

use Morgo\Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWidgetsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('area_slug')->index();
            $table->string('widget_type');
            $table->string('title')->nullable();
            $table->json('config')->nullable();
            $table->integer('widget_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('widgets');
    }
}
