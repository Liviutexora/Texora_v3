<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();

        $permission_names = [];

        // Build rows
        if($permission_names){
            $rows = array_map(function ($name) use ($now) {
                return [
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $permission_names);
    
            // Insert in chunks (skip duplicates)
            $chunks = array_chunk($rows, 100);
            foreach ($chunks as $chunk) {
                DB::table('permissions')->insertOrIgnore($chunk);
            }
    
            $this->command->info('Inserted or skipped duplicates for ' . count($permission_names) . ' permissions.');
        }
    }

}
