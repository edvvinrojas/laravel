<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vacation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PermissionScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $managerPerms = [
                'recursos_humanos' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                'vacaciones' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                'ausentismo' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                'empleados' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
            ];

            $userPerms = [
                'recursos_humanos' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                'vacaciones' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false],
                'ausentismo' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false],
            ];

            $mgrComercial = $this->upsertUser(
                'qa.gerente.comercial',
                'qa.gerente.comercial@copymart.com',
                'Gerente Comercial QA',
                'gerencia',
                'comercial',
                $managerPerms
            );

            $mgrOperaciones = $this->upsertUser(
                'qa.gerente.operaciones',
                'qa.gerente.operaciones@copymart.com',
                'Gerente Operaciones QA',
                'gerencia',
                'operaciones',
                $managerPerms
            );

            $empCom1 = $this->upsertUser(
                'qa.emp.com.1',
                'qa.emp.com.1@copymart.com',
                'Empleado Comercial Uno QA',
                'usuario',
                'comercial',
                $userPerms
            );

            $empCom2 = $this->upsertUser(
                'qa.emp.com.2',
                'qa.emp.com.2@copymart.com',
                'Empleado Comercial Dos QA',
                'usuario',
                'comercial',
                $userPerms
            );

            $empOps1 = $this->upsertUser(
                'qa.emp.ops.1',
                'qa.emp.ops.1@copymart.com',
                'Empleado Operaciones Uno QA',
                'usuario',
                'operaciones',
                $userPerms
            );

            $empOps2 = $this->upsertUser(
                'qa.emp.ops.2',
                'qa.emp.ops.2@copymart.com',
                'Empleado Operaciones Dos QA',
                'usuario',
                'operaciones',
                $userPerms
            );

            $eMgrCom = $this->upsertEmployee($mgrComercial, [
                'nombre' => 'Gerente Comercial QA',
                'departamento' => 'Comercial',
                'puesto' => 'Gerente Comercial',
                'sueldo' => 42000,
                'nss' => '90000000001',
                'rfc' => 'GCQ900101ABC',
                'curp' => 'GCQX900101HDFAAA01',
            ]);

            $eMgrOps = $this->upsertEmployee($mgrOperaciones, [
                'nombre' => 'Gerente Operaciones QA',
                'departamento' => 'Operaciones',
                'puesto' => 'Gerente Operaciones',
                'sueldo' => 42000,
                'nss' => '90000000002',
                'rfc' => 'GOQ900101ABC',
                'curp' => 'GOQX900101HDFAAA02',
            ]);

            $eCom1 = $this->upsertEmployee($empCom1, [
                'nombre' => 'Empleado Comercial Uno QA',
                'departamento' => 'Comercial',
                'puesto' => 'Ejecutivo Comercial',
                'sueldo' => 18000,
                'nss' => '90000000003',
                'rfc' => 'ECQ900101ABC',
                'curp' => 'ECQX900101HDFAAA03',
                'direct_manager_user_id' => $mgrComercial->id,
            ]);

            $eCom2 = $this->upsertEmployee($empCom2, [
                'nombre' => 'Empleado Comercial Dos QA',
                'departamento' => 'Comercial',
                'puesto' => 'Ejecutivo Comercial',
                'sueldo' => 17500,
                'nss' => '90000000004',
                'rfc' => 'EDQ900101ABC',
                'curp' => 'EDQX900101HDFAAA04',
                'direct_manager_user_id' => $mgrComercial->id,
            ]);

            $eOps1 = $this->upsertEmployee($empOps1, [
                'nombre' => 'Empleado Operaciones Uno QA',
                'departamento' => 'Operaciones',
                'puesto' => 'Tecnico de Campo',
                'sueldo' => 16500,
                'nss' => '90000000005',
                'rfc' => 'EOQ900101ABC',
                'curp' => 'EOQX900101HDFAAA05',
                'direct_manager_user_id' => $mgrOperaciones->id,
            ]);

            $eOps2 = $this->upsertEmployee($empOps2, [
                'nombre' => 'Empleado Operaciones Dos QA',
                'departamento' => 'Operaciones',
                'puesto' => 'Tecnico de Campo',
                'sueldo' => 16200,
                'nss' => '90000000006',
                'rfc' => 'EPQ900101ABC',
                'curp' => 'EPQX900101HDFAAA06',
                'direct_manager_user_id' => $mgrOperaciones->id,
            ]);

            $this->ensurePendingRequests($eCom1, $empCom1->id, 3, 'AUSENTISMO');
            $this->ensurePendingRequests($eCom2, $empCom2->id, 2, 'SALIDA_TEMPRANA');
            $this->ensurePendingRequests($eOps1, $empOps1->id, 4, 'LLEGADA_TARDE');
            $this->ensurePendingRequests($eOps2, $empOps2->id, 1, 'PERMISO_PERSONAL');

            // Keep manager self-records as active employees for scope tests.
            $this->ensurePendingRequests($eMgrCom, $mgrComercial->id, 1, 'OTRO');
            $this->ensurePendingRequests($eMgrOps, $mgrOperaciones->id, 1, 'OTRO');
        });
    }

    private function upsertUser(
        string $username,
        string $email,
        string $fullName,
        string $rol,
        string $department,
        array $permissions
    ): User {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'username' => $username,
                'full_name' => $fullName,
                'password' => Hash::make('C0PyM@rT'),
                'rol' => $rol,
                'department' => $department,
                'permissions' => $permissions,
                'is_active' => true,
                'is_hidden' => false,
            ]
        );
    }

    private function upsertEmployee(User $user, array $data): Employee
    {
        $defaults = [
            'birthday' => '1990-01-01',
            'hire_date' => '2024-01-15',
            'phone_emergency' => '5511111111',
            'contact_emergency' => 'Contacto QA',
            'is_active' => true,
        ];

        return Employee::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($defaults, $data)
        );
    }

    private function ensurePendingRequests(Employee $employee, int $requestedBy, int $vacDays, string $absenceType): void
    {
        Vacation::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'status' => 'PENDIENTE',
                'start_date' => now()->addDays(10)->toDateString(),
            ],
            [
                'vacation_days' => $vacDays,
                'end_date' => now()->addDays(10 + max(1, $vacDays))->toDateString(),
                'requested_by' => $requestedBy,
                'remaining_days' => max(0, $employee->vacationDaysRemaining() - $vacDays),
                'notes' => 'Solicitud QA para pruebas de permisos',
            ]
        );

        Absence::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'status' => 'PENDIENTE',
                'start_date' => now()->addDays(2)->toDateString(),
            ],
            [
                'absence_type' => $absenceType,
                'end_date' => now()->addDays(2)->toDateString(),
                'is_justified' => false,
                'justification' => null,
                'notes' => 'Ausencia QA para pruebas de permisos',
            ]
        );
    }
}
