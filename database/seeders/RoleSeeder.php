<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'superadmin',
            'admin',
            'audit',
            'encoder',
            'delinquent',
            'member'
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web'); // 'web' is the default guard
        }

        //assign all permissions to superadmin
        $superadminRole = Role::findByName('superadmin', 'web');
        $permissions = \Spatie\Permission\Models\Permission::all();
        foreach ($permissions as $permission) {
            $superadminRole->givePermissionTo($permission);
        }
    }
}
