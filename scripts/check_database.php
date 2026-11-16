<?php

require __DIR__ . '/../config/database.php';

$connection = database_connect();

if ($connection === false) {
    fwrite(STDERR, "Falha ao conectar ao MySQL: " . mysqli_connect_error() . PHP_EOL);
    exit(1);
}

$result = mysqli_query($connection, 'SELECT 1 AS connection_ok');

if ($result === false) {
    fwrite(STDERR, "A conexão foi aberta, mas a consulta de verificação falhou: " . mysqli_error($connection) . PHP_EOL);
    mysqli_close($connection);
    exit(1);
}

$row = mysqli_fetch_assoc($result);
mysqli_free_result($result);
mysqli_close($connection);

if ((int) $row['connection_ok'] !== 1) {
    fwrite(STDERR, "Resposta inválida na verificação do MySQL." . PHP_EOL);
    exit(1);
}

echo "Conexão com o MySQL verificada com sucesso." . PHP_EOL;
