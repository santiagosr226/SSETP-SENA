<?php

namespace Database\Seeders;

use App\Models\Ficha;
use App\Models\Programa;
use App\Models\Funcionario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FichasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que existan programas y funcionarios
        if (Programa::count() === 0) {
            Programa::create(['nivel' => 'Técnico', 'nombre' => 'Sistemas']);
            Programa::create(['nivel' => 'Tecnólogo', 'nombre' => 'Analisis y Desarrollo de Software']);
            Programa::create(['nivel' => 'Auxiliar', 'nombre' => 'Archivo y Registro']);
        }

        if (Funcionario::count() === 0) {
            Funcionario::create([
                'nombre' => 'Juan Pérez',
                'correo' => 'juan.perez@example.com',
                'telefono' => '3001112233',
                'rol' => 'instructor',
                'password' => bcrypt('password'),
                'primer_acceso' => false,
            ]);
            Funcionario::create([
                'nombre' => 'María Gómez',
                'correo' => 'maria.gomez@example.com',
                'telefono' => '3004445566',
                'rol' => 'instructor',
                'password' => bcrypt('password'),
                'primer_acceso' => false,
            ]);
        }

        $estados = ['activo', 'inactivo', 'finalizado', 'en curso'];
        $modalidades = ['presencial', 'virtual', 'mixta'];
        $jornadas = ['diurna', 'nocturna', 'mixta'];

        $programas = Programa::all();
        $instructores = Funcionario::where('rol', 'instructor')->get();
        if ($instructores->isEmpty()) {
            $instructores = Funcionario::all();
        }

        // Generar 8 fichas de ejemplo con números únicos
        $usados = [];
        for ($i = 0; $i < 8; $i++) {
            $programa = $programas->random();
            $instructor = $instructores->random();

            // Fechas consistentes
            $inicio = Carbon::now()->subMonths(rand(0, 18))->startOfMonth();
            $finalLectiva = (clone $inicio)->addMonths(rand(4, 12));
            $finalFormacion = (clone $finalLectiva)->addMonths(rand(2, 6));
            $limiteProductiva = (clone $finalFormacion)->addMonths(rand(1, 3));
            $actualizacion = Carbon::now()->subDays(rand(0, 60));

            // Asegurar numero único
            do {
                $numero = (string) (rand(100000, 999999) . rand(10, 99));
            } while (in_array($numero, $usados) || Ficha::where('numero', $numero)->exists());
            $usados[] = $numero;

            Ficha::create([
                'numero' => $numero,
                'estado' => $estados[array_rand($estados)],
                'modalidad' => $modalidades[array_rand($modalidades)],
                'jornada' => $jornadas[array_rand($jornadas)],
                'fecha_inicial' => $inicio->toDateString(),
                'fecha_final_lectiva' => $finalLectiva->toDateString(),
                'fecha_final_formacion' => $finalFormacion->toDateString(),
                'fecha_limite_productiva' => $limiteProductiva->toDateString(),
                'fecha_actualizacion' => $actualizacion->toDateString(),
                'resultados_aprendizaje_totales' => (string) rand(5, 40),
                'programa_id' => $programa->id,
                'funcionario_id' => $instructor->id,
            ]);
        }
    }
}
