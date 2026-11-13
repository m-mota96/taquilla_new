<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    /**
     * Cargos disponibles por tipo de evento (ver config/taquilla.php event_types).
     *
     * @return void
     */
    public function run()
    {
        $positions = [
            1 => [ // WF
                'EMPRESARIO', 'CEO', 'COACH', 'COORDINADOR', 'SUBDIRECCIÓN',
                'GERENTE', 'MARKETING', 'ATENCIÓN A CLIENTES', 'OPERACIONES', 'OTRO',
            ],
            2 => [ // COMVET
                'EMPRESARIO', 'CEO', 'MVZ',
            ],
        ];

        foreach ($positions as $event_type => $names) {
            foreach ($names as $name) {
                Position::firstOrCreate(['event_type' => $event_type, 'name' => $name]);
            }
        }
    }
}
