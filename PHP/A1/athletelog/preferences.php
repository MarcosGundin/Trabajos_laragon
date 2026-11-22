<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();

$fallos = [];

// Para empezar uso lo que haya en las cookies, o los valores por defecto
$temaElegido = temaActual();
$unidadElegida = cookieOpcion('unidad_peso', OPCIONES_UNIDAD, 'kg');
$verConsejos = ($_COOKIE['consejos'] ?? '') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Boton "Restablecer": borro las tres cookies dandoles una caducidad pasada
    if (isset($_POST['restablecer'])) {
        establecerCookie('tema', '', time() - 3600);
        establecerCookie('unidad_peso', '', time() - 3600);
        establecerCookie('consejos', '', time() - 3600);

        header('Location: preferences.php?restablecido=1');
        exit;
    }

    // Boton "Guardar"
    $temaElegido = obtenerTexto('tema_web');
    $unidadElegida = obtenerTexto('unidad_peso');
    // Un checkbox solo llega por POST cuando esta marcado, de ahi el isset
    $verConsejos = isset($_POST['ver_consejos']);

    // Solo vale lo que esta en las listas de config.php: desde el navegador se
    // puede cambiar el value de un radio o de un select
    if (!array_key_exists($temaElegido, OPCIONES_TEMA)) {
        $fallos[] = 'El tema seleccionado no es válido.';
    }
    if (!array_key_exists($unidadElegida, OPCIONES_UNIDAD)) {
        $fallos[] = 'La unidad de peso seleccionada no es válida.';
    }

    if ($fallos === []) {
        $limite = time() + VIDA_COOKIE;

        establecerCookie('tema', $temaElegido, $limite);
        establecerCookie('unidad_peso', $unidadElegida, $limite);
        establecerCookie('consejos', $verConsejos ? '1' : '0', $limite);

        // Recargo la pagina porque las cookies nuevas no llegan hasta la
        // siguiente peticion
        header('Location: preferences.php?guardado=1');
        exit;
    }
}

$aviso = '';
if (isset($_GET['guardado'])) {
    $aviso = 'Preferencias guardadas durante 30 días.';
} elseif (isset($_GET['restablecido'])) {
    $aviso = 'Preferencias restablecidas, se han borrado las cookies.';
}

renderHeader('Preferencias');
renderNavbar('Navbar');
?>

<main>
    <h1>Preferencias</h1>

    <?php if ($aviso !== ''): ?>
        <div class="exito">
            <p><?= e($aviso) ?></p>
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
        <fieldset>
            <legend>Tema</legend>
            <?php foreach (OPCIONES_TEMA as $clave => $etiqueta): ?>
                <label>
                    <input type="radio" name="tema_web" value="<?= e($clave) ?>"
                        <?= $temaElegido === $clave ? 'checked' : '' ?>>
                    <?= e($etiqueta) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <p>
            <label for="unidad_peso">Unidad de peso: </label>
            <select name="unidad_peso" id="unidad_peso">
                <?php foreach (OPCIONES_UNIDAD as $clave => $etiqueta): ?>
                    <option value="<?= e($clave) ?>" <?= $unidadElegida === $clave ? 'selected' : '' ?>>
                        <?= e($etiqueta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label>
                <input type="checkbox" name="ver_consejos" value="1" <?= $verConsejos ? 'checked' : '' ?>>
                Mostrar consejos
            </label>
        </p>

        <div class="botones">
            <button type="submit" name="guardar">Guardar preferencias</button>
            <button type="submit" name="restablecer" class="boton-gris">Restablecer preferencias</button>
        </div>
    </form>
</main>

<?php renderFooter('Footer'); ?>