<?php

require __DIR__ . "/bootstrap_administrador_inicial.php";

function afirmar_bootstrap_administrador($condicao, $mensagem, $conexao)
{
    if (!$condicao) {
        mysqli_rollback($conexao);
        mysqli_close($conexao);
        fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
        exit(1);
    }

    echo "OK: {$mensagem}" . PHP_EOL;
}

function configurar_variavel_bootstrap($nome, $valor)
{
    $anterior = getenv($nome);
    putenv($nome . "=" . $valor);

    return $anterior;
}

function restaurar_variavel_bootstrap($nome, $valorAnterior)
{
    if ($valorAnterior === false) {
        putenv($nome);
        return;
    }

    putenv($nome . "=" . $valorAnterior);
}

$conexao = conectar_banco_dados();

if ($conexao === false || !mysqli_begin_transaction($conexao)) {
    exit(1);
}

$sufixo = bin2hex(random_bytes(8));
$senhaTeste = bin2hex(random_bytes(12));
$variaveisAnteriores = [
    "APP_ENV" => configurar_variavel_bootstrap("APP_ENV", "local"),
    "ADMIN_BOOTSTRAP_HABILITADO" => configurar_variavel_bootstrap("ADMIN_BOOTSTRAP_HABILITADO", "true"),
    "ADMIN_INICIAL_NOME" => configurar_variavel_bootstrap("ADMIN_INICIAL_NOME", "Administrador de teste"),
    "ADMIN_INICIAL_EMAIL" => configurar_variavel_bootstrap("ADMIN_INICIAL_EMAIL", "admin.{$sufixo}@workhub.test"),
    "ADMIN_INICIAL_SENHA" => configurar_variavel_bootstrap("ADMIN_INICIAL_SENHA", $senhaTeste),
];

mysqli_query($conexao, "DELETE FROM administradores_plataforma");
$primeiroResultado = executar_bootstrap_administrador_inicial($conexao);
$administrador = buscar_administrador_por_email($conexao, getenv("ADMIN_INICIAL_EMAIL"));

afirmar_bootstrap_administrador(
    $primeiroResultado["sucesso"] &&
        $primeiroResultado["motivo"] === "criado" &&
        $administrador !== false &&
        password_verify($senhaTeste, $administrador["senha"]),
    "bootstrap local cria o primeiro administrador com bcrypt",
    $conexao,
);

$hashInicial = $administrador["senha"];
$segundoResultado = executar_bootstrap_administrador_inicial($conexao);
$administradorAposReinicio = buscar_administrador_por_email($conexao, getenv("ADMIN_INICIAL_EMAIL"));

afirmar_bootstrap_administrador(
    $segundoResultado["sucesso"] &&
        $segundoResultado["motivo"] === "administrador_existente" &&
        contar_administradores_plataforma($conexao) === 1 &&
        $administradorAposReinicio["senha"] === $hashInicial,
    "bootstrap é idempotente e não redefine a senha",
    $conexao,
);

mysqli_query($conexao, "DELETE FROM administradores_plataforma");
putenv("APP_ENV=production");
$resultadoProducao = executar_bootstrap_administrador_inicial($conexao);

afirmar_bootstrap_administrador(
    $resultadoProducao["sucesso"] &&
        $resultadoProducao["motivo"] === "desabilitado" &&
        contar_administradores_plataforma($conexao) === 0,
    "bootstrap permanece desabilitado em produção",
    $conexao,
);

mysqli_rollback($conexao);
mysqli_close($conexao);

foreach ($variaveisAnteriores as $nome => $valorAnterior) {
    restaurar_variavel_bootstrap($nome, $valorAnterior);
}

echo "OK: testes de bootstrap administrativo concluídos sem manter dados fictícios." . PHP_EOL;
