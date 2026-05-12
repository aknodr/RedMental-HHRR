<?php

namespace Database\Seeders;

use App\Models\Hhrr\Appointment;
use App\Models\Client;
use App\Models\Hhrr\Clinic;
use App\Models\Hhrr\Contract;
use App\Models\Hhrr\Department;
use App\Models\Hhrr\Employee;
use App\Models\Hhrr\Patient;
use App\Models\Hhrr\PatientInsurance;
use App\Models\Hhrr\Payer;
use App\Models\Hhrr\Payroll;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::where('name', 'Demo Behavioral Health')->first();
        if (! $client) {
            $this->command->warn('Demo client not found. Run DemoClientSeeder first.');
            return;
        }

        $admin = Department::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Administration'],
            ['code' => 'ADM', 'description' => 'Front-office administration, intake and reception duties.', 'active' => true]
        );
        $clin = Department::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Clinical'],
            ['code' => 'CLN', 'description' => 'Therapists, case managers, and direct-care clinical staff.', 'active' => true]
        );
        $bill = Department::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Billing'],
            ['code' => 'BIL', 'description' => 'Insurance billing, claims, AR follow-up.', 'active' => true]
        );

        $medicaid = Payer::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Florida Medicaid'],
            ['type' => 'Medicaid', 'edi_payer_id' => 'FLMCD', 'phone' => '(877) 254-1055', 'email' => 'provider@flmedicaid.gov',
             'address' => '2727 Mahan Drive', 'city' => 'Tallahassee', 'state' => 'FL', 'zip' => '32308',
             'notes' => 'Fee-for-service Florida Medicaid (AHCA).', 'active' => true]
        );
        $medicare = Payer::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Medicare Part B'],
            ['type' => 'Medicare', 'edi_payer_id' => '09102', 'phone' => '(866) 454-9007', 'email' => 'provider@medicare.fcso.com',
             'address' => '532 Riverside Ave', 'city' => 'Jacksonville', 'state' => 'FL', 'zip' => '32202',
             'notes' => 'First Coast Service Options — FL Part B MAC.', 'active' => true]
        );
        $aetna = Payer::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Aetna Commercial'],
            ['type' => 'Commercial', 'edi_payer_id' => '60054', 'phone' => '(800) 872-3862', 'email' => 'providers@aetna.com',
             'address' => '151 Farmington Ave', 'city' => 'Hartford', 'state' => 'CT', 'zip' => '06156',
             'notes' => 'Commercial PPO/HMO plans.', 'active' => true]
        );
        $bcbs = Payer::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Blue Cross Blue Shield of Florida'],
            ['type' => 'Commercial', 'edi_payer_id' => '00590', 'phone' => '(800) 352-2583', 'email' => 'providers@floridablue.com',
             'address' => '4800 Deerwood Campus Pkwy', 'city' => 'Jacksonville', 'state' => 'FL', 'zip' => '32246',
             'notes' => 'Florida Blue — BCBS of Florida.', 'active' => true]
        );
        $self = Payer::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Self-Pay'],
            ['type' => 'Self-Pay', 'edi_payer_id' => 'SELF', 'phone' => '(305) 555-0100', 'email' => 'billing@demo-bh.local',
             'address' => '500 NW 2nd Ave', 'city' => 'Miami', 'state' => 'FL', 'zip' => '33128',
             'notes' => 'Patient-direct billing — no insurance involved.', 'active' => true]
        );

        $miami = Clinic::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Miami Main Clinic'],
            ['code' => 'MIA', 'address' => '100 Brickell Ave', 'city' => 'Miami', 'state' => 'FL', 'zip' => '33131',
             'latitude' => 25.7617, 'longitude' => -80.1918,
             'phone' => '(305) 555-1000', 'email' => 'miami@demo-bh.local', 'active' => true]
        );
        $hialeah = Clinic::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Hialeah Branch'],
            ['code' => 'HIA', 'address' => '200 W 49th St', 'city' => 'Hialeah', 'state' => 'FL', 'zip' => '33012',
             'latitude' => 25.8576, 'longitude' => -80.2781,
             'phone' => '(305) 555-2000', 'email' => 'hialeah@demo-bh.local', 'active' => true]
        );
        $kendall = Clinic::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Kendall Outpatient'],
            ['code' => 'KEN', 'address' => '300 SW 88th St', 'city' => 'Kendall', 'state' => 'FL', 'zip' => '33176',
             'latitude' => 25.6793, 'longitude' => -80.3173,
             'phone' => '(305) 555-3000', 'email' => 'kendall@demo-bh.local', 'active' => true]
        );
        $clinicList = [$miami, $hialeah, $kendall];

        $therapistRole = Role::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Therapist', 'guard_name' => 'web']
        );
        $therapistRole->syncPermissions([
            'hhrr.patients.view', 'hhrr.patients.edit',
            'hhrr.clinics.view',
        ]);

        $caseManagerRole = Role::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Case Manager', 'guard_name' => 'web']
        );
        $caseManagerRole->syncPermissions([
            'hhrr.patients.view', 'hhrr.patients.edit',
            'hhrr.clinics.view',
        ]);

        $billerRole = Role::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Biller', 'guard_name' => 'web']
        );
        $billerRole->syncPermissions([
            'hhrr.patients.view', 'hhrr.patients.edit',
            'hhrr.payers.view',   'hhrr.payers.create', 'hhrr.payers.edit',
            'hhrr.employees.view',
            'hhrr.contracts.view', 'hhrr.contracts.create', 'hhrr.contracts.edit',
        ]);

        $receptionistRole = Role::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'Receptionist', 'guard_name' => 'web']
        );
        $receptionistRole->syncPermissions([
            'hhrr.patients.view', 'hhrr.patients.create', 'hhrr.patients.edit',
        ]);

        $people = [
            ['first' => 'Carmen', 'last' => 'Rodriguez', 'dept' => $admin, 'pos' => 'Administrator',     'role' => null,              'rate' => 50, 'provider' => false, 'npi' => null,         'gender' => 'Female', 'dob' => '1980-04-12', 'hired_months_ago' => 36, 'addr' => '1500 Collins Ave',   'city' => 'Miami Beach', 'zip' => '33139'],
            ['first' => 'David',  'last' => 'Martinez',  'dept' => $clin,  'pos' => 'Lead therapist',    'role' => $therapistRole,    'rate' => 55, 'provider' => true,  'npi' => '1234567890', 'gender' => 'Male',   'dob' => '1978-08-22', 'hired_months_ago' => 30, 'addr' => '850 Brickell Bay Dr', 'city' => 'Miami',       'zip' => '33131'],
            ['first' => 'Laura',  'last' => 'Garcia',    'dept' => $clin,  'pos' => 'Therapist',         'role' => $therapistRole,    'rate' => 45, 'provider' => true,  'npi' => '1234567891', 'gender' => 'Female', 'dob' => '1985-11-07', 'hired_months_ago' => 18, 'addr' => '2200 NW 7th St',     'city' => 'Miami',       'zip' => '33125'],
            ['first' => 'Miguel', 'last' => 'Hernandez', 'dept' => $clin,  'pos' => 'Case manager',      'role' => $caseManagerRole,  'rate' => 38, 'provider' => true,  'npi' => '1234567892', 'gender' => 'Male',   'dob' => '1982-06-14', 'hired_months_ago' => 24, 'addr' => '500 W 49th St',      'city' => 'Hialeah',     'zip' => '33012'],
            ['first' => 'Sofia',  'last' => 'Lopez',     'dept' => $clin,  'pos' => 'Case manager',      'role' => $caseManagerRole,  'rate' => 38, 'provider' => true,  'npi' => '1234567893', 'gender' => 'Female', 'dob' => '1990-02-28', 'hired_months_ago' => 12, 'addr' => '900 SW 88th St',     'city' => 'Kendall',     'zip' => '33176'],
            ['first' => 'Jorge',  'last' => 'Perez',     'dept' => $bill,  'pos' => 'Billing specialist','role' => $billerRole,       'rate' => 32, 'provider' => false, 'npi' => null,         'gender' => 'Male',   'dob' => '1988-09-19', 'hired_months_ago' => 8,  'addr' => '350 NW 2nd Ave',     'city' => 'Miami',       'zip' => '33128'],
            ['first' => 'Ana',    'last' => 'Torres',    'dept' => $admin, 'pos' => 'Receptionist',      'role' => $receptionistRole, 'rate' => 22, 'provider' => false, 'npi' => null,         'gender' => 'Female', 'dob' => '1995-03-05', 'hired_months_ago' => 6,  'addr' => '1100 Biscayne Blvd', 'city' => 'Miami',       'zip' => '33132'],
        ];

        $employees = [];
        foreach ($people as $i => $p) {
            $emailPrefix = strtolower($p['first'] . '.' . $p['last']);
            $emp = Employee::firstOrCreate(
                ['client_id' => $client->id, 'first_name' => $p['first'], 'last_name' => $p['last']],
                [
                    'department_id'            => $p['dept']->id,
                    'employee_number'          => 'EMP-' . str_pad((string)($i+1), 3, '0', STR_PAD_LEFT),
                    'npi'                      => $p['npi'],
                    'position'                 => $p['pos'],
                    'is_provider'              => $p['provider'],
                    'hourly_rate'              => $p['rate'],
                    'salary'                   => round($p['rate'] * 2080, 2),
                    'hire_date'                => Carbon::now()->subMonths($p['hired_months_ago'])->toDateString(),
                    'termination_date'         => null,
                    'email'                    => $emailPrefix . '@demo-bh.local',
                    'phone'                    => '(305) 555-' . str_pad((string)(2000 + $i), 4, '0', STR_PAD_LEFT),
                    'date_of_birth'            => $p['dob'],
                    'gender'                   => $p['gender'],
                    'address'                  => $p['addr'],
                    'city'                     => $p['city'],
                    'state'                    => 'FL',
                    'zip'                      => $p['zip'],
                    'emergency_contact_name'   => $p['first'] === 'Carmen' ? 'Roberto Rodriguez' : ($p['first'] . ' Family'),
                    'emergency_contact_phone'  => '(305) 555-' . str_pad((string)(7000 + $i), 4, '0', STR_PAD_LEFT),
                    'notes'                    => "Demo {$p['pos']} record — used for thesis defense walkthrough.",
                    'active'                   => true,
                ]
            );
            $employees[strtolower($p['first'])] = $emp;

            if ($p['role']) {
                // Create a login for this employee
                $user = User::firstOrCreate(
                    ['email' => strtolower($p['first'] . '.' . $p['last']) . '@demo-bh.local'],
                    [
                        'client_id' => $client->id,
                        'name'      => $p['first'] . ' ' . $p['last'],
                        'password'  => Hash::make('password123'),
                        'active'    => true,
                    ]
                );
                $user->syncRoles([$p['role']->name]);
            }
        }

        $patientsData = [
            ['Juan',    'Antonio', 'Rivera',   'M', '1985-03-12', $medicaid, 'MCD0010001', 'GRP-MCD-A', 'English', 'PSR'],
            ['Maria',   'Elena',   'Sanchez',  'F', '1990-07-22', $aetna,    'AET2024002',  'GRP-AET-1', 'Spanish', 'IT'],
            ['Pedro',   'Luis',    'Gonzalez', 'M', '1978-11-05', $medicaid, 'MCD0010003', 'GRP-MCD-A', 'Spanish', 'PSR'],
            ['Ana',     'Beatriz', 'Vargas',   'F', '2005-01-18', $medicaid, 'MCD0010004', 'GRP-MCD-A', 'English', 'TCM'],
            ['Roberto', 'Carlos',  'Fernandez','M', '1965-09-30', $medicare, 'MED99X0005', 'GRP-MED-B', 'Spanish', 'IT'],
            ['Luisa',   'Maria',   'Diaz',     'F', '1972-04-15', $bcbs,     'BCBS0FL006', 'GRP-BCBS-2','English', 'PSR'],
            ['Carlos',  'Andres',  'Mendoza',  'M', '1998-12-03', $medicaid, 'MCD0010007', 'GRP-MCD-A', 'Spanish', 'TCM'],
            ['Isabel',  'Sofia',   'Cruz',     'F', '1988-06-20', $aetna,    'AET2024008',  'GRP-AET-1', 'English', null],
            ['Miguel',  'Angel',   'Ortiz',    'M', '1995-08-14', $self,     'SELFPAY009',  'GRP-SELF',  'Spanish', null],
            ['Patricia','Lucia',   'Reyes',    'F', '2010-02-25', $medicaid, 'MCD0010010', 'GRP-MCD-A', 'English', null],
        ];

        $providers = [$employees['david'], $employees['laura'], $employees['miguel'], $employees['sofia']];

        // Plausible Miami-area lat/lng pool so the route planner has real
        // coordinates to optimize. Indexes line up with $patientsData.
        $patientCoords = [
            ['Miami',         25.7617, -80.1918],
            ['Hialeah',       25.8576, -80.2781],
            ['Kendall',       25.6793, -80.3173],
            ['Coral Gables',  25.7215, -80.2684],
            ['Doral',         25.8195, -80.3553],
            ['Aventura',      25.9565, -80.1392],
            ['Miami Beach',   25.7907, -80.1300],
            ['Homestead',     25.4687, -80.4776],
            ['North Miami',   25.8901, -80.1867],
            ['Cutler Bay',    25.5808, -80.3470],
        ];

        $patients = [];
        foreach ($patientsData as $i => [$first, $middle, $last, $sex, $dob, $payer, $policy, $group, $lang, $service]) {
            $intakeDate = Carbon::now()->subDays(($i + 1) * 15)->toDateString();
            $coord = $patientCoords[$i] ?? $patientCoords[0];
            $patient = Patient::firstOrCreate(
                ['client_id' => $client->id, 'first_name' => $first, 'last_name' => $last],
                [
                    'mrn'                      => 'MRN-' . str_pad((string)($i+1001), 4, '0', STR_PAD_LEFT),
                    'assigned_provider_id'     => $providers[$i % count($providers)]->id,
                    'middle_name'              => $middle,
                    'date_of_birth'            => $dob,
                    'gender'                   => $sex === 'M' ? 'Male' : 'Female',
                    'ssn'                      => '500-' . str_pad((string)(10 + $i), 2, '0', STR_PAD_LEFT) . '-' . str_pad((string)(1000 + $i * 7), 4, '0', STR_PAD_LEFT),
                    'phone'                    => '(305) 555-' . str_pad((string)(1000 + $i), 4, '0', STR_PAD_LEFT),
                    'email'                    => strtolower("{$first}.{$last}@patient.local"),
                    'address'                  => (100 + $i * 50) . ' ' . ['Main', 'Oak', 'Maple', 'Cedar', 'Palm', 'Coral'][$i % 6] . ' St',
                    'city'                     => $coord[0],
                    'state'                    => 'FL',
                    'zip'                      => '3310' . ($i % 9),
                    'latitude'                 => $coord[1],
                    'longitude'                => $coord[2],
                    'emergency_contact_name'   => $first . "'s Family Contact",
                    'emergency_contact_phone'  => '(305) 555-' . str_pad((string)(8000 + $i), 4, '0', STR_PAD_LEFT),
                    'preferred_language'       => $lang,
                    'intake_date'              => $intakeDate,
                    'notes'                    => "Demo patient — primary service interest: " . ($service ?: 'general intake'),
                    'active'                   => true,
                ]
            );

            // Enroll patient in 1-2 clinics rotating through the available list
            $clinic = $clinicList[$i % count($clinicList)];
            $patient->clinics()->syncWithoutDetaching([
                $clinic->id => ['enrollment_date' => $intakeDate, 'status' => 'active', 'notes' => "Initial enrollment at {$clinic->name}."],
            ]);

            if ($payer && $policy) {
                PatientInsurance::firstOrCreate(
                    ['patient_id' => $patient->id, 'payer_id' => $payer->id, 'priority' => 'primary'],
                    [
                        'policy_number'           => $policy,
                        'group_number'            => $group,
                        'subscriber_name'         => $patient->full_name,
                        'subscriber_relationship' => 'self',
                        'effective_date'          => Carbon::now()->startOfYear()->toDateString(),
                        'termination_date'        => Carbon::now()->endOfYear()->toDateString(),
                        'active'                  => true,
                    ]
                );
            }

            $patients[$last] = ['model' => $patient, 'service' => $service];
        }

        // Active employment contracts — full year terms, salaried.
        foreach (['david', 'laura', 'miguel', 'sofia'] as $key) {
            $emp = $employees[$key];
            Contract::firstOrCreate(
                ['client_id' => $client->id, 'employee_id' => $emp->id, 'type' => 'employment'],
                [
                    'patient_id' => null,
                    'title'      => 'Employment agreement — ' . $emp->full_name,
                    'start_date' => $emp->hire_date,
                    'end_date'   => Carbon::parse($emp->hire_date)->addYear()->toDateString(),
                    'status'     => 'active',
                    'amount'     => ($emp->hourly_rate ?? 0) * 2080,
                    'terms'      => 'Full-time employment, on-site only, 40 hours/week. Standard non-disclosure and code-of-conduct clauses apply.',
                    'notes'      => 'Demo contract — used for the contracts list page.',
                ]
            );
        }
        // Expiring-soon contract — ends within 30 days. Powers the "expiring" tab + dashboard alert.
        Contract::firstOrCreate(
            ['client_id' => $client->id, 'employee_id' => $employees['jorge']->id, 'type' => 'service_agreement'],
            [
                'patient_id' => null,
                'title'      => 'Billing services consultancy — Q1 ' . now()->year,
                'start_date' => now()->subDays(60)->toDateString(),
                'end_date'   => now()->addDays(15)->toDateString(),
                'status'     => 'active',
                'amount'     => 12500,
                'terms'      => 'Quarterly consulting on EDI claim submission and AR follow-up.',
                'notes'      => 'Renewal pending — vendor will send a new SOW.',
            ]
        );
        // Expired contract — already past end_date. Powers the "expired" tab.
        Contract::firstOrCreate(
            ['client_id' => $client->id, 'employee_id' => $employees['ana']->id, 'type' => 'employment'],
            [
                'patient_id' => null,
                'title'      => 'Front-desk seasonal cover — Winter ' . (now()->year - 1),
                'start_date' => now()->subMonths(8)->toDateString(),
                'end_date'   => now()->subMonths(2)->toDateString(),
                'status'     => 'expired',
                'amount'     => 6000,
                'terms'      => 'Temporary 6-month coverage for reception duties.',
                'notes'      => 'Demo expired contract — kept for historical reference.',
            ]
        );
        // Vendor contract for clinic supplies.
        Contract::firstOrCreate(
            ['client_id' => $client->id, 'type' => 'vendor', 'title' => 'Office supplies vendor — annual blanket'],
            [
                'employee_id' => null,
                'patient_id'  => null,
                'start_date'  => now()->subMonths(3)->toDateString(),
                'end_date'    => now()->addMonths(9)->toDateString(),
                'status'      => 'active',
                'amount'      => 4800,
                'terms'       => 'Monthly delivery of standard office supplies; net 30 invoicing.',
                'notes'       => 'Demo vendor contract.',
            ]
        );

        $providers = [$employees['david'], $employees['laura'], $employees['miguel'], $employees['sofia']];
        $patientList = Patient::where('client_id', $client->id)->get();
        $today = Carbon::today();

        // 5 appointments yesterday (completed), 8 today (scheduled), 4 tomorrow.
        $schedule = [
            -1 => ['count' => 5, 'status' => 'completed'],
             0 => ['count' => 8, 'status' => 'scheduled'],
             1 => ['count' => 4, 'status' => 'scheduled'],
             2 => ['count' => 3, 'status' => 'scheduled'],
        ];
        $appointmentSeq = 0;
        foreach ($schedule as $dayOffset => $cfg) {
            for ($i = 0; $i < $cfg['count']; $i++) {
                $provider = $providers[$appointmentSeq % count($providers)];
                $patient  = $patientList[$appointmentSeq % $patientList->count()];
                $clinic   = $clinicList[$appointmentSeq % count($clinicList)];
                $when     = $today->copy()->addDays($dayOffset)->setTime(9 + ($i % 8), ($i % 2) * 30);

                Appointment::firstOrCreate(
                    ['client_id' => $client->id, 'provider_id' => $provider->id, 'scheduled_at' => $when],
                    [
                        'patient_id'       => $patient->id,
                        'clinic_id'        => $clinic->id,
                        'duration_minutes' => 45,
                        'status'           => $cfg['status'],
                        'reason'           => ['Follow-up', 'Initial intake', 'Medication review', 'Therapy session', 'Care plan review'][$appointmentSeq % 5],
                        'notes'            => '',
                    ]
                );
                $appointmentSeq++;
            }
        }

        $samplePatients = $patientList->take(5);
        $samplePayer    = \App\Models\Hhrr\Payer::where('client_id', $client->id)->first();
        $invoiceFixtures = [
            // [days_ago_issued, status, paid_offset_or_null, lines]
            [45, 'paid', 14, [
                ['Group therapy session', 'H2017', 16, 5.97],
                ['Individual therapy', '90834', 1, 95.50],
            ]],
            [25, 'paid', 14, [
                ['Case-management contact', 'T1017', 4, 22.50],
            ]],
            [10, 'sent', null, [
                ['Group therapy session', 'H2017', 16, 5.97],
                ['Group therapy session', 'H2017', 16, 5.97],
            ]],
            [40, 'overdue', null, [
                ['Individual therapy', '90834', 1, 95.50],
            ]],
            [2, 'draft', null, [
                ['Initial assessment', '90791', 1, 175.00],
                ['Care-plan review', 'T1017', 2, 22.50],
            ]],
        ];
        foreach ($invoiceFixtures as $idx => [$daysIssued, $status, $paidOffset, $lines]) {
            $patient   = $samplePatients[$idx % $samplePatients->count()];
            $issueDate = Carbon::now()->subDays($daysIssued);
            $invNumber = sprintf('INV-%s-%04d', $issueDate->format('Y'), $idx + 1);

            $subtotal = collect($lines)->sum(fn ($l) => $l[2] * $l[3]);

            $invoice = \App\Models\Hhrr\Invoice::firstOrCreate(
                ['invoice_number' => $invNumber],
                [
                    'client_id'      => $client->id,
                    'patient_id'     => $patient->id,
                    'payer_id'       => $samplePayer?->id,
                    'issue_date'     => $issueDate->toDateString(),
                    'due_date'       => $issueDate->copy()->addDays(30)->toDateString(),
                    'paid_date'      => $paidOffset ? $issueDate->copy()->addDays($paidOffset)->toDateString() : null,
                    'status'         => $status,
                    'subtotal'       => round($subtotal, 2),
                    'tax'            => 0,
                    'total'          => round($subtotal, 2),
                    'amount_paid'    => $status === 'paid' ? round($subtotal, 2) : 0,
                    'notes'          => '',
                    'terms'          => 'Net 30. Late fee 1.5% per month after due date.',
                    'created_by'     => $admin?->id,
                ]
            );
            if ($invoice->lines()->doesntExist()) {
                foreach ($lines as $i => [$desc, $cpt, $qty, $rate]) {
                    $invoice->lines()->create([
                        'description'  => $desc,
                        'cpt_code'     => $cpt,
                        'service_date' => $issueDate->copy()->subDays(5 + $i)->toDateString(),
                        'quantity'     => $qty,
                        'unit_price'   => $rate,
                        'line_total'   => round($qty * $rate, 2),
                    ]);
                }
            }
        }

        $payPeriods = [
            ['start' => $today->copy()->subDays(27), 'end' => $today->copy()->subDays(14), 'status' => 'paid',  'frequency' => 'bi_weekly'],
            ['start' => $today->copy()->subDays(13), 'end' => $today->copy(),              'status' => 'draft', 'frequency' => 'bi_weekly'],
        ];
        foreach ($payPeriods as $period) {
            foreach ($providers as $prov) {
                $payroll = Payroll::firstOrCreate(
                    [
                        'client_id'    => $client->id,
                        'employee_id'  => $prov->id,
                        'period_start' => $period['start']->toDateString(),
                        'period_end'   => $period['end']->toDateString(),
                    ],
                    [
                        'frequency'         => $period['frequency'],
                        'hours_worked'      => 72.5 + ($prov->id % 4) * 2.0,
                        'hourly_rate'       => (float) ($prov->hourly_rate ?? 40),
                        'per_patient_bonus' => 5.00,
                        'patients_seen'     => 12 + ($prov->id % 6),
                        'deductions'        => 215.00,
                        'status'            => $period['status'],
                        'notes'             => ' Hours and patient counts derived from appointments.',
                    ]
                );
                $payroll->recalculate();
                $payroll->save();
            }
        }

        $this->command->info('Demo data seeded: ' . count($patientsData) . ' patients, ' . count($people) . ' employees, '
            . count($clinicList) . ' clinics, ' . Appointment::where('client_id', $client->id)->count() . ' appointments, '
            . Payroll::where('client_id', $client->id)->count() . ' payroll rows.');
    }
}
