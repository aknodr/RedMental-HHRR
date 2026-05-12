<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Full catalog of permissions grouped by module. The Client Admin UI renders
     * this as a matrix (rows = roles, columns = permissions).
     *
     * Convention: "<module>.<resource>.<action>"
     */
    public const CATALOG = [
        'System' => [
            'system.users.view'   => 'View users',
            'system.users.create' => 'Create users',
            'system.users.edit'   => 'Edit users',
            'system.users.delete' => 'Delete users',
            'system.roles.view'   => 'View roles',
            'system.roles.manage' => 'Create / edit / delete roles',
        ],

        'HHRR — Patients' => [
            'hhrr.patients.view'   => 'View patients',
            'hhrr.patients.create' => 'Create patients',
            'hhrr.patients.edit'   => 'Edit patients',
            'hhrr.patients.delete' => 'Delete patients',
        ],
        'HHRR — Employees' => [
            'hhrr.employees.view'   => 'View employees',
            'hhrr.employees.create' => 'Create employees',
            'hhrr.employees.edit'   => 'Edit employees',
            'hhrr.employees.delete' => 'Delete employees',
        ],
        'HHRR — Departments' => [
            'hhrr.departments.view'   => 'View departments',
            'hhrr.departments.create' => 'Create departments',
            'hhrr.departments.edit'   => 'Edit departments',
            'hhrr.departments.delete' => 'Delete departments',
        ],
        'HHRR — Contracts' => [
            'hhrr.contracts.view'   => 'View contracts',
            'hhrr.contracts.create' => 'Create contracts',
            'hhrr.contracts.edit'   => 'Edit contracts',
            'hhrr.contracts.delete' => 'Delete contracts',
        ],
        'HHRR — Payers' => [
            'hhrr.payers.view'   => 'View payers',
            'hhrr.payers.create' => 'Create payers',
            'hhrr.payers.edit'   => 'Edit payers',
            'hhrr.payers.delete' => 'Delete payers',
        ],
        'HHRR — Clinics' => [
            'hhrr.clinics.view'   => 'View clinics',
            'hhrr.clinics.create' => 'Create clinics',
            'hhrr.clinics.edit'   => 'Edit clinics',
            'hhrr.clinics.delete' => 'Delete clinics',
        ],
        'HHRR — Appointments' => [
            'hhrr.appointments.view'   => 'View appointments calendar',
            'hhrr.appointments.create' => 'Create appointments',
            'hhrr.appointments.edit'   => 'Edit appointments',
            'hhrr.appointments.delete' => 'Delete appointments',
        ],
        'HHRR — Payroll' => [
            'hhrr.payroll.view'    => 'View payroll',
            'hhrr.payroll.manage'  => 'Generate / edit payroll',
        ],
        'HHRR — Invoices' => [
            'hhrr.invoices.view'   => 'View invoices',
            'hhrr.invoices.create' => 'Create invoices',
            'hhrr.invoices.edit'   => 'Edit invoices / record payments',
            'hhrr.invoices.delete' => 'Delete invoices',
        ],
        'HHRR — Route planner' => [
            'hhrr.routes.view' => 'View daily home-visit route planner',
        ],
        'System — Audit' => [
            'system.audit.view' => 'View system audit log',
        ],

    ];

    public function run(): void
    {
        foreach (self::CATALOG as $group) {
            foreach ($group as $name => $_description) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
        }

        // Seed the two built-in roles. Additional roles are created by each Client Admin.
        // Super Admin also gets the audit-view permission so they can inspect
        // every tenant's activity.
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web'])
            ->syncPermissions(['system.audit.view']);

        // Client Admin gets every permission — they are the full administrator
        // for their organization and should never be blocked by the permission
        // middleware.
        Role::firstOrCreate(['name' => 'Client Admin', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        // HHRR Admin — owns everything in the HHRR module (patients, employees,
        // clinics, payers, contracts, appointments, payroll, invoices, routes).
        // No clinical access.
        Role::firstOrCreate(['name' => 'HHRR Admin', 'guard_name' => 'web'])
            ->syncPermissions(
                Permission::where('name', 'like', 'hhrr.%')
                    ->orWhere('name', 'system.audit.view')
                    ->get()
            );

    }
}
