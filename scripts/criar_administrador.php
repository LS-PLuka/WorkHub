<?php

if (PHP_SAPI !== "cli") {
    http_response_code(403);
    echo "Este script só pode ser executado pelo terminal.";
    exit(1);
}

require __DIR__ . "/../config/banco_dados.php";
require __DIR__ . "/../model/administracao.php";

function solicitar_dado_administrador($rotulo)
{
    fwrite(STDOUT, $rotulo . ": ");
    $valor = fgets(STDIN);
    return $valor === false ? "" : trim($valor);
}

function solicitar_senha_administrador()
{
    fwrite(STDOUT, "Senha: ");
    $ocultouEntrada = DIRECTORY_SEPARATOR === "/" && function_exists("shell_exec");
    if ($ocultouEntrada) {
        shell_exec("stty -echo");
    }
    $senha = fgets(STDIN);
    if ($ocultouEntrada) {
        shell_exec("stty echo");
        fwrite(STDOUT, PHP_EOL);
    }
    return $senha === false ? "" : trim($senha);
}

$nome = solicitar_dado_administrador("Nome");
$email = strtolower(solicitar_dado_administrador("E-mail"));
$senha = solicitar_senha_administrador();

if ($nome === "" || filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($senha) < 8) {
    fwrite(STDERR, "Nome, e-mail válido e senha com ao menos 8 caracteres são obrigatórios." . PHP_EOL);
    exit(1);
}

$conexao = conectar_banco_dados();
if ($conexao === false) {
    fwrite(STDERR, "Não foi possível conectar ao banco de dados." . PHP_EOL);
    exit(1);
}

$hashSenha = password_hash($senha, PASSWORD_BCRYPT);
$sucesso = cadastrar_administrador_plataforma($conexao, $nome, $email, $hashSenha);
mysqli_close($conexao);

if (!$sucesso) {
    fwrite(STDERR, "Não foi possível criar o administrador. Verifique se o e-mail já está cadastrado." . PHP_EOL);
    exit(1);
}

echo "Administrador criado com sucesso." . PHP_EOL;
