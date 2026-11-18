<?php

require __DIR__ . "/../controller/cliente_controller.php";
require_once __DIR__ . "/../controller/pedido_controller.php";
require_once __DIR__ . "/../model/usuario.php";
require_once __DIR__ . "/../model/produto.php";

function afirmar_detalhes_cliente($condicao, $mensagem, $conexao)
{
    if (!$condicao) {
        mysqli_rollback($conexao);
        mysqli_close($conexao);
        fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
        exit(1);
    }

    echo "OK: {$mensagem}" . PHP_EOL;
}

function inserir_empresa_detalhes_cliente($conexao, $sufixo, $prefixo)
{
    $cnpj = str_pad((string) random_int(1, 99999999999999), 14, "0", STR_PAD_LEFT);
    $comando = mysqli_prepare(
        $conexao,
        "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone)
         VALUES (?, ?, ?, ?, '11999999999')",
    );
    $nome = "Detalhes {$prefixo} {$sufixo}";
    $email = strtolower($prefixo) . ".{$sufixo}@workhub.test";
    mysqli_stmt_bind_param($comando, "ssss", $nome, $nome, $cnpj, $email);
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}

$conexao = conectar_banco_dados();
if ($conexao === false || !mysqli_begin_transaction($conexao)) {
    exit(1);
}

$sufixo = bin2hex(random_bytes(5));
$empresaA = inserir_empresa_detalhes_cliente($conexao, $sufixo, "A");
$empresaB = inserir_empresa_detalhes_cliente($conexao, $sufixo, "B");
$senha = password_hash("Senha123!", PASSWORD_BCRYPT);
cadastrar_usuario($conexao, $empresaA, "Responsável", "d.{$sufixo}@workhub.test", $senha, "PROPRIETARIO");
$usuarioA = mysqli_insert_id($conexao);
cadastrar_cliente($conexao, $empresaA, "Sem pedidos", "11999999999", null);
$clienteSemPedidos = mysqli_insert_id($conexao);
cadastrar_cliente($conexao, $empresaA, "Com pedidos", "11999999998", null);
$clienteComPedidos = mysqli_insert_id($conexao);
cadastrar_cliente($conexao, $empresaB, "Cliente externo", "11999999997", null);
$clienteB = mysqli_insert_id($conexao);
cadastrar_produto($conexao, $empresaA, "Produto de teste", null, "10.50");
$produtoA = mysqli_insert_id($conexao);

afirmar_detalhes_cliente(
    listar_pedidos_cliente_empresa($conexao, $clienteSemPedidos, $empresaA) === [],
    "cliente sem pedidos retorna histórico vazio",
    $conexao,
);

$primeiro = cadastrar_pedido(
    $conexao,
    $empresaA,
    $usuarioA,
    $clienteComPedidos,
    "2026-12-20",
    [["produto_id" => $produtoA, "quantidade" => 1]],
    false,
);
$segundo = cadastrar_pedido(
    $conexao,
    $empresaA,
    $usuarioA,
    $clienteComPedidos,
    "2026-12-21",
    [["produto_id" => $produtoA, "quantidade" => 2]],
    false,
);
afirmar_detalhes_cliente(
    $primeiro["sucesso"] && $segundo["sucesso"],
    "pedidos de teste são cadastrados",
    $conexao,
);
$comandoStatus = mysqli_prepare($conexao, "UPDATE pedidos SET status = ? WHERE id = ? AND empresa_id = ?");
$status = "EM_PRODUCAO";
$idPedido = (int) $primeiro["id_pedido"];
mysqli_stmt_bind_param($comandoStatus, "sii", $status, $idPedido, $empresaA);
mysqli_stmt_execute($comandoStatus);
$status = "PRONTO";
$idPedido = (int) $segundo["id_pedido"];
mysqli_stmt_bind_param($comandoStatus, "sii", $status, $idPedido, $empresaA);
mysqli_stmt_execute($comandoStatus);
mysqli_stmt_close($comandoStatus);
$historico = listar_pedidos_cliente_empresa($conexao, $clienteComPedidos, $empresaA);
afirmar_detalhes_cliente(
    count($historico) === 2 && $historico[0]["status"] !== "" && $historico[1]["status"] !== "",
    "histórico retorna vários pedidos com status",
    $conexao,
);
afirmar_detalhes_cliente(
    formatar_valor_pedido("0.00") === "0,00" &&
        formatar_valor_pedido("10.50") === "10,50" &&
        formatar_valor_pedido("1234.56") === "1.234,56",
    "valores monetários usam o padrão brasileiro",
    $conexao,
);
afirmar_detalhes_cliente(
    buscar_cliente_empresa($conexao, $clienteB, $empresaA) === false &&
        listar_pedidos_cliente_empresa($conexao, $clienteB, $empresaA) === [],
    "cliente de outra empresa não é encontrado nem expõe histórico",
    $conexao,
);
afirmar_detalhes_cliente(
    buscar_cliente_empresa($conexao, 999999999, $empresaA) === false,
    "cliente inexistente não é encontrado",
    $conexao,
);

mysqli_rollback($conexao);
mysqli_close($conexao);
echo "OK: testes de detalhes de cliente concluídos sem manter dados fictícios." . PHP_EOL;
