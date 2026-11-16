<?php
    declare(strict_types=1);


    // Arranco la sesion aqui, una sola vez. Como cada pagina carga este archivo
    // antes que nada, $_SESSION ya existe en cualquier sitio.
    if (session_status() !== PHP_SESSION_ACTIVE){
        session_start();
    };

    // Este archivo junta todos los demas. Cada pagina solo tiene que hacer require_once de
    // bootstrap.php y ya puede usar las constantes, los arrays y las funciones de abajo.
    include 'config/config.php';
    include 'includes/header.php';
    include 'includes/helpers.php';
    include 'includes/components.php';
    include 'includes/navbar.php';
    include 'includes/footer.php';
    include 'includes/data.php';

    // Cada carga de pagina cuenta como una visita
    sumarVisita();
?>