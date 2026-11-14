<?php 
    declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Esta funcion pinta el pie de la pagina con el año actual (sacado con date('Y')) y el
// nombre del autor, y cierra las etiquetas body y html que abrio renderHeader().
// Se llama siempre al final de cada pagina.

// La segunda parte muestra en el footer el año actual, el nombre del creador de la app, i las visitas que llevamos a la pagina.
function renderFooter(string $Footer): void
{
?>
<footer>
<p><?= e(date('Y')); ?> - <?= e(APP_OWNER) ?> - Visitas: <?= e($_COOKIE['visits'] ?? 0) ?></p>
</footer>
</body>
</html>
<?php
}
?>