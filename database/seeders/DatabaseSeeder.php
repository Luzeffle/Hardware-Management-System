<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // create permission for spatie (dot-separated resource.action format)
        $permissions = [
            // Dashboard
            'dashboard.view',

            // POS / Sales
            'pos.access',
            'sales.create',
            'sales.view-history',
            'sales.refund',
            'sales.print-receipt',

            // Inventory
            'inventory.view-overview',
            'inventory.update',
            'inventory.view-movements',
            'inventory.archive',
            'inventory.delete',

            // Audit Logs
            'audit.user-activity.view',
            'audit.system-logs.view',

            // Reports
            'reports.view',

            // Users
            'users.create',
            'users.edit',
            'users.delete',
            'users.view-list',

            // Suppliers
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'suppliers.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles for spatie
        $adminRole = Role::firstOrCreate(['name'=>'admin']);
        $managerRole = Role::firstOrCreate(['name'=>'manager']);
        $cashierRole = Role::firstOrCreate(['name'=>'cashier']);

        // give permissions to role
        $adminRole->givePermissionTo(Permission::all());

        // Manager: mostly sales, inventory, reports, users
        $managerRole->syncPermissions([
            'dashboard.view',
            'pos.access',
            'sales.create',
            'sales.view-history',
            'sales.refund',
            'inventory.view-overview',
            'inventory.update',
            'inventory.view-movements',
            'inventory.archive',
            'reports.view',
            'audit.user-activity.view',
            'audit.system-logs.view',
            'users.create',
            'users.edit',
            'users.view-list',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.view',
        ]);

        // Cashier: sales only
        $cashierRole->syncPermissions([
            'pos.access',
            'sales.create',
            'sales.view-history',
            'sales.print-receipt',
        ]);


        // Default admin user and assign user role
        $admin = User::firstOrCreate(
            ['phone'=>'09362690603'],
            [
                'name' => 'Admin',
                'pin' => 1234,
            ]
        );

        // Default cashier user
        $cashier = User::firstOrCreate(
            ['phone'=>'09287476832'],
            [
                'name'=> 'Cashier',
                'pin' => '1234'
            ]
        );
        // Default manager user
        $manager = User::firstOrCreate(
            ['phone'=>'09108712969'],
            [
                'name'=> 'Manager',
                'pin' => '1234'
            ]
        );
        $admin->assignRole('admin');
        $cashier->assignRole('cashier');
        $manager->assignRole('manager');
    }
}
