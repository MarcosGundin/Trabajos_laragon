<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();

// Datos de partida del formulario: vacio y con la fecha de hoy
$datosForm = [
    'date' => date('Y-m-d'),
    'type' => '',
    'duration' => '',
    'rpe' => '',
    'notes' => '',
    'injury_checked' => false,
    'injury' => '',
];
$fallos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Meto lo que llega del formulario en el array asociativo
    $datosForm = [
        'date' => obtenerTexto('fecha_sesion'),
        'type' => obtenerTexto('tipo_sesion'),
        'duration' => obtenerTexto('minutos'),
        'rpe' => obtenerTexto('esfuerzo'),
        'notes' => obtenerTexto('sensaciones'),
        'injury_checked' => isset($_POST['hay_molestia']),
        'injury' => obtenerTexto('detalle_molestia'),
    ];

    // 2. Lo valido
    $fallos = validateTraining($datosForm);

    // 3. Si no hay fallos lo guardo en el csv (antes era la sesion)
    if ($fallos === []) {
        $guardado = escribir_csv('data/trainings.csv', [
            $_SESSION['user']['username'],
            $datosForm['date'],
            $datosForm['type'],
            (int) $datosForm['duration'],
            (int) $datosForm['rpe'],
            $datosForm['notes'],
            $datosForm['injury_checked'] ? $datosForm['injury'] : '',
        ]);

        if ($guardado) {
            // Recargo la pagina para que F5 no vuelva a guardar el mismo entrenamiento
            header('Location: training.php?guardado=1');
            exit;
        }

        $fallos[] = 'No hemos podido guardar el entrenamiento, prueba otra vez mas tarde.';
    }
}

// Ahora la lista sale del csv y no de la sesion
$listaSesiones = [];
try {
    $listaSesiones = readTrainings('data/trainings.csv', $_SESSION['user']['username']);
} catch (Exception $e) {
    $fallos[] = $e->getMessage();
}
$resumen = resumenEntrenamientos($listaSesiones);

renderHeader('Nuevo entrenamiento');
renderNavbar('Navbar');
?>

<main>
    <h1>Nuevo entrenamiento</h1>

    <?php if (isset($_GET['guardado'])): ?>
        <div class="exito">
            <p>Entrenamiento guardado.</p>
        </div>
    <?php endif; ?>

    <?php if ($fallos !== []): ?>
        <ul class="errores">
            <?php foreach ($fallos as $fallo): ?>
                <li><?= e($fallo) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="<?= e($_SERVER['SCRIPT_NAME']) ?>" method="post" novalidate>
        <?= csrfField() ?>
        <p>
            <label for="fecha_sesion">Fecha: </label>
            <input type="date" name="fecha_sesion" id="fecha_sesion"
                    value="<?= e($datosForm['date']) ?>" max="<?= e(date('Y-m-d')) ?>" required>
        </p>

        <fieldset>
            <legend>Tipo de sesión</legend>
            <?php foreach (TIPOS_SESION as $clave => $etiqueta): ?>
                <label>
                    <input type="radio" name="tipo_sesion" value="<?= e($clave) ?>"
                        <?= $datosForm['type'] === $clave ? 'checked' : '' ?>>
                    <?= e($etiqueta) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <p>
            <label for="minutos">Duración (minutos): </label>
            <input type="number" name="minutos" id="minutos"
                    value="<?= e($datosForm['duration']) ?>"
                    min="<?= MINUTOS_MIN ?>" max="<?= MINUTOS_MAX ?>" required>
        </p>

        <fieldset>
            <legend>Esfuerzo percibido (RPE, de 1 a 10)</legend>
            <?php for ($n = ESFUERZO_MIN; $n <= ESFUERZO_MAX; $n++): ?>
                <label>
                    <input type="radio" name="esfuerzo" value="<?= $n ?>"
                        <?= $datosForm['rpe'] === (string) $n ? 'checked' : '' ?>>
                    <?= $n ?>
                </label>
            <?php endfor; ?>
        </fieldset>

        <p>
            <label for="sensaciones">Sensaciones (máx. <?= LIMITE_SENSACIONES ?> caracteres): </label>
            <textarea name="sensaciones" id="sensaciones" rows="3"
                        maxlength="<?= LIMITE_SENSACIONES ?>"><?= e($datosForm['notes']) ?></textarea>
        </p>

        <!-- La descripcion solo se ve si la casilla esta marcada, lo hace el CSS -->
        <div class="bloque-molestia">
            <label>
                <input type="checkbox" name="hay_molestia" id="hay_molestia" value="1"
                    <?= $datosForm['injury_checked'] ? 'checked' : '' ?>>
                He tenido alguna lesión o molestia
            </label>

            <p class="detalle-molestia">
                <label for="detalle_molestia">Describe la lesión o molestia: </label>
                <input type="text" name="detalle_molestia" id="detalle_molestia"
                        value="<?= e($datosForm['injury']) ?>">
            </p>
        </div>

        <button type="submit">Guardar entrenamiento</button>
    </form>

    <section class="zona-tabla">
        <h2>Entrenamientos registrados</h2>

        <?php if ($listaSesiones === []): ?>
            <p>Todavía no has registrado ningún entrenamiento.</p>
        <?php else: ?>
            <?= renderTablaSesiones($listaSesiones) ?>
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
        <?php endif; ?>
    </section>
</main>

<?php renderFooter('Footer'); ?>