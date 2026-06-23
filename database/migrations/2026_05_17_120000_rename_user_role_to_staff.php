<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('name', 'user')->update(['name' => 'staff']);
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'staff')->update(['name' => 'user']);
    }
};
