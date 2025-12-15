<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nivel',
        'nombre',
    ];

    public function fichas()
    {
        return $this->hasMany(Ficha::class);
    }
}
