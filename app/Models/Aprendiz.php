<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aprendiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'tipo_documento',
        'documento',
        'correo',
        'telefono',
        'estado',
        'alternativa',
        'resultados_aprendizaje',
        'password',
        'primer_acceso',
        'fecha_actualizacion',
        'ficha_id'
    ];

    protected $hidden = [
        'password',
    ];

    protected $dates = [
        'fecha_actualizacion',
    ];

    public function ficha()
    {
        return $this->belongsTo(Ficha::class);
    }

    public function otrasAlternativas()
    {
        return $this->hasMany(OtraAlternativa::class, 'aprendiz_id');
    }
}
