<?php
namespace Database\Seeders;

use App\Models\GlobalParameter;
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

        // 1. CATEGORÍAS RAÍZ (Nivel 0) - Sin POSITIONS
        $roots = [
            'DOCUMENT_TYPE'        => 'TIPO DE DOCUMENTO',
            'WORKER_TYPE'          => 'TIPO DE TRABAJADOR',
            'ATTENDANCE_STATUS'    => 'ESTADO DE ASISTENCIA',
            'PERIODICIDAD_DE_PAGO' => 'PERIODICIDAD DE PAGO', // Nombre corregido para el controller
            'PROJECT_TYPE'         => 'TIPO DE PROYECTO',
            'PROJECT_STATUS'       => 'ESTADO DE PROYECTO',
            'GENDER'               => 'GÉNERO',
        ];

        $parentIds = [];

        foreach ($roots as $key => $name) {
            $parent = GlobalParameter::create([
                'group'       => 'ROOT',
                'name'        => $name,
                'description' => "Categoría principal para $name",
                'is_active'   => true,
                'level'       => 0,
                'parent_id'   => null,
            ]);
            $parentIds[$key] = $parent->id;
        }

        // 2. DEFINICIÓN DE HIJOS (Nivel 1)
        $children = [
            ['group' => 'PROJECT_TYPE', 'name' => 'Obra', 'parent' => 'PROJECT_TYPE'],
            ['group' => 'PROJECT_TYPE', 'name' => 'Servicio', 'parent' => 'PROJECT_TYPE'],

            ['group' => 'DOCUMENT_TYPE', 'name' => 'DNI', 'parent' => 'DOCUMENT_TYPE'],
            ['group' => 'DOCUMENT_TYPE', 'name' => 'Carnet de Extranjería', 'parent' => 'DOCUMENT_TYPE'],
            ['group' => 'DOCUMENT_TYPE', 'name' => 'Pasaporte', 'parent' => 'DOCUMENT_TYPE'],

            ['group' => 'PROJECT_STATUS', 'name' => 'En Ejecución', 'parent' => 'PROJECT_STATUS'],
            ['group' => 'PROJECT_STATUS', 'name' => 'Paralizado', 'parent' => 'PROJECT_STATUS'],
            ['group' => 'PROJECT_STATUS', 'name' => 'Finalizado', 'parent' => 'PROJECT_STATUS'],

            ['group' => 'WORKER_TYPE', 'name' => 'Obrero', 'parent' => 'WORKER_TYPE'],
            ['group' => 'WORKER_TYPE', 'name' => 'Oficina / Staff', 'parent' => 'WORKER_TYPE'],

            ['group' => 'ATTENDANCE_STATUS', 'name' => 'Asistió', 'parent' => 'ATTENDANCE_STATUS'],
            ['group' => 'ATTENDANCE_STATUS', 'name' => 'Falta', 'parent' => 'ATTENDANCE_STATUS'],
            ['group' => 'ATTENDANCE_STATUS', 'name' => 'Tardanza', 'parent' => 'ATTENDANCE_STATUS'],
            ['group' => 'ATTENDANCE_STATUS', 'name' => 'Permiso GNC', 'parent' => 'ATTENDANCE_STATUS'],

            ['group' => 'PERIODICIDAD_DE_PAGO', 'name' => 'Semanal', 'parent' => 'PERIODICIDAD_DE_PAGO'],
            ['group' => 'PERIODICIDAD_DE_PAGO', 'name' => 'Quincenal', 'parent' => 'PERIODICIDAD_DE_PAGO'],
            ['group' => 'PERIODICIDAD_DE_PAGO', 'name' => 'Mensual', 'parent' => 'PERIODICIDAD_DE_PAGO'],

            ['group' => 'GENDER', 'name' => 'Masculino', 'parent' => 'GENDER'],
            ['group' => 'GENDER', 'name' => 'Femenino', 'parent' => 'GENDER'],
        ];

        foreach ($children as $child) {
            GlobalParameter::create([
                'group'     => $child['group'],
                'name'      => $child['name'],
                'parent_id' => $parentIds[$child['parent']],
                'level'     => 1,
                'is_active' => true,
            ]);
        }
    }
}
