<?php 
    declare(strict_types=1);

    /** Escapa texto para imprimirlo seguro en HTML (evita XSS). */
    function e(string|int|float|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
  
    // Genera un token CSRF para la sesion actual
    function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    // Devuelve el campo oculto con el token CSRF para incluirlo en los formularios.
    function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
    }

    // Comprueba si el token recibido coincide con el guardado en la sesion.
    function verifyCsrfToken(?string $token): bool
    {
        if (
            !isset($_SESSION['csrf_token']) ||
            !is_string($_SESSION['csrf_token']) ||
            $token === null
        ) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // Sirve para saber si un enlace del menu es el de la pagina actual. Compara el archivo del
    // enlace con el archivo en el que estamos y, si coinciden, devuelve el atributo
    // class="activo" para que el CSS lo pinte distinto. Si no coinciden devuelve un texto vacio.
    function esActiva($pagina, $actual) {
    return $pagina === $actual ? ' class="activo"' : '';
    }
    // Recibe el array de deportes y una categoria y devuelve un array nuevo solo con los
    // deportes de esa categoria. Recorre todos con un foreach y va guardando en $result los
    // que coinciden. Se usa en el index para separar individuales y de equipo.
    function getSportsByCategory(array $sports, string $category): array
    {
        $result = [];

        foreach ($sports as $sport) {
            if ($sport['category'] == $category) {
                $result[] = $sport;
            }
        }

        return $result;
    }
    // Devuelve un span con el mensaje de error de un campo concreto, ya escapado con e().
    // Si ese campo no tiene error en el array $errors devuelve un texto vacio, asi se puede
    // llamar siempre debajo de cada input sin comprobar nada antes.
    function fieldError(array $errors, string $field): string
{
    return isset($errors[$field])
        ? '<span class="field-error" role="alert">' . e($errors[$field]) . '</span>'
        : '';
}
    // Coge un campo de $_POST como texto. Si no existe, o llega como array porque
    // alguien ha toqueteado el formulario, devuelve una cadena vacia. Quita los
    // espacios de los lados con trim.
    function obtenerTexto(string $campo): string
    {
        $dato = $_POST[$campo] ?? '';
        return is_string($dato) ? trim($dato) : '';
    }
    // Si no hay usuario en la sesion redirige al login. El exit es para que la
    // pagina no siga ejecutandose despues de mandar la redireccion.
    function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: login.php');
            exit;
        }
    }
    // Crea una cookie. Para borrarla se le pasa una caducidad que ya haya pasado.
    // path / hace que valga en toda la web, httponly impide que JavaScript la lea y
    // samesite evita que se mande en peticiones POST que vengan de otras webs.
    function establecerCookie(string $nombre, string $valor, int $caduca): void
    {
        setcookie($nombre, $valor, [
            'expires' => $caduca,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    function cookieOpcion(string $nombre, array $opciones, string $porDefecto): string
    {
        $guardado = $_COOKIE[$nombre] ?? '';
        if (is_string($guardado) && array_key_exists($guardado, $opciones)) {
            return $guardado;
        }
        return $porDefecto;
    }

    // Tema que hay que aplicar (claro por defecto)
    function temaActual(): string
    {
        return cookieOpcion('tema', OPCIONES_TEMA, 'light');
    }
    // Suma una visita a la cookie visits. Tambien actualizo $_COOKIE a mano para que
    // el footer de esta misma peticion ya enseñe el numero nuevo.
    function sumarVisita(): void
    {
        $previas = $_COOKIE['visits'] ?? '0';
        $total = (is_string($previas) ? max(0, (int) $previas) : 0) + 1;

        establecerCookie('visits', (string) $total, time() + VIDA_COOKIE);
        $_COOKIE['visits'] = (string) $total;
    }

    // Comprueba los datos de un entrenamiento y devuelve una lista con los mensajes
    // de error. Si la lista viene vacia es que esta todo correcto.
    function validateTraining(array $input): array
    {
        $fallos = [];

        // Fecha: obligatoria, real y que no sea de un dia futuro.
        // Los textos Y-m-d se pueden comparar con > porque se ordenan como fechas.
        $dia = $input['date'];
        if ($dia === '') {
            $fallos[] = 'La fecha es obligatoria.';
        } else {
            $objetoFecha = DateTimeImmutable::createFromFormat('!Y-m-d', $dia);
            if ($objetoFecha === false || $objetoFecha->format('Y-m-d') !== $dia) {
                $fallos[] = 'La fecha no es válida.';
            } elseif ($dia > date('Y-m-d')) {
                $fallos[] = 'La fecha no puede ser futura.';
            }
        }

        // Tipo: tiene que ser una de las claves de TIPOS_SESION
        if (!array_key_exists($input['type'], TIPOS_SESION)) {
            $fallos[] = 'Elige un tipo de sesión válido.';
        }

        // Duracion: entero entre 5 y 360
        $minutos = filter_var($input['duration'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => MINUTOS_MIN, 'max_range' => MINUTOS_MAX],
        ]);
        if ($minutos === false) {
            $fallos[] = 'La duración debe ser un entero entre ' . MINUTOS_MIN . ' y ' . MINUTOS_MAX . ' minutos.';
        }

        // RPE: entero entre 1 y 10
        $esfuerzo = filter_var($input['rpe'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => ESFUERZO_MIN, 'max_range' => ESFUERZO_MAX],
        ]);
        if ($esfuerzo === false) {
            $fallos[] = 'El esfuerzo percibido (RPE) debe ser un entero entre ' . ESFUERZO_MIN . ' y ' . ESFUERZO_MAX . '.';
        }

        // Sensaciones: opcionales, con un maximo de 200 caracteres
        if (mb_strlen($input['notes']) > LIMITE_SENSACIONES) {
            $fallos[] = 'Las sensaciones no pueden pasar de ' . LIMITE_SENSACIONES . ' caracteres.';
        }

        // Lesion: si marca la casilla tiene que explicar que le pasa
        if ($input['injury_checked'] && $input['injury'] === '') {
            $fallos[] = 'Has marcado lesión o molestia, tienes que describirla.';
        }

        return $fallos;
    }

    // Carga de la sesion: minutos multiplicados por el esfuerzo percibido
    function calculateLoad(int $duration, int $rpe): int
    {
        return $duration * $rpe;
    }
    // Calcula las estadisticas a partir del array de entrenamientos y las devuelve
    // juntas en un array: total de sesiones, minutos, RPE medio y carga total.
    function resumenEntrenamientos(array $sesiones): array
    {
        $cuantas = count($sesiones);
        $minutosSumados = array_sum(array_column($sesiones, 'duration'));

        $cargaSumada = 0;
        foreach ($sesiones as $s) {
            $cargaSumada += calculateLoad($s['duration'], $s['rpe']);
        }

        // Si todavia no hay sesiones no se puede dividir, y la media es 0
        $media = $cuantas > 0
            ? array_sum(array_column($sesiones, 'rpe')) / $cuantas
            : 0;

        return [
            'total' => $cuantas,
            'minutos' => $minutosSumados,
            'esfuerzo_medio' => round($media, 1),
            'carga' => $cargaSumada,
        ];
    }

        // ---- PASO 7: csv ----
    // readTrainings y escribir_csv las cogi de data/ejemploinicial.php y las adapte.

    // Si el fichero no existe lo creo con la cabecera
    function crearFicheroSiNoExiste(string $file): void
    {
        if (!file_exists($file)) {
            $h = @fopen($file, 'w'); // la @ es para que no salga el warning de PHP
            if ($h !== false) {
                fputcsv($h, ['username', 'date', 'type', 'duration', 'rpe', 'notes', 'injury']);
                fclose($h);
            }
        }
    }

    // Lee el csv y devuelve solo las filas del usuario, cada una como array asociativo
    function readTrainings(string $file, string $username): array
    {
        $result = [];

        crearFicheroSiNoExiste($file);
        $h = @fopen($file, 'r');

        if ($h === false) {
            // en vez de echo y exit lanzo una excepcion y la pagina enseña el mensaje
            throw new Exception('No se ha podido abrir el fichero de entrenamientos.');
        }

        flock($h, LOCK_SH);

        $cabeceras = fgetcsv($h); // la primera linea es la cabecera, asi que la salto
        while (($fila = fgetcsv($h)) !== false) {
            // si la fila esta rota o en blanco la salto, si no array_combine da error
            if (count($fila) !== count($cabeceras)) {
                continue;
            }

            $training = array_combine($cabeceras, $fila);

            if ($training['username'] === $username) {
                // del csv todo llega como texto, lo paso a int para calculateLoad
                $training['duration'] = (int) $training['duration'];
                $training['rpe'] = (int) $training['rpe'];
                $result[] = $training;
            }
        }

        flock($h, LOCK_UN);
        fclose($h);

        return $result;
    }

    // Añade una fila al final del csv. Devuelve false si no se ha podido escribir
    function escribir_csv(string $file, array $registro): bool
    {
        crearFicheroSiNoExiste($file);
        $h = @fopen($file, 'a');

        if ($h === false) {
            return false;
        }

        flock($h, LOCK_EX); // cojo la llave
        fputcsv($h, $registro);
        flock($h, LOCK_UN); // devuelvo la llave

        fclose($h);

        return true;
    }
?>