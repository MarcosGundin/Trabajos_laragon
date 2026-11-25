<?php
$file_route = __DIR__ . '/trainings.csv';

//funcion leer los registros del fichero
function readTrainings(string $file, string $username): array
{
    $result = []; //aqui guardare las filas del csv
    $h = fopen($file, 'r');

    if ($h === false) {
        //gestiono error
        echo 'No se pudo abrir el fichero';
        exit;
    }
    //gestiono el operar con el fichero

    //Leer fichero
    flock($h, LOCK_SH);

    $cabeceras = fgetcsv($h);
    while (($fila = fgetcsv($h)) !== false) {
        $training = array_combine($cabeceras, $fila);

        if ($fila[0] == $username) {
            $result[] = $training;
        }
    }
    flock($h, LOCK_UN);
    fclose($h);
    
    return $result;
}
//funcion escribir un registro al fichero csv
function escribir_csv(string $file, array $registro): bool
{
    if (!crearFicheroSiNoExiste($file)) {
        return false;
    }

    //Escribir un nuevo registro
    $h = @fopen($file, 'a');

    if ($h === false) {
        //gestiono error: la pagina enseña el mensaje
        return false;
    }

    flock($h, LOCK_EX); //1. cojo la llave
    fputcsv($h, $registro); //escribe en el fichero
    fflush($h); //vacio el buffer antes de devolver la llave
    flock($h, LOCK_UN); //2. Devuelvo la llave

    fclose($h);

    return true;
}
?>