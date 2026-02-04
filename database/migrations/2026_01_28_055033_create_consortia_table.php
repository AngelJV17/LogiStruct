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
        Schema::create('consortia', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->unique()->nullable(); // Algunos consorcios no tienen RUC propio de inmediato
            $table->string('name');
            $table->string('url_logo')->nullable();

            // Datos del representante del consorcio
            $table->string('legal_representative')->nullable();
            $table->string('representative_dni')->nullable();
            $table->string('representative_email')->nullable();
            $table->string('representative_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consortia');
    }
};
