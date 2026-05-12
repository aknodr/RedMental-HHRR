<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Hhrr\Clinic;
use App\Models\Hhrr\Contract;
use App\Models\Hhrr\Employee;
use App\Models\Hhrr\Patient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return view('dashboard.super-admin', [
                'totalClients'  => Client::count(),
                'activeClients' => Client::where('active', true)->count(),
                'recentClients' => Client::latest()->limit(5)->get(),
            ]);
        }

        return view('dashboard.index', [
            'client' => $user->client,
            'stats'  => [
                'patients_total'     => Patient::count(),
                'patients_active'    => Patient::where('active', true)->count(),
                'employees_active'   => Employee::where('active', true)->count(),
                'contracts_active'   => Contract::active()->count(),
                'contracts_expiring' => Contract::expiringSoon()->count(),
                'contracts_expired'  => Contract::expired()->count(),
            ],
            'recentPatients'    => Patient::latest()->limit(5)->get(),
            'patientsByClinic'  => Clinic::withCount('patients')->orderByDesc('patients_count')->get(),
            'expiringContracts' => Contract::expiringSoon()->with(['employee', 'patient'])->orderBy('end_date')->limit(8)->get(),
        ]);
    }

}
