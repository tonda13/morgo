<?php

declare(strict_types=1);

use Morgo\Core\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOptionsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('options', function (Blueprint $table) {
            $table->string('option_key')->primary();
            $table->longText('option_value')->nullable();
        });

        // Výchozí hodnoty
        \Illuminate\Database\Capsule\Manager::table('options')->insert([
            ['option_key' => 'site_name',        'option_value' => 'Morgo'],
            ['option_key' => 'site_description', 'option_value' => ''],
            ['option_key' => 'site_language',    'option_value' => 'cs_CZ'],
            ['option_key' => 'active_theme',     'option_value' => 'default'],
            ['option_key' => 'admin_email',      'option_value' => ''],
        ]);
    }

    public function down(): void
    {
        $this->schema->dropIfExists('options');
    }
}
