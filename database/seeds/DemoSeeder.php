<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as DB;

class DemoSeeder
{
    public function run(): void
    {
        // Demo admin uživatel
        DB::table('users')->insertOrIgnore([
            'email'         => 'admin@example.com',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]),
            'display_name'  => 'Admin',
            'role'          => 'admin',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        // Homepage
        DB::table('pages')->insertOrIgnore([
            'slug'           => '/',
            'title'          => 'Vítejte na Morgo',
            'status'         => 'published',
            'content_blocks' => json_encode([
                'time'    => time() * 1000,
                'version' => '2.29.0',
                'blocks'  => [
                    [
                        'id'   => 'demo1',
                        'type' => 'header',
                        'data' => ['text' => 'Vítejte na Morgo', 'level' => 1],
                    ],
                    [
                        'id'   => 'demo2',
                        'type' => 'paragraph',
                        'data' => ['text' => 'Morgo je lehký CMS framework inspirovaný WordPressem. Začněte úpravou této stránky v administraci.'],
                    ],
                ],
            ]),
            'menu_order'     => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        // Ukázková stránka
        DB::table('pages')->insertOrIgnore([
            'slug'           => 'o-nas',
            'title'          => 'O nás',
            'status'         => 'published',
            'content_blocks' => json_encode([
                'time'    => time() * 1000,
                'version' => '2.29.0',
                'blocks'  => [
                    [
                        'id'   => 'about1',
                        'type' => 'header',
                        'data' => ['text' => 'O nás', 'level' => 1],
                    ],
                    [
                        'id'   => 'about2',
                        'type' => 'paragraph',
                        'data' => ['text' => 'Toto je ukázková stránka. Upravte ji podle potřeby.'],
                    ],
                ],
            ]),
            'menu_order'     => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        echo "Demo data vložena.\n";
    }
}
