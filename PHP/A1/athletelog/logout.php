<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Primero vacio lo que hay en la sesion, luego la destruyo y vuelvo al login
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;