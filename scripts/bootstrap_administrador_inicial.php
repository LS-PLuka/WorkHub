<?php

require_once __DIR__ . "/../config/banco_dados.php";
require_once __DIR__ . "/../model/administracao.php";

function bootstrap_administrador_habilitado()
{
    $ambiente = strtolower(trim((string) getenv("APP_ENV")));
    $habilitado = strtolower(trim((string) getenv("ADMIN_BOOTSTRAP_HABILITADO")));

    return in_array($ambiente, ["local", "development", "desenvolvimento", "demo"], true) &&
        in_array($habilitado, ["1", "true"], true);
}

function executar_bootstrap_administrador_inicial($conexaoExistente = null)
{
    if (!bootstrap_administrador_habilitado()) {
        return ["sucesso" => true, "motivo" => "desabilitado"];
    }

    $email = strtolower(trim((string) getenv("ADMIN_INICIAL_EMAIL")));
    $senha = (string) getenv("ADMIN_INICIAL_SENHA");
    $nome = trim((string) getenv("ADMIN_INICIAL_NOME"));

    if ($nome === "") {
        $nome = "Administrador local";
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($senha) < 8) {
        return ["sucesso" => false, "motivo" => "configuracao_invalida"];
    }

    $conexao = $conexaoExistente ?: conectar_banco_dados();
    $deveFecharConexao = $conexaoExistente === null;

    if ($conexao === false) {
        return ["sucesso" => false, "motivo" => "conexao_indisponivel"];
    }

    $totalAdministradores = contar_administradores_plataforma($conexao);

    if ($totalAdministradores === false) {
        if ($deveFecharConexao) {
            mysqli_close($conexao);
        }

        return ["sucesso" => false, "motivo" => "consulta_indisponivel"];
    }

    if ($totalAdministradores > 0) {
        if ($deveFecharConexao) {
            mysqli_close($conexao);
        }

        return ["sucesso" => true, "motivo" => "administrador_existente"];
    }

    $hashSenha = password_hash($senha, PASSWORD_BCRYPT);
    $sucesso = $hashSenha !== false && cadastrar_administrador_plataforma($conexao, $nome, $email, $hashSenha);

    if ($deveFecharConexao) {
        mysqli_close($conexao);
    }

    return ["sucesso" => $sucesso, "motivo" => $sucesso ? "criado" : "nao_criado"];
}

if (PHP_SAPI !== "cli" || realpath($_SERVER["SCRIPT_FILENAME"] ?? "") !== __FILE__) {
    return;
}

$resultado = executar_bootstrap_administrador_inicial();

if (!$resultado["sucesso"]) {
    fwrite(STDERR, "Não foi possível preparar o administrador inicial local." . PHP_EOL);
    exit(1);
}

if ($resultado["motivo"] === "criado") {
    echo "Administrador inicial local preparado." . PHP_EOL;
} elseif ($resultado["motivo"] === "administrador_existente") {
    echo "Administrador inicial não criado: já existe administrador da plataforma." . PHP_EOL;
} else {
    echo "Bootstrap local de administrador desabilitado para este ambiente." . PHP_EOL;
}
