<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',

            'can-see',
            'can-see-all',
            'can-change',

            'create-user',
            'delete-user',
            'update-user',
            'view-user',

            'content-move',
            'content-copy',
            'content-delete',
            'content-share',

            'create-folder',
            'upload-file',

            'restore-content',
            'permanent-delete-content',
            'view-content',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
