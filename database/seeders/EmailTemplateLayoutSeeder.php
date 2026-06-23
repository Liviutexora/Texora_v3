<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateLayoutSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('email_template_layouts')->insert([
            [
                'name' => 'Default Layout',
                'body' => <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{SITE_NAME}} - Notification</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background: #0d6efd; padding: 20px; text-align: center; color: #fff; }
        .header img { max-height: 40px; margin-bottom: 10px; }
        .body { padding: 20px; }
        .footer { background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{SITE_LOGO}}" alt="{{SITE_NAME}} Logo">
            <h2>{{SITE_NAME}}</h2>
        </div>

        <div class="body">
            {{BODY}}
        </div>

        <div class="footer">
            &copy; {{CURRENT_YEAR}} {{SITE_NAME}}. All rights reserved. <br>
            Contact: {{SITE_EMAIL}} | {{SITE_PHONE}}
        </div>
    </div>
</body>
</html>
HTML,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
