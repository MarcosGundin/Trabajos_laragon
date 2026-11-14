<?php
declare(strict_types=1);

// Construye la tabla con los entrenamientos guardados en la sesion y la devuelve
// como texto HTML. Cada dato pasa por e() antes de meterlo en la tabla.
function renderTablaSesiones(array $sesiones): string
{
    $tabla = '<table>'
        . '<tr><th>#</th><th>Fecha</th><th>Tipo</th><th>Minutos</th><th>RPE</th>'
        . '<th>Carga</th><th>Sensaciones</th><th>Lesión</th></tr>';

    foreach ($sesiones as $pos => $s) {
        // La fecha se guarda como AAAA-MM-DD pero se enseña como DD/MM/AAAA
        $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $s['date']);

        $tabla .= '<tr>'
            . '<td>' . e($pos + 1) . '</td>'
            . '<td>' . e($fechaObj ? $fechaObj->format('d/m/Y') : $s['date']) . '</td>'
            . '<td>' . e(TIPOS_SESION[$s['type']]) . '</td>'
            . '<td>' . e($s['duration']) . '</td>'
            . '<td>' . e($s['rpe']) . '</td>'
            . '<td>' . e(calculateLoad($s['duration'], $s['rpe'])) . '</td>'
            . '<td>' . e($s['notes'] !== '' ? $s['notes'] : '-') . '</td>'
            . '<td>' . e($s['injury'] !== '' ? $s['injury'] : 'No') . '</td>'
            . '</tr>';
    }

    return $tabla . '</table>';
}