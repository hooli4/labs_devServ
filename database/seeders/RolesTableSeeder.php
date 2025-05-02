<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'description' => 'All rights',
            'code' => 'ADM',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Role::create([
            'name' => 'user',
            'description' => 'Only read users list, read and update own profile',
            'code' => 'USR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Role::create([
            'name' => 'guest',
            'description' => 'Only read users list',
            'code' => 'GST',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);
    }
}
