<?php

namespace Database\Seeders;

use App\Models\PreOperationalItem;
use Illuminate\Database\Seeder;

class PreOperationalItemSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const SECTIONS = [
        'Condiciones generales' => [
            '¿La máquina se encuentra en buen estado general?',
            '¿La máquina está correctamente instalada y estable?',
            '¿La estructura, carcasa o bastidor presenta buen estado?',
            '¿No se observan daños, golpes, grietas o deformaciones?',
            '¿Los componentes de la máquina se encuentran correctamente instalados y asegurados?',
            '¿El área alrededor de la máquina está limpia, ordenada y libre de obstáculos?',
        ],
        'Seguridad y protecciones' => [
            '¿Las guardas y protecciones de la máquina están instaladas y en buen estado?',
            '¿Las partes móviles peligrosas se encuentran protegidas?',
            '¿Los dispositivos de seguridad funcionan correctamente?',
            '¿La parada de emergencia está visible, accesible y funciona correctamente?',
            '¿Los sensores, interruptores o sistemas de seguridad funcionan correctamente, cuando aplican?',
            '¿La señalización y etiquetas de seguridad son visibles y legibles?',
        ],
        'Sistema eléctrico y controles' => [
            '¿Los cables y conexiones eléctricas visibles están en buen estado?',
            '¿No existen cables expuestos, quemados o deteriorados?',
            '¿El tablero o gabinete eléctrico se encuentra en buen estado y cerrado?',
            '¿Los botones, interruptores, selectores y controles funcionan correctamente?',
            '¿Los indicadores, alarmas o pantallas funcionan correctamente?',
            '¿La conexión a tierra se encuentra en condiciones adecuadas, cuando aplica?',
        ],
        'Sistema mecánico' => [
            '¿Las partes móviles se encuentran en buen estado?',
            '¿Los elementos mecánicos están correctamente ajustados y asegurados?',
            '¿No existen piezas sueltas, rotas o excesivamente desgastadas?',
            '¿No se presentan vibraciones anormales durante el funcionamiento?',
            '¿No se presentan ruidos anormales durante el funcionamiento?',
            '¿No se observan fugas de aceite, grasa, agua, aire u otros fluidos?',
        ],
        'Sistemas auxiliares' => [
            '¿Las mangueras, tuberías y conexiones están en buen estado, cuando aplican?',
            '¿Los niveles de aceite, lubricante u otros fluidos son adecuados, cuando aplican?',
            '¿Los sistemas hidráulicos o neumáticos funcionan correctamente, cuando aplican?',
            '¿Los sistemas de refrigeración o ventilación funcionan correctamente, cuando aplican?',
        ],
        'Operación' => [
            '¿La máquina enciende y arranca normalmente?',
            '¿Los controles responden correctamente?',
            '¿No se presentan alarmas o fallas durante el arranque?',
            '¿Los parámetros de operación se encuentran dentro de los valores establecidos?',
            '¿La máquina funciona de manera estable?',
            '¿La máquina realiza su función de manera segura y normal?',
        ],
        'Condiciones del operador' => [
            '¿El operador conoce el funcionamiento básico de la máquina?',
            '¿El operador conoce los principales riesgos asociados a la máquina?',
            '¿El operador cuenta con los elementos de protección personal requeridos?',
            '¿El operador conoce la ubicación y funcionamiento de la parada de emergencia?',
        ],
    ];

    public function run(): void
    {
        if (PreOperationalItem::query()->exists()) {
            return;
        }

        $order = 0;

        foreach (self::SECTIONS as $section => $labels) {
            foreach ($labels as $label) {
                PreOperationalItem::create([
                    'section' => $section,
                    'label' => $label,
                    'order' => $order++,
                ]);
            }
        }
    }
}
