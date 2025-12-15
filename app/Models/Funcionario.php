<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'correo',
        'telefono',
        'rol',
        'password',
        'primer_acceso'
    ];

    protected $hidden = [
        'password',
    ];

    public function fichas()
    {
        return $this->hasMany(Ficha::class);
    }

    public function otrasAlternativas()
    {
        return $this->hasMany(OtraAlternativa::class);
    }
}
