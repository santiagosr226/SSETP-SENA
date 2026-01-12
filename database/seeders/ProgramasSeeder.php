<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programa;
use Illuminate\Support\Facades\DB;

class ProgramasSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la tabla primero
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('programas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $programas = [
            // Programas Auxiliares (10)
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Sistemas Informáticos',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Electricidad Industrial',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Mecánica Automotriz',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Mantenimiento de Equipos',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Logística y Transporte',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Administración',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Contabilidad',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Secretariado',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Panadería',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Cocina',
            ],

            // Programas Operarios (10)
            [
                'nivel' => 'Operario',
                'nombre' => 'Soldadura',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Máquinas y Herramientas',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Refrigeración',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Plomería',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Carpintería',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Instalaciones Eléctricas',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Mecánica Industrial',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Pintura Industrial',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Mantenimiento de Edificios',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Jardinería y Paisajismo',
            ],

            // Programas Técnicos (10)
            [
                'nivel' => 'Técnico',
                'nombre' => 'Desarrollo de Software',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Redes y Telecomunicaciones',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Mecatrónica',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Automatización Industrial',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Electricidad y Electrónica',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Gestión Administrativa',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Contabilidad y Finanzas',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Seguridad y Salud en el Trabajo',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Gastronomía',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Diseño Gráfico',
            ],

            // Programas Tecnólogos (10)
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Análisis y Desarrollo de Sistemas',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Gestión de Redes de Datos',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Mecánica Industrial',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Automatización y Control',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Gestión Empresarial',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Gestión Financiera',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Gestión Ambiental',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Gestión Logística',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Diseño y Desarrollo de Productos',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Gestión de la Calidad',
            ],
        ];

        // Insertar todos los programas
        foreach ($programas as $programa) {
            Programa::create($programa);
        }

        $this->command->info('✅ 40 programas de formación creados exitosamente!');
        $this->command->info('📊 Distribución:');
        $this->command->info('   • 10 programas Auxiliares');
        $this->command->info('   • 10 programas Operarios');
        $this->command->info('   • 10 programas Técnicos');
        $this->command->info('   • 10 programas Tecnólogos');
    }
}