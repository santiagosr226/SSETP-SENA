<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ficha extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'estado',
        'modalidad',
        'jornada',
        'fecha_inicial',
        'fecha_final_lectiva',
        'fecha_final_formacion',
        'fecha_limite_productiva',
        'fecha_actualizacion',
        'resultados_aprendizaje_totales',
        'programa_id',
        'funcionario_id'
    ];

    protected $dates = [
        'fecha_inicial',
        'fecha_final_lectiva',
        'fecha_final_formacion',
        'fecha_limite_productiva',
        'fecha_actualizacion',
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function aprendices()
    {
        return $this->hasMany(Aprendiz::class);
    }
}
