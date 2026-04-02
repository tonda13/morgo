<?php

declare(strict_types=1);

use Morgo\Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('display_name');
            $table->enum('role', ['admin', 'editor', 'viewer'])->default('editor');
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });

        $this->schema->create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('ip', 45);
            $table->timestamp('attempted_at')->useCurrent();
            $table->index(['email', 'attempted_at']);
        });

        $this->schema->create('remember_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('remember_tokens');
        $this->schema->dropIfExists('login_attempts');
        $this->schema->dropIfExists('users');
    }
}
