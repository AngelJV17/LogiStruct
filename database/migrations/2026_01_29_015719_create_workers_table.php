<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Para el QR único
            $table->string('dni', 15)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Clasificación (Obrero, Especialista, Oficina) -> global_parameters
            $table->foreignId('worker_type_id')->constrained('global_parameters');

            // Cargo Específico (Maestro, Residente, Contador) -> global_parameters
            $table->foreignId('position_id')->constrained('global_parameters');

            // Asignación Laboral
            // Si project_id es null, se entiende que es de Oficina Central
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');

            // Empresa que lo contrata (Empresa A siempre, o la que paga)
            $table->foreignId('company_id')->constrained();

            // Datos de Pago
            $table->decimal('daily_salary', 10, 2)->default(0);
            $table->decimal('monthly_salary', 10, 2)->default(0); // Para especialistas/oficina

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
