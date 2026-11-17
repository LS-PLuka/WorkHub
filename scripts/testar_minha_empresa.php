<?php

require __DIR__ . "/../controller/empresa_controller.php";

function afirmar_minha_empresa($condicao, $mensagem, $conexao)
{
    if (!$condicao) {
        mysqli_rollback($conexao);
        mysqli_close($conexao);
        fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
        exit(1);
    }

    echo "OK: {$mensagem}" . PHP_EOL;
}

function gerar_cnpj_minha_empresa()
{
    $base = str_pad((string) random_int(0, 999999999999), 12, "0", STR_PAD_LEFT);
    $pesosPrimeiroDigito = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $pesosSegundoDigito = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;

    foreach ($pesosPrimeiroDigito as $indice => $peso) {
        $soma += (int) $base[$indice] * $peso;
    }
    $primeiroDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    $soma = 0;

    foreach ($pesosSegundoDigito as $indice => $peso) {
        $soma += (int) ($base . $primeiroDigito)[$indice] * $peso;
    }
    $segundoDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);

    return $base . $primeiroDigito . $segundoDigito;
}

$conexao = conectar_banco_dados();
if ($conexao === false || mysqli_begin_transaction($conexao) === false) {
    exit(1);
}

iniciar_sessao_segura();
$sufixo = bin2hex(random_bytes(5));
$cnpjEmpresaA = gerar_cnpj_minha_empresa();
$cnpjEmpresaB = gerar_cnpj_minha_empresa();
mysqli_query(
    $conexao,
    "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone)
     VALUES ('Empresa A', 'Empresa A LTDA', '{$cnpjEmpresaA}', 'a.{$sufixo}@workhub.test', '11999999999')",
);
$idEmpresaA = mysqli_insert_id($conexao);
mysqli_query(
    $conexao,
    "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone)
     VALUES ('Empresa B', 'Empresa B LTDA', '{$cnpjEmpresaB}', 'b.{$sufixo}@workhub.test', '11999999998')",
);
$idEmpresaB = mysqli_insert_id($conexao);

afirmar_minha_empresa(
    verificar_permissao(["PROPRIETARIO"], ["perfil" => "PROPRIETARIO"]),
    "Proprietário possui acesso à página Minha Empresa",
    $conexao,
);
afirmar_minha_empresa(
    !verificar_permissao(["PROPRIETARIO"], ["perfil" => "GERENTE"]) &&
        !verificar_permissao(["PROPRIETARIO"], ["perfil" => "FUNCIONARIO"]),
    "Gerente e Funcionário não possuem acesso à edição cadastral",
    $conexao,
);
afirmar_minha_empresa(
    validar_dados_minha_empresa([
        "nome_fantasia" => "",
        "razao_social" => "",
        "email" => "invalido",
        "telefone" => "11999999998",
    ]) !== [],
    "nome, razão social, e-mail e telefone inválidos são rejeitados",
    $conexao,
);
afirmar_minha_empresa(
    validar_dados_minha_empresa([
        "nome_fantasia" => "Empresa atualizada",
        "razao_social" => "Empresa Atualizada LTDA",
        "email" => "contato@empresa.com.br",
        "telefone" => "(11) 99999-9999",
    ]) === [],
    "e-mail .com.br e telefone formatado são aceitos",
    $conexao,
);
afirmar_minha_empresa(
    atualizar_dados_cadastrais_empresa(
        $conexao,
        $idEmpresaA,
        "Empresa atualizada",
        "Empresa Atualizada LTDA",
        "contato@empresa.com.br",
        normalizar_telefone("(11) 99999-9999"),
    ),
    "dados da empresa autenticada são atualizados",
    $conexao,
);
$empresaA = buscar_empresa_por_id($conexao, $idEmpresaA);
$empresaB = buscar_empresa_por_id($conexao, $idEmpresaB);
afirmar_minha_empresa(
    $empresaA["nome_fantasia"] === "Empresa atualizada" &&
        $empresaA["telefone"] === "11999999999" &&
        $empresaA["cnpj"] === $cnpjEmpresaA,
    "telefone é normalizado e CNPJ permanece imutável",
    $conexao,
);
afirmar_minha_empresa(
    $empresaB["nome_fantasia"] === "Empresa B" && $empresaB["cnpj"] === $cnpjEmpresaB,
    "atualização não alcança outra empresa",
    $conexao,
);

$_SESSION["tokens_csrf"]["minha_empresa"] = "token-minha-empresa";
afirmar_minha_empresa(
    !validar_token_csrf("minha_empresa", "token-invalido") &&
        validar_token_csrf("minha_empresa", "token-minha-empresa"),
    "CSRF inválido é rejeitado e token válido é aceito",
    $conexao,
);

mysqli_rollback($conexao);
mysqli_close($conexao);
echo "OK: testes de Minha Empresa concluídos sem manter dados fictícios." . PHP_EOL;
