<?php

namespace Database\Seeders;

use App\Models\GlobalParameter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GlobalParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpieza de seguridad
        Schema::disableForeignKeyConstraints();
        DB::table('global_parameters')->truncate();
        Schema::enableForeignKeyConstraints();

        // 1. CATEGORÍAS RAÍZ (Nivel 0)
        $roots = [
            'DOCUMENT_TYPE'      => 'TIPO DE DOCUMENTO',
            'WORKER_TYPE'        => 'TIPO DE TRABAJADOR',
            'POSITION'           => 'CARGOS / PUESTOS',
            'ATTENDANCE_STATUS'  => 'ESTADO DE ASISTENCIA',
            'PAYMENT_PERIOD'     => 'PERIODICIDAD DE PAGO',
            'PROJECT_TYPE'       => 'TIPO DE PROYECTO', // Obras o Servicios
            'PROJECT_STATUS'     => 'ESTADO DE PROYECTO',
            'GENDER'             => 'GÉNERO',
        ];

        $parentIds = [];

        foreach ($roots as $key => $name) {
            $parent = GlobalParameter::create([
                'group'       => 'ROOT',
                'name'        => $name,
                'description' => "Categoría principal para $name",
                'is_active'   => true,
                'level'       => 0, // Las raíces son Nivel 0
                'parent_id'   => null,
            ]);
            $parentIds[$key] = $parent->id;
        }

        // 2. DEFINICIÓN DE HIJOS (Nivel 1)
        $children = [
            // --- Tipo de Proyecto (Obra o Servicio) ---
            ['group' => 'PROJECT_TYPE', 'name' => 'Obra', 'parent' => 'PROJECT_TYPE'],
            ['group' => 'PROJECT_TYPE', 'name' => 'Servicio', 'parent' => 'PROJECT_TYPE'],

            // --- Documentos ---
            ['group' => 'DOCUMENT_TYPE', 'name' => 'DNI', 'parent' => 'DOCUMENT_TYPE'],
            ['group' => 'DOCUMENT_TYPE', 'name' => 'Carnet de Extranjería', 'parent' => 'DOCUMENT_TYPE'],

            // --- Estados de Proyecto ---
            ['group' => 'PROJECT_STATUS', 'name' => 'En Ejecución', 'parent' => 'PROJECT_STATUS'],
            ['group' => 'PROJECT_STATUS', 'name' => 'Paralizado', 'parent' => 'PROJECT_STATUS'],
            ['group' => 'PROJECT_STATUS', 'name' => 'Finalizado', 'parent' => 'PROJECT_STATUS'],

            // --- Tipos de Trabajador ---
            ['group' => 'WORKER_TYPE', 'name' => 'Obrero', 'parent' => 'WORKER_TYPE'],
            ['group' => 'WORKER_TYPE', 'name' => 'Oficina / Staff', 'parent' => 'WORKER_TYPE'],

            // --- Cargos ---
            ['group' => 'POSITION', 'name' => 'Maestro de Obra', 'parent' => 'POSITION'],
            ['group' => 'POSITION', 'name' => 'Operario', 'parent' => 'POSITION'],
            ['group' => 'POSITION', 'name' => 'Oficial', 'parent' => 'POSITION'],
            ['group' => 'POSITION', 'name' => 'Peón', 'parent' => 'POSITION'],
            ['group' => 'POSITION', 'name' => 'Residente', 'parent' => 'POSITION'],

            // --- Asistencia ---
            ['group' => 'ATTENDANCE_STATUS', 'name' => 'Asistió', 'parent' => 'ATTENDANCE_STATUS'],
            ['group' => 'ATTENDANCE_STATUS', 'name' => 'Falta', 'parent' => 'ATTENDANCE_STATUS'],
            ['group' => 'ATTENDANCE_STATUS', 'name' => 'Tardanza', 'parent' => 'ATTENDANCE_STATUS'],

            // --- Periodicidad de Pago ---
            ['group' => 'PAYMENT_PERIOD', 'name' => 'Semanal', 'parent' => 'PAYMENT_PERIOD'],
            ['group' => 'PAYMENT_PERIOD', 'name' => 'Quincenal', 'parent' => 'PAYMENT_PERIOD'],
            ['group' => 'PAYMENT_PERIOD', 'name' => 'Mensual', 'parent' => 'PAYMENT_PERIOD'],

            // --- Género ---
            ['group' => 'GENDER', 'name' => 'Masculino', 'parent' => 'GENDER'],
            ['group' => 'GENDER', 'name' => 'Femenino', 'parent' => 'GENDER'],
        ];

        foreach ($children as $child) {
            GlobalParameter::create([
                'group'     => $child['group'],
                'name'      => $child['name'],
                'parent_id' => $parentIds[$child['parent']],
                'level'     => 1, // Los hijos son Nivel 1
                'is_active' => true,
            ]);
        }
    }
}
