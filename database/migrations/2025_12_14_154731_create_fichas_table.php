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
        Schema::create('fichas', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->string('estado');
            $table->string('modalidad');
            $table->string('jornada');
            $table->date('fecha_inicial');
            $table->date('fecha_final_lectiva');
            $table->date('fecha_final_formacion');
            $table->date('fecha_limite_productiva');
            $table->date('fecha_actualizacion');
            $table->string('resultados_aprendizaje_totales');
            $table->foreignId('programa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funcionario_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**º
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichas');
    }
};
