<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GlobalParameterSeeder::class,
            PayrollMasterSeeder::class,
            // Aquí llamarás a los otros seeders después
        ]);

        /* // User::factory(10)->create();

        // 1. Crear Grupos Raíz en Global Parameters
        $workerType    = GlobalParameter::create(['group' => 'ROOT', 'name' => 'TIPO TRABAJADOR', 'level' => 1]);
        $projectType   = GlobalParameter::create(['group' => 'ROOT', 'name' => 'TIPO PROYECTO', 'level' => 1]);
        $projectStatus = GlobalParameter::create(['group' => 'ROOT', 'name' => 'ESTADO PROYECTO', 'level' => 1]);

        // 2. Crear Sub-parámetros (Tipos de Trabajador)
        $obrero = GlobalParameter::create([
            'group' => 'WORKER_TYPE', 'name' => 'Obrero', 'parent_id' => $workerType->id, 'level' => 2,
        ]);
        $especialista = GlobalParameter::create([
            'group' => 'WORKER_TYPE', 'name' => 'Especialista', 'parent_id' => $workerType->id, 'level' => 2,
        ]);
        $oficina = GlobalParameter::create([
            'group' => 'WORKER_TYPE', 'name' => 'Oficina Central', 'parent_id' => $workerType->id, 'level' => 2,
        ]);

        // 3. Crear Cargos (Hijos de los tipos)
        GlobalParameter::create(['group' => 'POSITION', 'name' => 'Maestro de Obra', 'parent_id' => $obrero->id, 'level' => 3]);
        GlobalParameter::create(['group' => 'POSITION', 'name' => 'Peón', 'parent_id' => $obrero->id, 'level' => 3]);
        GlobalParameter::create(['group' => 'POSITION', 'name' => 'Residente de Obra', 'parent_id' => $especialista->id, 'level' => 3]);
        GlobalParameter::create(['group' => 'POSITION', 'name' => 'Administrador', 'parent_id' => $oficina->id, 'level' => 3]);

        // 4. Tipos y Estados de Proyecto
        GlobalParameter::create(['group' => 'PROJECT_TYPE', 'name' => 'Obra', 'parent_id' => $projectType->id, 'level' => 2]);
        GlobalParameter::create(['group' => 'PROJECT_TYPE', 'name' => 'Servicio', 'parent_id' => $projectType->id, 'level' => 2]);
        GlobalParameter::create(['group' => 'PROJECT_STATUS', 'name' => 'En Ejecución', 'parent_id' => $projectStatus->id, 'level' => 2]);

        // 5. Crear Empresa Principal (Empresa A)
        Company::create([
            'ruc'                  => '20123456789',
            'name'                 => 'Constructora e Inmobiliaria A S.A.C.',
            'email'                => 'gerencia@empresa-a.com',
            'issues_payment_order' => true,
            'legal_representative' => 'Juan Perez',
            'representative_dni'   => '44556677',
        ]);

        // 6. Usuario Administrador de prueba
        User::factory()->create([
            'name'  => 'Admin LogiStruct',
            'email' => 'admin@logistruct.com',
        ]); */
    }
}
