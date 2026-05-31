<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage-users', 'manage-roles', 'manage-schools', 'manage-groups',
            'manage-courses', 'manage-lessons', 'manage-quizzes', 'manage-questions',
            'manage-payments', 'manage-access-grants', 'manage-payment-settings',
            'manage-homepage', 'manage-notifications', 'manage-announcements',
            'manage-backups', 'manage-operations', 'manage-integrations',
            'manage-library', 'manage-live-sessions', 'view-reports',
            'manage-skills', 'manage-paths', 'manage-foundation',
            'approve-content', 'approve-payments',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['guard_name' => 'web', 'name' => $permission]);
        }

        $admin = Role::create(['guard_name' => 'web', 'name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $teacher = Role::create(['guard_name' => 'web', 'name' => 'teacher']);
        $teacher->givePermissionTo([
            'manage-courses', 'manage-lessons', 'manage-quizzes', 'manage-questions',
            'manage-skills', 'manage-library', 'manage-live-sessions',
        ]);

        $supervisor = Role::create(['guard_name' => 'web', 'name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'manage-courses', 'manage-lessons', 'manage-quizzes', 'manage-questions',
            'manage-users', 'manage-schools', 'manage-groups',
            'manage-access-grants', 'view-reports',
        ]);

        $student = Role::create(['guard_name' => 'web', 'name' => 'student']);
        $parent = Role::create(['guard_name' => 'web', 'name' => 'parent']);
    }
}
