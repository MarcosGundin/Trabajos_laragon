<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();

$error = '';
$todas = [];

// Leo las sesiones del usuario. Si falla el fichero guardo el mensaje para enseñarlo
try {
    $todas = readTrainings('data/trainings.csv', $_SESSION['user']['username']);
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Boton "Descargar mi historial": genero el csv aqui mismo, antes de pintar nada de la pagina.
// $todas ya tiene solo las sesiones del usuario en sesion, asi que no hay datos de nadie mas.
if (isset($_GET['descargar']) && $error === '') {
    // estas dos cabeceras hacen que el navegador lo descargue como un csv
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="mi_historial.csv"');

    $salida = fopen('php://output', 'w');
    fwrite($salida, "\xEF\xBB\xBF"); // esto es para que Excel lea bien las tildes
    fputcsv($salida, ['username', 'date', 'type', 'duration', 'rpe', 'notes', 'injury']);
    foreach ($todas as $s) {
        fputcsv($salida, $s);
    }
    fclose($salida);
    exit;
}

// Ordeno por fecha de mas nueva a mas vieja (el formato Y-m-d se puede comparar tal cual)
usort($todas, function ($a, $b) {
    return $b['date'] <=> $a['date'];
});

// Filtro por tipo. Si el valor no es un tipo que existe, lo dejo vacio (todos)
$tipoFiltro = $_GET['tipo'] ?? '';
if (!is_string($tipoFiltro) || !array_key_exists($tipoFiltro, TIPOS_SESION)) {
    $tipoFiltro = '';
}

$sesiones = [];
foreach ($todas as $s) {
    if ($tipoFiltro === '' || $s['type'] === $tipoFiltro) {
        $sesiones[] = $s;
    }
}

// Estadisticas del paso 6 con la misma funcion
$resumen = resumenEntrenamientos($sesiones);

// Opcional: resumen por tipo con un array acumulador
$porTipo = [];
foreach ($todas as $s) {
    if (!isset($porTipo[$s['type']])) {
        $porTipo[$s['type']] = ['sesiones' => 0, 'minutos' => 0];
    }
    $porTipo[$s['type']]['sesiones']++;
    $porTipo[$s['type']]['minutos'] += $s['duration'];
}

renderHeader('Historial');
renderNavbar('Navbar');
?>

<main>
    <h1>Historial</h1>

    <?php if ($error !== ''): ?>
        <ul class="errores">
            <li><?= e($error) ?></li>
        </ul>
    <?php elseif ($todas === []): ?>
        <p>Todavía no has registrado ningún entrenamiento.</p>
    <?php else: ?>
        <form action="history.php" method="get">
            <label for="tipo">Filtrar por tipo de sesión: </label>
            <select name="tipo" id="tipo">
                <option value="">Todos</option>
                <?php foreach (TIPOS_SESION as $clave => $etiqueta): ?>
                    <option value="<?= e($clave) ?>" <?= $tipoFiltro === $clave ? 'selected' : '' ?>>
                        <?= e($etiqueta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>

        <section class="zona-tabla">
            <?php if ($sesiones === []): ?>
                <p>No hay sesiones de ese tipo.</p>
            <?php else: ?>
                <?= renderTablaSesiones($sesiones) ?>

                <!-- EJERCICIO 8: Borrado seguro por POST + token CSRF -->
                <h3>Eliminar sesiones</h3>
                <ul>
                    <?php foreach ($sesiones as $idx => $s): ?>
                        <li>
                            <?= e($s['date']) ?> - <?= e(TIPOS_SESION[$s['type']] ?? $s['type']) ?> (<?= e($s['duration']) ?> min)
                            <form action="delete.php" method="post" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= e($idx) ?>">
                                <button type="submit">Borrar</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h2>Estadísticas</h2>
            <div class="panel-resumen">
                <div class="dato">
                    <span class="dato-numero"><?= e($resumen['total']) ?></span>
                    <span class="dato-texto">Sesiones</span>
                </div>
                <div class="dato">
                    <span class="dato-numero"><?= e($resumen['minutos']) ?></span>
                    <span class="dato-texto">Minutos totales</span>
                </div>
                <div class="dato">
                    <span class="dato-numero"><?= e(number_format($resumen['esfuerzo_medio'], 1, ',', '.')) ?></span>
                    <span class="dato-texto">RPE medio</span>
                </div>
                <div class="dato">
                    <span class="dato-numero"><?= e($resumen['carga']) ?></span>
                    <span class="dato-texto">Carga total</span>
                </div>
            </div>

            <h2>Resumen por tipo de sesión</h2>
            <table>
                <tr><th>Tipo</th><th>Sesiones</th><th>Minutos</th></tr>
                <?php foreach ($porTipo as $tipo => $datos): ?>
                    <tr>
                        <td><?= e(TIPOS_SESION[$tipo] ?? $tipo) ?></td>
                        <td><?= e($datos['sesiones']) ?></td>
                        <td><?= e($datos['minutos']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <form action="history.php" method="get">
                <button type="submit" name="descargar" value="1">Descargar mi historial</button>
            </form>
        </section>
    <?php endif; ?>
</main>

<?php renderFooter('Footer'); ?>