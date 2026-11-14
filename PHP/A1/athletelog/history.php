<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();

renderHeader('Historial');
renderNavbar('Navbar');
?>

<main>
    <h1>Historial</h1>
</main>

<?php renderFooter('Footer'); ?>