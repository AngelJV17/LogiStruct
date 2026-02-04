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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // Identificación y Nombres
            $table->string('project_code')->unique(); 
            $table->string('project_name');
            $table->string('short_name')->unique();
            
            // Clasificación (Viene de global_parameters)
            $table->foreignId('type_id')->constrained('global_parameters'); // Obra o Servicio
            $table->foreignId('status_id')->constrained('global_parameters'); // Ejecución, Liquidación, etc.

            // Financiero (Base para Curva S)
            $table->decimal('contractual_amount', 15, 2)->default(0); 
            $table->decimal('projected_amount', 15, 2)->default(0); 

            // Temporalidad
            $table->date('start_date');
            $table->date('end_date_contractual');
            $table->date('end_date_real')->nullable();

            // Ubicación (Convención de nombres corregida)
            $table->foreignId('department_id')->constrained('ubigeo_peru_departments');
            $table->foreignId('province_id')->constrained('ubigeo_peru_provinces');
            $table->foreignId('district_id')->constrained('ubigeo_peru_districts');
            $table->string('address')->nullable();

            // Propiedad y Media
            $table->string('cover_image')->nullable();
            $table->foreignId('consortium_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes(); // Borrado lógico para seguridad de datos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
