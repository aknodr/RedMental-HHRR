<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RedMental System') — {{ config('app.name', 'RedMental') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

<div class="flex h-screen overflow-hidden" x-data="{ sidebar: true }">

    <aside class="hidden md:flex w-64 bg-slate-900 text-slate-200 flex-col border-r border-slate-800">
        <div class="px-5 py-5 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center font-black">R</div>
                <div>
                    <div class="font-bold text-white text-sm">RedMental</div>
                    @auth
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">
                            @if(auth()->user()->isSuperAdmin())
                                Super Administrator
                            @else
                                {{ auth()->user()->client?->name ?? '—' }}
                            @endif
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-sm">
            @auth
                @if(auth()->user()->isSuperAdmin())
                    {{-- Super Admin navigation --}}
                    <div>
                        <div class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">System</div>
                        <a href="{{ route('dashboard') }}"
                           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                        </a>
                        <a href="{{ route('super-admin.clients.index') }}"
                           class="nav-link {{ request()->routeIs('super-admin.clients.*') ? 'active' : '' }}">
                            <i data-lucide="building-2" class="w-4 h-4"></i> Clients
                        </a>
                    </div>
                @else
                    {{-- Regular user navigation --}}
                    <div>
                        <div class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">General</div>
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                        </a>
                    </div>

                    <div>
                        <div class="px-2 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-2">HHRR</div>
                        @can('hhrr.patients.view')
                            <a href="{{ route('hhrr.patients.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.patients.*') ? 'active' : '' }}">
                                <i data-lucide="user-round" class="w-4 h-4"></i> Patients
                            </a>
                        @endcan
                        @can('hhrr.employees.view')
                            <a href="{{ route('hhrr.employees.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.employees.*') ? 'active' : '' }}">
                                <i data-lucide="users-round" class="w-4 h-4"></i> Employees
                            </a>
                        @endcan
                        @can('hhrr.clinics.view')
                            <a href="{{ route('hhrr.clinics.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.clinics.*') ? 'active' : '' }}">
                                <i data-lucide="hospital" class="w-4 h-4"></i> Clinics
                            </a>
                        @endcan
                        @can('hhrr.appointments.view')
                            <a href="{{ route('hhrr.appointments.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.appointments.*') ? 'active' : '' }}">
                                <i data-lucide="calendar-days" class="w-4 h-4"></i> Appointments
                            </a>
                        @endcan
                        @can('hhrr.routes.view')
                            <a href="{{ route('hhrr.routes.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.routes.*') ? 'active' : '' }}">
                                <i data-lucide="route" class="w-4 h-4"></i> Route planner
                            </a>
                        @endcan
                        @can('hhrr.invoices.view')
                            <a href="{{ route('hhrr.invoices.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.invoices.*') ? 'active' : '' }}">
                                <i data-lucide="file-text" class="w-4 h-4"></i> Invoices
                            </a>
                        @endcan
                        @can('hhrr.payroll.view')
                            <a href="{{ route('hhrr.payroll.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.payroll.*') ? 'active' : '' }}">
                                <i data-lucide="banknote" class="w-4 h-4"></i> Payroll
                            </a>
                        @endcan
                        @can('hhrr.departments.view')
                            <a href="{{ route('hhrr.departments.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.departments.*') ? 'active' : '' }}">
                                <i data-lucide="network" class="w-4 h-4"></i> Departments
                            </a>
                        @endcan
                        @can('hhrr.payers.view')
                            <a href="{{ route('hhrr.payers.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.payers.*') ? 'active' : '' }}">
                                <i data-lucide="wallet" class="w-4 h-4"></i> Payers
                            </a>
                        @endcan
                        @can('hhrr.contracts.view')
                            <a href="{{ route('hhrr.contracts.index') }}"
                               class="nav-link {{ request()->routeIs('hhrr.contracts.*') ? 'active' : '' }}">
                                <i data-lucide="file-signature" class="w-4 h-4"></i> Contracts
                            </a>
                        @endcan
                    </div>


                    @if(auth()->user()->isClientAdmin())
                        <div>
                            <div class="px-2 text-[10px] font-bold text-amber-400 uppercase tracking-widest mb-2">Administration</div>
                            <a href="{{ route('admin.users.index') }}"
                               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i data-lucide="users" class="w-4 h-4"></i> Users
                            </a>
                            <a href="{{ route('admin.roles.index') }}"
                               class="nav-link {{ request()->routeIs('admin.roles.*') && ! request()->routeIs('admin.roles.matrix*') ? 'active' : '' }}">
                                <i data-lucide="shield" class="w-4 h-4"></i> Roles
                            </a>
                            <a href="{{ route('admin.roles.matrix') }}"
                               class="nav-link {{ request()->routeIs('admin.roles.matrix*') ? 'active' : '' }}">
                                <i data-lucide="grid-2x2-check" class="w-4 h-4"></i> Permissions matrix
                            </a>
                            @can('system.audit.view')
                                <a href="{{ route('admin.audit.index') }}"
                                   class="nav-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i> Audit log
                                </a>
                            @endcan
                        </div>
                    @endif
                @endif
            @endauth
        </nav>

        @auth
            <div class="border-t border-slate-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-white text-xs font-semibold truncate">{{ auth()->user()->name }}</div>
                        <div class="text-slate-400 text-[10px] truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-white transition" title="Sign out">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </aside>

    <main class="flex-1 overflow-y-auto">
        <div class="p-6 md:p-8">
            @yield('content')
        </div>
    </main>
</div>

<style>
    .nav-link { display: flex; align-items: center; gap: 0.625rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; color: rgb(203 213 225); transition: all 0.15s; font-weight: 500; }
    .nav-link:hover { background-color: rgb(30 41 59); color: white; }
    .nav-link.active { background-color: rgb(79 70 229); color: white; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());

    window.RM = window.RM || {};

    RM.toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', timer: 3500, timerProgressBar: true,
        showConfirmButton: false, icon, title,
    });

    RM.confirmDelete = (form, label = 'this record') => {
        Swal.fire({
            title: 'Delete ' + label + '?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
        return false;
    };

    // Wire any <form data-confirm-delete="label"> inside the page so authors
    // don't have to write inline JS.
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-confirm-delete]');
        if (!form || form.dataset.confirmed === '1') return;
        e.preventDefault();
        Swal.fire({
            title: 'Delete ' + (form.dataset.confirmDelete || 'this record') + '?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });

    // Server-side flash messages → SweetAlert2 toasts
    @if(session('status'))
        RM.toast('success', @json(session('status')));
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
    @endif
    @if($errors->any())
        Swal.fire({ icon: 'error', title: 'Please fix the form', text: @json($errors->first()) });
    @endif
</script>
</body>
</html>
