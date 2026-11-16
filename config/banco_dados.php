<?php

/**
 * Abre a conexão MySQL da aplicação com variáveis de ambiente.
 * Este projeto utiliza MySQLi procedural intencionalmente.
 */
function conectar_banco_dados()
{
    $servidor = getenv('DB_HOST') ?: 'db';
    $porta = (int) (getenv('DB_PORT') ?: 3306);
    $nomeBanco = getenv('DB_NAME') ?: '';
    $usuarioBanco = getenv('DB_USER') ?: '';
    $senhaBanco = getenv('DB_PASSWORD') ?: '';

    mysqli_report(MYSQLI_REPORT_OFF);
    $conexao = mysqli_connect($servidor, $usuarioBanco, $senhaBanco, $nomeBanco, $porta);

    if ($conexao === false) {
        return false;
    }

    mysqli_set_charset($conexao, 'utf8mb4');

    return $conexao;
}
