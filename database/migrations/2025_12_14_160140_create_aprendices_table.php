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
        Schema::create('aprendices', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('tipo_documento');
            $table->string('documento');
            $table->string('correo');
            $table->string('telefono');
            $table->string('estado');
            $table->string('alternativa')->nullable();
            $table->string('resultados_aprendizaje');
            $table->string('password');
            $table->boolean('primer_acceso')->default(false);
            $table->string('fecha_actualizacion');
            $table->foreignId('ficha_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aprendices');
    }
};
