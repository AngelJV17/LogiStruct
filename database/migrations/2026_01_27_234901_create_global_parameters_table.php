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
        Schema::create('global_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('group'); // Ej: 'PROJECT_TYPE', 'WORKER_CATEGORY', 'EXPENSE_TYPE'
            $table->string('name');
            $table->text('description')->nullable();

            // Relación Jerárquica
            $table->foreignId('parent_id')->nullable()->constrained('global_parameters')->onDelete('cascade');

            $table->integer('level')->default(1); // Para facilitar filtros (1=Raíz, 2=Hijo, etc.)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_parameters');
    }
};
