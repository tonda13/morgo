<?php

declare(strict_types=1);

use Morgo\Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePagesTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->enum('status', ['draft', 'published', 'private'])->default('draft')->index();
            $table->string('template')->nullable();
            $table->longText('content_blocks')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->integer('menu_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('pages')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        $this->schema->create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->index();
            $table->string('field_key');
            $table->text('field_value')->nullable();
            $table->unique(['page_id', 'field_key']);
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('custom_fields');
        $this->schema->dropIfExists('pages');
    }
}
