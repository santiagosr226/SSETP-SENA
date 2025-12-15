<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtraAlternativa extends Model
{
    use HasFactory;

    protected $table = 'otras_alternativas';

    protected $fillable = [
        'aprendiz_id',
        'funcionario_id',
        'alternativa',
        'fecha_inicio_ep',
        'fecha_fin_ep',
        'arl',
        'empresa_proyecto',
        'registro_sofia_plus',
        'fecha_registro_sofia_plus',
        'observaciones_seguimiento',
        'radicado_solicitud',
        'radicado_respuesta'
    ];

    protected $dates = [
        'fecha_inicio_ep',
        'fecha_fin_ep',
        'fecha_registro_sofia_plus',
    ];

    public function aprendiz()
    {
        return $this->belongsTo(Aprendiz::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
