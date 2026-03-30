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
        // create permission for spatie
        $permissions = [
            // Dashboard
            'view dashboard',

            // POS / Sales
            // debatable, might just make this into one.
            'create sale',
            'view sales history',
            'refund sale',
            'print receipt',

            // Inventory
            'view stock overview',
            'update stock',
            'view stock movements',
            'archive stock',
            'delete stock',

            // Audit Logs
            'view user activity logs',
            'view system logs',

            // Reports
            'view reports',

            // Users
            'create user',
            'edit user',
            'delete user',
            'view user list',

            // Suppliers
            'create supplier',
            'edit supplier',
            'delete supplier',
            'view suppliers',
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
            'view dashboard',
            'create sale',
            'view sales history',
            'refund sale',
            'view stock overview',
            'update stock',
            'view stock movements',
            'archive stock',
            'view reports',
            'view user activity logs',
            'view system logs',
            'create user',
            'edit user',
            'view user list',
            'create supplier',
            'edit supplier',
            'view suppliers',
        ]);

        // Cashier: sales only
        $cashierRole->syncPermissions([
            'create sale',
            'view sales history',
            'print receipt',
        ]);


        // Default admin user and assign user role
        $admin = User::firstOrCreate(
            ['phone'=>'09362690603'],
            [
                'name' => 'Admin',
                'pin' => 1234,
            ]
        );

        $admin->assignRole('admin');
    }
}
