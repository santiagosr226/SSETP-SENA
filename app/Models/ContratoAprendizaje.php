<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoAprendizaje extends Model
{
    use HasFactory;

    protected $table = 'contratos_aprendizaje';

    protected $fillable = [
        // Añade aquí los campos de la tabla cuando los definas en la migración
    ];
}
