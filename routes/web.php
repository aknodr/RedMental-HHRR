<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Hhrr\AppointmentController;
use App\Http\Controllers\Hhrr\ClinicController;
use App\Http\Controllers\Hhrr\ContractController;
use App\Http\Controllers\Hhrr\DepartmentController;
use App\Http\Controllers\Hhrr\EmployeeController;
use App\Http\Controllers\Hhrr\InvoiceController;
use App\Http\Controllers\Hhrr\PatientController;
use App\Http\Controllers\Hhrr\PayerController;
use App\Http\Controllers\Hhrr\PayrollController;
use App\Http\Controllers\Hhrr\RouteController;
use App\Http\Controllers\SuperAdmin\ClientController as SuperAdminClientController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::resource('clients', SuperAdminClientController::class);
    });

    Route::middleware('client_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('roles/matrix',  [AdminRoleController::class, 'matrix'])->name('roles.matrix');
        Route::post('roles/matrix', [AdminRoleController::class, 'saveMatrix'])->name('roles.matrix.save');
        Route::resource('roles', AdminRoleController::class);
        Route::resource('users', AdminUserController::class);
    });

    // Audit log (Super Admin sees everything; Client Admin sees their tenant)
    Route::middleware('permission:system.audit.view')->group(function () {
        Route::get('admin/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.audit.index');
    });

    // Each resource is gated by granular permissions (view / create / edit / delete).
    Route::prefix('hhrr')->name('hhrr.')->group(function () {
        // Payroll
        Route::middleware('permission:hhrr.payroll.view')->group(function () {
            Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
        });
        Route::middleware('permission:hhrr.payroll.manage')->group(function () {
            Route::get('payroll/generate',  [PayrollController::class, 'generate'])->name('payroll.generate');
            Route::post('payroll',          [PayrollController::class, 'store'])->name('payroll.store');
            Route::get('payroll/{payroll}/edit',  [PayrollController::class, 'edit'])->name('payroll.edit');
            Route::put('payroll/{payroll}',       [PayrollController::class, 'update'])->name('payroll.update');
            Route::delete('payroll/{payroll}',    [PayrollController::class, 'destroy'])->name('payroll.destroy');
        });

        // Appointments calendar
        Route::middleware('permission:hhrr.appointments.view')->group(function () {
            Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        });
        Route::middleware('permission:hhrr.appointments.create')->group(function () {
            Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
            Route::post('appointments',       [AppointmentController::class, 'store'])->name('appointments.store');
        });
        Route::middleware('permission:hhrr.appointments.edit')->group(function () {
            Route::get('appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
            Route::put('appointments/{appointment}',      [AppointmentController::class, 'update'])->name('appointments.update');
        });
        Route::middleware('permission:hhrr.appointments.delete')->group(function () {
            Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
        });

        // Invoices
        Route::middleware('permission:hhrr.invoices.view')->group(function () {
            Route::get('invoices',             [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/{invoice}',   [InvoiceController::class, 'show'])->name('invoices.show');
        });
        Route::middleware('permission:hhrr.invoices.create')->group(function () {
            Route::get('invoices/create',      [InvoiceController::class, 'create'])->name('invoices.create');
            Route::post('invoices',            [InvoiceController::class, 'store'])->name('invoices.store');
        });
        Route::middleware('permission:hhrr.invoices.edit')->group(function () {
            Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('invoices/{invoice}',      [InvoiceController::class, 'update'])->name('invoices.update');
            Route::post('invoices/{invoice}/send',      [InvoiceController::class, 'send'])->name('invoices.send');
            Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark_paid');
        });
        Route::middleware('permission:hhrr.invoices.delete')->group(function () {
            Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        });

        // Route planner
        Route::middleware('permission:hhrr.routes.view')->group(function () {
            Route::get('route-planner', [RouteController::class, 'index'])->name('routes.index');
        });

        // Clinics
        Route::middleware('permission:hhrr.clinics.view')->group(function () {
            Route::get('clinics', [ClinicController::class, 'index'])->name('clinics.index');
        });
        Route::middleware('permission:hhrr.clinics.create')->group(function () {
            Route::get('clinics/create', [ClinicController::class, 'create'])->name('clinics.create');
            Route::post('clinics',       [ClinicController::class, 'store'])->name('clinics.store');
        });
        Route::middleware('permission:hhrr.clinics.view')->group(function () {
            Route::get('clinics/{clinic}', [ClinicController::class, 'show'])->name('clinics.show');
        });
        Route::middleware('permission:hhrr.clinics.edit')->group(function () {
            Route::get('clinics/{clinic}/edit', [ClinicController::class, 'edit'])->name('clinics.edit');
            Route::put('clinics/{clinic}',      [ClinicController::class, 'update'])->name('clinics.update');
        });
        Route::middleware('permission:hhrr.clinics.delete')->group(function () {
            Route::delete('clinics/{clinic}', [ClinicController::class, 'destroy'])->name('clinics.destroy');
        });

        // Departments
        Route::middleware('permission:hhrr.departments.view')->group(function () {
            Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        });
        Route::middleware('permission:hhrr.departments.create')->group(function () {
            Route::get('departments/create',  [DepartmentController::class, 'create'])->name('departments.create');
            Route::post('departments',        [DepartmentController::class, 'store'])->name('departments.store');
        });
        Route::middleware('permission:hhrr.departments.edit')->group(function () {
            Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
            Route::put('departments/{department}',      [DepartmentController::class, 'update'])->name('departments.update');
        });
        Route::middleware('permission:hhrr.departments.delete')->group(function () {
            Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        });

        // Payers
        Route::middleware('permission:hhrr.payers.view')->group(function () {
            Route::get('payers', [PayerController::class, 'index'])->name('payers.index');
        });
        Route::middleware('permission:hhrr.payers.create')->group(function () {
            Route::get('payers/create', [PayerController::class, 'create'])->name('payers.create');
            Route::post('payers',       [PayerController::class, 'store'])->name('payers.store');
        });
        Route::middleware('permission:hhrr.payers.edit')->group(function () {
            Route::get('payers/{payer}/edit', [PayerController::class, 'edit'])->name('payers.edit');
            Route::put('payers/{payer}',      [PayerController::class, 'update'])->name('payers.update');
        });
        Route::middleware('permission:hhrr.payers.delete')->group(function () {
            Route::delete('payers/{payer}', [PayerController::class, 'destroy'])->name('payers.destroy');
        });

        // Employees — literal /create must register before /{employee} so the
        // wildcard binding doesn't try to find a model with id "create".
        Route::middleware('permission:hhrr.employees.view')->group(function () {
            Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
        });
        Route::middleware('permission:hhrr.employees.create')->group(function () {
            Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::post('employees',       [EmployeeController::class, 'store'])->name('employees.store');
        });
        Route::middleware('permission:hhrr.employees.view')->group(function () {
            Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        });
        Route::middleware('permission:hhrr.employees.edit')->group(function () {
            Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
            Route::put('employees/{employee}',      [EmployeeController::class, 'update'])->name('employees.update');
        });
        Route::middleware('permission:hhrr.employees.delete')->group(function () {
            Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        });

        // Patients — same pattern: /create must come before /{patient}.
        Route::middleware('permission:hhrr.patients.view')->group(function () {
            Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
        });
        Route::middleware('permission:hhrr.patients.create')->group(function () {
            Route::get('patients/create', [PatientController::class, 'create'])->name('patients.create');
            Route::post('patients',       [PatientController::class, 'store'])->name('patients.store');
        });
        Route::middleware('permission:hhrr.patients.view')->group(function () {
            Route::get('patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
        });
        Route::middleware('permission:hhrr.patients.edit')->group(function () {
            Route::get('patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
            Route::put('patients/{patient}',      [PatientController::class, 'update'])->name('patients.update');
        });
        Route::middleware('permission:hhrr.patients.delete')->group(function () {
            Route::delete('patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');
        });

        // Contracts
        Route::middleware('permission:hhrr.contracts.view')->group(function () {
            Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
        });
        Route::middleware('permission:hhrr.contracts.create')->group(function () {
            Route::get('contracts/create', [ContractController::class, 'create'])->name('contracts.create');
            Route::post('contracts',       [ContractController::class, 'store'])->name('contracts.store');
        });
        Route::middleware('permission:hhrr.contracts.edit')->group(function () {
            Route::get('contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
            Route::put('contracts/{contract}',      [ContractController::class, 'update'])->name('contracts.update');
        });
        Route::middleware('permission:hhrr.contracts.delete')->group(function () {
            Route::delete('contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
        });
    });

});
