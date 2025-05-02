<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([
            'name' => 'get-list-user',
            'description' => 'returns list of registered users',
            'code' => 'GLU',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'read-userRole',
            'description' => 'returns info about user`s roles',
            'code' => 'ReadUR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'create-userRole',
            'description' => 'gives a role to User',
            'code' => 'CUR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'update-userRole',
            'description' => 'updates a User`s role',
            'code' => 'UUR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'update-user',
            'description' => 'updates a User`s info',
            'code' => 'UU',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'delete-userRole',
            'description' => 'deletes a User`s role',
            'code' => 'DUR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'restore-userRole',
            'description' => 'restores a User`s role',
            'code' => 'RestoreUR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'get-list-role',
            'description' => 'returns list of roles',
            'code' => 'GLR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'read-role',
            'description' => 'returns info of specific role',
            'code' => 'ReadR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'create-role',
            'description' => 'creates a new role',
            'code' => 'CR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'update-role',
            'description' => 'updates an existing role',
            'code' => 'UR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'delete-role',
            'description' => 'deletes an existing role',
            'code' => 'DR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'restore-role',
            'description' => 'restores an existing role',
            'code' => 'RestoreR',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'get-list-permission',
            'description' => 'returns a list of permissions',
            'code' => 'GLP',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'read-permission',
            'description' => 'returns info of specific permission',
            'code' => 'RP',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'create-permission',
            'description' => 'creates a new permisson',
            'code' => 'CP',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'update-permission',
            'description' => 'update an existing permisson',
            'code' => 'UP',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'delete-permission',
            'description' => 'deletes an existing permisson',
            'code' => 'DP',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);

        Permission::create([
            'name' => 'restore-permission',
            'description' => 'restores an existing permisson',
            'code' => 'RestoreP',
            'created_at' => Carbon::now(),
            'created_by' => 0,
        ]);
    }
}
