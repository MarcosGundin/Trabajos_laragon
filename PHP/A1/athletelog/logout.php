<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Elimino el token CSRF de la sesión.
unset($_SESSION['csrf_token']);

// Vacío los datos de la sesión y la destruyo.
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;