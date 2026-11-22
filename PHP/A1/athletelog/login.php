<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$nombreIntroducido = '';
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreIntroducido = obtenerTexto('usuario');

    // La contraseña va sin trim, como en register.php
    $claveBruta = $_POST['password'] ?? '';
    $claveIntroducida = is_string($claveBruta) ? $claveBruta : '';

    // Busco al usuario en $users.
    $ficha = $users[$nombreIntroducido] ?? null;

    // Compruebo el usuario y la contraseña.
    if ($ficha !== null && password_verify($claveIntroducida, $ficha['password_hash'])) {

        // Regenero el identificador de la sesión.
        session_regenerate_id(true);

        // Regenero también el token CSRF.
        unset($_SESSION['csrf_token']);
        generateCsrfToken();

        // Guardo los datos del usuario en la sesión.
        $_SESSION['user'] = [
            'username' => $nombreIntroducido,
            'name' => $ficha['name'],
            'sport' => $ficha['sport'],
        ];

        header('Location: index.php');
        exit;
    }

    $mensajeError = 'Usuario o contraseña incorrectos';
}

renderHeader();
renderNavbar('Navbar');
?>

<main>
    <h1>Iniciar sesión</h1>

    <?php if ($mensajeError !== ''): ?>
        <ul class="errores">
            <li><?= e($mensajeError) ?></li>
        </ul>
    <?php endif; ?>

    <form action="<?= e($_SERVER['SCRIPT_NAME']) ?>" method="post" novalidate>
        <?= csrfField() ?>
        <p>
            <label for="usuario">Usuario: </label>
            <input type="text" name="usuario" id="usuario"
                    value="<?= e($nombreIntroducido) ?>" required>
        </p>

        <p>
            <label for="password">Contraseña: </label>
            <input type="password" name="password" id="password" required>
        </p>

        <button type="submit">Entrar</button>
    </form>
</main>

<?php renderFooter('Footer'); ?>