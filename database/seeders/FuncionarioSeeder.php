<?php

namespace Database\Seeders;

use App\Models\Funcionario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FuncionarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('es_ES');
        $roles = ['administrador', 'coordinador', 'instructor'];

        // Crear un administrador por defecto
        Funcionario::create([
            'nombre' => 'Administrador Principal',
            'correo' => 'admin@sena.edu.co',
            'telefono' => '3001234567',
            'rol' => 'administrador',
            'password' => Hash::make('Sena2024'),
            'primer_acceso' => false,
        ]);

        // Generar 39 funcionarios adicionales
        for ($i = 0; $i < 39; $i++) {

            // Nombres originales (pueden tener acentos)
            $firstNameOriginal = $faker->firstName;
            $lastNameOriginal  = $faker->lastName . ' ' . $faker->lastName;

            // Nombre completo (se puede guardar con acentos sin problema)
            $fullName = $firstNameOriginal . ' ' . $lastNameOriginal;

            // Limpiar texto SOLO para el correo
            $firstNameClean = Str::slug($firstNameOriginal, '');
            $lastNameClean  = Str::slug(str_replace(' ', '', $lastNameOriginal), '');

            // Construir correo sin acentos ni caracteres raros
            $email = strtolower(substr($firstNameClean, 0, 1) . $lastNameClean) . '@sena.edu.co';

            // Asegurar que el correo sea único
            while (Funcionario::where('correo', $email)->exists()) {
                $email = strtolower(
                    substr($firstNameClean, 0, 1) .
                    Str::random(1) .
                    $lastNameClean
                ) . '@sena.edu.co';
            }

            // Distribución de roles
            if ($i < 2) {
                $role = 'administrador';
            } elseif ($i < 10) {
                $role = 'coordinador';
            } else {
                $role = 'instructor';
            }

            Funcionario::create([
                'nombre' => $fullName,
                'correo' => $email,
                'telefono' => '3' . $faker->numberBetween(0, 9) . $faker->numberBetween(1000000, 9999999),
                'rol' => $role,
                'password' => Hash::make('Sena2024'),
                'primer_acceso' => true,
            ]);
        }
    }
}
