<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $roles = [
        'Super Admin',
        'Company Admin',
        'Branch Manager',
        'Sales',
        'Designer',
        'Production',
        'Storekeeper',
        'Accountant',
        'HR',
        'Viewer',
    ];

    /**
     * @var list<string>
     */
    private array $permissions = [
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'roles.view',
        'roles.create',
        'roles.edit',
        'roles.delete',
        'companies.manage',
        'branches.manage',
        'departments.manage',
        'employees.manage',
        'activity_logs.view',
        'crm.customers.view',
        'crm.customers.create',
        'crm.customers.edit',
        'crm.customers.delete',
        'crm.leads.view',
        'crm.leads.create',
        'crm.leads.edit',
        'crm.leads.delete',
        'crm.activities.view',
        'crm.activities.create',
        'crm.activities.edit',
        'crm.activities.delete',
        'quotations.view',
        'quotations.create',
        'quotations.edit',
        'quotations.delete',
        'quotations.approve',
        'quotations.send',
        'quotations.convert',
        'artwork.view',
        'artwork.create',
        'artwork.edit',
        'artwork.delete',
        'artwork.assign',
        'artwork.submit',
        'artwork.approve',
        'sales_orders.view',
        'sales_orders.create',
        'sales_orders.edit',
        'sales_orders.delete',
        'sales_orders.confirm',
        'sales_orders.production',
        'sales_orders.close',
        'production.view',
        'production.create',
        'production.edit',
        'production.delete',
        'production.schedule',
        'production.start',
        'production.complete',
        'production.qc',
        'inventory.view',
        'inventory.create',
        'inventory.edit',
        'inventory.delete',
        'inventory.receive',
        'inventory.issue',
        'inventory.adjust',
        'inventory.transfer',
        'settings.view',
        'settings.manage',
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $rolePermissions = [
        'Company Admin' => [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit',
            'branches.manage', 'departments.manage', 'employees.manage',
            'activity_logs.view',
            'crm.customers.view', 'crm.customers.create', 'crm.customers.edit', 'crm.customers.delete',
            'crm.leads.view', 'crm.leads.create', 'crm.leads.edit', 'crm.leads.delete',
            'crm.activities.view', 'crm.activities.create', 'crm.activities.edit', 'crm.activities.delete',
            'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete',
            'quotations.approve', 'quotations.send', 'quotations.convert',
            'artwork.view', 'artwork.create', 'artwork.edit', 'artwork.delete',
            'artwork.assign', 'artwork.submit', 'artwork.approve',
            'sales_orders.view', 'sales_orders.create', 'sales_orders.edit', 'sales_orders.delete',
            'sales_orders.confirm', 'sales_orders.production', 'sales_orders.close',
            'production.view', 'production.create', 'production.edit', 'production.delete',
            'production.schedule', 'production.start', 'production.complete', 'production.qc',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'inventory.receive', 'inventory.issue', 'inventory.adjust', 'inventory.transfer',
            'settings.view', 'settings.manage',
        ],
        'Branch Manager' => [
            'users.view', 'users.edit',
            'branches.manage', 'departments.manage', 'employees.manage',
            'activity_logs.view',
            'crm.customers.view', 'crm.customers.create', 'crm.customers.edit',
            'crm.leads.view', 'crm.leads.create', 'crm.leads.edit',
            'crm.activities.view', 'crm.activities.create', 'crm.activities.edit',
            'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.approve', 'quotations.send',
            'artwork.view', 'artwork.create', 'artwork.edit', 'artwork.assign', 'artwork.approve',
            'sales_orders.view', 'sales_orders.create', 'sales_orders.edit', 'sales_orders.confirm',
            'settings.view',
        ],
        'Sales' => [
            'crm.customers.view', 'crm.customers.create', 'crm.customers.edit',
            'crm.leads.view', 'crm.leads.create', 'crm.leads.edit',
            'crm.activities.view', 'crm.activities.create', 'crm.activities.edit',
            'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.send',
            'artwork.view', 'artwork.create',
            'quotations.convert',
            'sales_orders.view', 'sales_orders.create', 'sales_orders.edit', 'sales_orders.confirm',
        ],
        'Production' => [
            'sales_orders.view', 'sales_orders.production',
            'production.view', 'production.create', 'production.edit',
            'production.schedule', 'production.start', 'production.complete', 'production.qc',
            'inventory.view', 'inventory.issue',
        ],
        'Storekeeper' => [
            'inventory.view', 'inventory.create', 'inventory.edit',
            'inventory.receive', 'inventory.issue', 'inventory.adjust', 'inventory.transfer',
        ],
        'Designer' => [
            'artwork.view', 'artwork.edit', 'artwork.submit',
        ],
        'Viewer' => [
            'users.view', 'roles.view', 'activity_logs.view',
            'crm.customers.view', 'crm.leads.view', 'crm.activities.view',
            'quotations.view',
            'artwork.view',
            'sales_orders.view',
            'production.view',
            'inventory.view',
        ],
        'Accountant' => [
            'crm.customers.view',
            'quotations.view',
            'sales_orders.view',
            'inventory.view',
            'activity_logs.view',
            'settings.view',
        ],
        'HR' => [
            'users.view', 'users.create', 'users.edit',
            'employees.manage',
            'departments.manage',
            'branches.manage',
            'activity_logs.view',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach ($this->roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $superAdmin = Role::findByName('Super Admin', 'web');
        $superAdmin->syncPermissions($this->permissions);

        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions($permissions);
        }
    }
}
