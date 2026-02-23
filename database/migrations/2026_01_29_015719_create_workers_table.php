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
            $table->uuid('uuid')->unique(); // Indispensable para el QR

            // Identificación
            $table->foreignId('document_type_id')->constrained('global_parameters');
            $table->string('document_number', 20)->unique();

            // Nombres separados (Vital para planillas)
            $table->string('first_name');
            $table->string('last_name_paternal');
            $table->string('last_name_maternal');

            // Datos Personales
            $table->date('birth_date')->nullable();
            $table->foreignId('gender_id')->nullable()->constrained('global_parameters');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            // Clasificación y Cargo
            $table->foreignId('worker_type_id')->constrained('global_parameters'); // Obrero, Oficina, etc.
            $table->foreignId('position_id')->constrained('global_parameters');    // Maestro, Operario, etc.

            // Relación con el lugar de trabajo y empleador
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->constrained(); // Empresa del consorcio que lo contrata

            // Estructura Salarial
            $table->decimal('daily_salary', 10, 2)->default(0);
            $table->decimal('monthly_salary', 10, 2)->default(0);
            $table->foreignId('payment_type_id')->nullable()->constrained('global_parameters'); // Destajo, Jornal, Mensual

            // Control y Auditoría
            $table->string('photo_path')->nullable(); // Para el carnet/QR
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
