<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'debugging718@gmail.com',
            'password' => bcrypt('password')
        ]);

        $SuperAdminRole = Role::create(['name' => 'Super Admin']);
        $AdminRole = Role::create(['name' => 'Admin']);
        $VisualizzatoreRole = Role::create(['name' => 'Visualizzatore']);

        // Use the permissions from file_context_0
        $permissions = [
            'can-see',
            'can-see-all',
            'can-change',
            'content-move',
            'content-copy',
            'content-delete',
            'content-share',
            'create-folder',
            'upload-file',
            'restore-content',
            'permanent-delete-content',
            'view-content',
            'create-user',
            'delete-user',
            'update-user',
            'view-user',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
        ];

        $SuperAdminRolePermissions = $permissions;
        $AdminRolePermissions = array_slice($permissions, 0, 12);
        $VisualizzatoreRolePermissions = [
            'can-see',
            'view-content',
        ];

        $SuperAdminRole->syncPermissions($SuperAdminRolePermissions);
        $AdminRole->syncPermissions($AdminRolePermissions);
        $VisualizzatoreRole->syncPermissions($VisualizzatoreRolePermissions);

        $user->assignRole([$SuperAdminRole->id]);
    }
}
