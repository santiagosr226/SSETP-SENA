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
                'nombre' => 'Auxiliar en Sistemas Informáticos',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Electricidad Industrial',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Mecánica Automotriz',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Mantenimiento de Equipos',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Logística y Transporte',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Administración',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Contabilidad',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Secretariado',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Panadería',
            ],
            [
                'nivel' => 'Auxiliar',
                'nombre' => 'Auxiliar en Cocina',
            ],

            // Programas Operarios (10)
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Soldadura',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Máquinas y Herramientas',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Refrigeración',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Plomería',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Carpintería',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Instalaciones Eléctricas',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Mecánica Industrial',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Pintura Industrial',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Mantenimiento de Edificios',
            ],
            [
                'nivel' => 'Operario',
                'nombre' => 'Operario en Jardinería y Paisajismo',
            ],

            // Programas Técnicos (10)
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Desarrollo de Software',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Redes y Telecomunicaciones',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Mecatrónica',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Automatización Industrial',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Electricidad y Electrónica',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Gestión Administrativa',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Contabilidad y Finanzas',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Seguridad y Salud en el Trabajo',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Gastronomía',
            ],
            [
                'nivel' => 'Técnico',
                'nombre' => 'Técnico en Diseño Gráfico',
            ],

            // Programas Tecnólogos (10)
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Análisis y Desarrollo de Sistemas',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Gestión de Redes de Datos',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Mecánica Industrial',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Automatización y Control',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Gestión Empresarial',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Gestión Financiera',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Gestión Ambiental',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Gestión Logística',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Diseño y Desarrollo de Productos',
            ],
            [
                'nivel' => 'Tecnólogo',
                'nombre' => 'Tecnólogo en Gestión de la Calidad',
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