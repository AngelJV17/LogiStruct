<?php
namespace Database\Seeders;

use App\Models\Bank;
use App\Models\PensionSystem;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PayrollMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 1. CARGOS (POSITIONS) ---
        $positions = [
            ['name' => 'Maestro de Obra', 'department' => 'Operaciones'],
            ['name' => 'Operario', 'department' => 'Operaciones'],
            ['name' => 'Oficial', 'department' => 'Operaciones'],
            ['name' => 'Peón', 'department' => 'Operaciones'],
            ['name' => 'Ingeniero Residente', 'department' => 'Ingeniería'],
            ['name' => 'Asistente Administrativo', 'department' => 'Administración'],
            ['name' => 'Prevencionista de Riesgos', 'department' => 'Seguridad'],
            ['name' => 'Almacenero', 'department' => 'Logística'],
        ];
        foreach ($positions as $pos) {
            Position::create($pos);
        }

        // --- 2. BANCOS (BANKS) ---
        $banks = [
            ['name' => 'BANCO DE CREDITO DEL PERU', 'short_name' => 'BCP', 'ruc' => '20100047218'],
            ['name' => 'BBVA PERU', 'short_name' => 'BBVA', 'ruc' => '20100130204'],
            ['name' => 'INTERBANK', 'short_name' => 'INTERBANK', 'ruc' => '20100053455'],
            ['name' => 'SCOTIABANK PERU', 'short_name' => 'SCOTIA', 'ruc' => '20100043140'],
            ['name' => 'BANCO DE LA NACION', 'short_name' => 'BN', 'ruc' => '20100030595'],
        ];
        foreach ($banks as $bank) {
            Bank::create($bank);
        }

        // --- 3. SISTEMAS DE PENSIONES (PENSION SYSTEMS) ---
        $pensions = [
            ['name' => 'AFP INTEGRA', 'type' => 'AFP'],
            ['name' => 'AFP PRIMA', 'type' => 'AFP'],
            ['name' => 'AFP HABITAT', 'type' => 'AFP'],
            ['name' => 'AFP PROFUTURO', 'type' => 'AFP'],
            ['name' => 'ONP', 'type' => 'ONP'],
        ];

        foreach ($pensions as $ps) {
            PensionSystem::updateOrInsert(['name' => $ps['name']], $ps);
        }

    }
}
