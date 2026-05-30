<?php
/**
 * xbt.co.il - Database Configuration
 * Update these credentials for your environment.
 */

declare(strict_types=1);

$isDev = (defined('APP_ENV') ? APP_ENV : getenv('APP_ENV')) === 'development';

return [
    'driver'   => 'mysql',
    'host'     => ($v = getenv('DB_HOST')) !== false ? $v : '127.0.0.1',
    'port'     => ($v = getenv('DB_PORT')) !== false ? $v : '3306',
    'database' => ($v = getenv('DB_NAME')) !== false ? $v : ($isDev ? 'xbt_finance'     : 'usedtrac_finca1'),
    'username' => ($v = getenv('DB_USER')) !== false ? $v : ($isDev ? 'root'             : 'usedtrac_finca1'),
    'password' => ($v = getenv('DB_PASS')) !== false ? $v : ($isDev ? ''                 : '5W*nOAv*qC][}3j1'),
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ],
];
