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
            $table->uuid('uuid')->unique(); 

            // Identificación (Usa Global Parameters)
            $table->foreignId('document_type_id')->constrained('global_parameters');
            $table->string('document_number', 20)->unique();

            // Nombres
            $table->string('first_name');
            $table->string('last_name_paternal');
            $table->string('last_name_maternal');

            // Datos Personales
            $table->date('birth_date')->nullable();
            $table->foreignId('gender_id')->nullable()->constrained('global_parameters');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            // Clasificación Laboral
            $table->foreignId('worker_type_id')->constrained('global_parameters'); 
            $table->foreignId('position_id')->constrained('positions'); // Nueva tabla independiente

            // Relación con lugar de trabajo y empleador
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->constrained(); 

            // Estructura Salarial y Pago
            $table->decimal('daily_salary', 10, 2)->default(0);
            $table->decimal('monthly_salary', 10, 2)->default(0);
            $table->foreignId('payment_type_id')->nullable()->constrained('global_parameters');

            // Información Bancaria y Previsional (Tablas Independientes)
            $table->foreignId('bank_id')->nullable()->constrained('banks');
            $table->foreignId('pension_system_id')->nullable()->constrained('pension_systems');
            $table->string('bank_account', 30)->nullable();
            $table->string('cci', 30)->nullable();
            $table->string('cuspp', 20)->nullable(); // Código único de AFP
            
            // Auditoría y Control
            $table->date('hire_date')->nullable();
            $table->string('photo_path')->nullable();
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
