<?php 
declare(strict_types=1);

$users = [
    '@MarcosGundin' => [
        'name' => 'Marcos',
        'password' => '123456789',
        'sport' => 'karate'
    ],

    '@DanielFiguereo' => [
        'name' => 'Daniel',
        'password' => '987654321',
        'sport' => 'football'
    ],

    '@AlexVazquez' => [
        'name' => 'Alex',
        'password' => '567891234',
        'sport' => 'swimming'
    ],
];

$fp = fopen('data/users.csv', 'w');

if (flock($fp, LOCK_EX)){
    fputcsv($fp, ['useraname', 'name', 'password_hash', 'sport'], ',', '"', '');

    foreach ($users as $username => $user) {
        fputcsv($fp, [
            $username,
            $user['name'],
            password_hash($user['password'], PASSWORD_DEFAULT),
            $user['sport']
        ], ',', '"', '',);
    }
    flock($fp, LOCK_UN);
}
fclose($fp);

?>