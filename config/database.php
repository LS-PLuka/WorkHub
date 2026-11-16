<?php

/**
 * Opens the application's MySQL connection using environment variables.
 * This project intentionally uses procedural MySQLi.
 */
function database_connect()
{
    $host = getenv('DB_HOST') ?: 'db';
    $port = (int) (getenv('DB_PORT') ?: 3306);
    $name = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $password = getenv('DB_PASSWORD') ?: '';

    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = mysqli_connect($host, $user, $password, $name, $port);

    if ($connection === false) {
        return false;
    }

    mysqli_set_charset($connection, 'utf8mb4');

    return $connection;
}
