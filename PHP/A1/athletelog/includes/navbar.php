<?php 
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

// Dibuja el menu. Miro si hay usuario en la sesion: con sesion se usa el menu
// completo, con el saludo y el enlace de cerrar sesion, y sin sesion el basico.
function renderNavbar(string $navbar): void
{
    $pagina_actual = basename($_SERVER['PHP_SELF']);
    $hayUsuario = isset($_SESSION['user']);
    $enlaces = $hayUsuario ? MENU_CON_SESION : MENU_SIN_SESION;
?>
<nav>
    <span>Athlete Log</span>
    <ul>
        <?php if ($hayUsuario): ?>
            <li><span>Hola, <?= e($_SESSION['user']['name']) ?></span></li>
        <?php endif; ?>

        <?php foreach ($enlaces as $archivo => $texto): ?>
            <li><a href="<?= e($archivo) ?>"<?php echo esActiva($archivo, $pagina_actual); ?>><?= e($texto) ?></a></li>
        <?php endforeach ?>

        <?php if ($hayUsuario): ?>
            <li><a href="logout.php">Cerrar sesión</a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php
}