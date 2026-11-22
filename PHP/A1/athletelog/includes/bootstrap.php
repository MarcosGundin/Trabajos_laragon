<?php
    declare(strict_types=1);


    // Arranco la sesion aqui, una sola vez. Como cada pagina carga este archivo
    // antes que nada, $_SESSION ya existe en cualquier sitio.
    if (session_status() !== PHP_SESSION_ACTIVE){
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        );

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

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
    
    // Compruebo el token CSRF en todas las peticiones POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && CSRF_ACTIVADO) {
    $token = $_POST['csrf_token'] ?? null;

    // Si el token no es texto o no coincide, rechazo la peticion.
    if (!is_string($token) || !verifyCsrfToken($token)) {
        http_response_code(403);
        exit('Solicitud no válida. El token CSRF no es correcto.');
    }
}

// Cada carga de pagina cuenta como una visita.
sumarVisita();
?>