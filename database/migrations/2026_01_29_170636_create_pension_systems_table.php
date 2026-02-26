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
        Schema::create('pension_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name');     // Ej: AFP Integra, ONP, Jubilación Minera
            $table->string('type', 50); // Ej: AFP, ONP, ESPECIAL, MIXTO
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pension_systems');
    }
};
