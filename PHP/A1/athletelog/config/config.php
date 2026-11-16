<?php 
    declare(strict_types=1);

    // Datos generales de la aplicacion. pageTitle es el titulo que sale en la pestaña del
    // navegador y APP_OWNER el nombre del autor, que se muestra en el footer.
    const pageTitle = "Athelte Log";
    const APP_OWNER = "Marcos Gundin Navarro";

    // Menu de navegacion. La clave es el archivo al que lleva el enlace y el valor es el
    // texto que se ve. La navbar recorre este array con un foreach para crear los enlaces,
    // asi si quiero añadir una pagina solo tengo que añadirla aqui.
    // Dos menus segun el estado: uno sin sesion y otro con sesion iniciada
    const MENU_SIN_SESION = [
        'register.php' => 'Registro',
        'login.php' => 'Login',
    ];

    const MENU_CON_SESION = [
        'index.php' => 'Inicio',
        'training.php' => 'Nuevo entrenamiento',
        'history.php' => 'Historial',
        'preferences.php' => 'Preferencias',
    ];
    
     // Cuanto viven las cookies: 30 dias, en segundos
    const VIDA_COOKIE = 60 * 60 * 24 * 30;

    // Opciones que se aceptan en las preferencias. La clave es lo que se guarda en
    // la cookie y el valor lo que se ve en pantalla. Las claves del tema son light y
    // dark porque despues forman la clase del body (theme-light / theme-dark).
    const OPCIONES_TEMA = [
        'light' => 'Claro',
        'dark' => 'Oscuro',
        'pink' => 'Rosa',
    ];

    const OPCIONES_UNIDAD = [
        'kg' => 'kg',
        'lb' => 'lb',
    ];
     // Tipos de sesion que se pueden elegir
    const TIPOS_SESION = [
        'tecnica' => 'Técnica',
        'fuerza' => 'Fuerza',
        'resistencia' => 'Resistencia',
        'competicion' => 'Competición',
        'recuperacion' => 'Recuperación',
    ];
      // Limites de los campos del formulario de entrenamientos
    const MINUTOS_MIN = 5;
    const MINUTOS_MAX = 360;
    const ESFUERZO_MIN = 1;
    const ESFUERZO_MAX = 10;
    const LIMITE_SENSACIONES = 200;
?>