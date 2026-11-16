<?php

require __DIR__ . '/../config/banco_dados.php';

$conexao = conectar_banco_dados();
if ($conexao === false) {
    fwrite(STDERR, 'Falha ao conectar ao MySQL: ' . mysqli_connect_error() . PHP_EOL);
    exit(1);
}

$resultado = mysqli_query($conexao, 'SELECT 1 AS conexao_ok');
if ($resultado === false) {
    fwrite(STDERR, 'A conexão foi aberta, mas a consulta de verificação falhou: ' . mysqli_error($conexao) . PHP_EOL);
    mysqli_close($conexao);
    exit(1);
}

$linha = mysqli_fetch_assoc($resultado);
mysqli_free_result($resultado);
mysqli_close($conexao);

if ((int) $linha['conexao_ok'] !== 1) {
    fwrite(STDERR, 'Resposta inválida na verificação do MySQL.' . PHP_EOL);
    exit(1);
}

echo 'Conexão com o MySQL verificada com sucesso.' . PHP_EOL;
