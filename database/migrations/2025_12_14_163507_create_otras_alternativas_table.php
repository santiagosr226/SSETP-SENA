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
        Schema::create('otras_alternativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aprendiz_id')->constrained('aprendices')->onDelete('cascade');
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->onDelete('set null');
            $table->string('alternativa');
            $table->date('fecha_inicio_ep');
            $table->date('fecha_fin_ep');
            $table->string('arl');
            $table->text('empresa_proyecto');
            $table->boolean('registro_sofia_plus')->default(false);
            $table->date('fecha_registro_sofia_plus');
            $table->text('observaciones_seguimiento');
            $table->string('radicado_solicitud');
            $table->string('radicado_respuesta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otras_alternativas');
    }
};
