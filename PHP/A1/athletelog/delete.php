<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

requireLogin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido. Usa POST.');
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !verifyCsrfToken($token)) {
    http_response_code(403);
    exit('Token CSRF no válido o ausente (403 Forbidden).');
}

$username = $_SESSION['user']['username'] ?? '';
$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;

if ($username === '' || $id === false || $id < 0) {
    http_response_code(400);
    exit('ID o usuario no válido.');
}

$csvFile = __DIR__ . '/data/trainings.csv';

if (is_file($csvFile) && is_readable($csvFile)) {
    $fp = fopen($csvFile, 'c+');
    if ($fp !== false && flock($fp, LOCK_EX)) {
        $allRows = [];
        $header = fgetcsv($fp);
        if ($header !== false) {
            while (($row = fgetcsv($fp)) !== false) {
                if ($row === [null] || $row === false) {
                    continue;
                }
                $allRows[] = $row;
            }
        }

        $userMatchIndex = 0;
        $deleted = false;
        $newRows = [];

        foreach ($allRows as $row) {
            $rowUser = $row[0] ?? '';
            if ($rowUser === $username) {
                if ($userMatchIndex === $id && !$deleted) {
                    $deleted = true;
                    $userMatchIndex++;
                    continue;
                }
                $userMatchIndex++;
            }
            $newRows[] = $row;
        }

        if ($deleted && $header !== false) {
            rewind($fp);
            ftruncate($fp, 0);
            fputcsv($fp, $header);
            foreach ($newRows as $r) {
                fputcsv($fp, $r);
            }
        }

        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

header('Location: history.php');
exit;