<?php
require __DIR__ . "/../controller/pedido_controller.php";
require_once __DIR__ . "/../model/usuario.php";
require_once __DIR__ . "/../model/cliente.php";
require_once __DIR__ . "/../model/produto.php";

function afirmar_pedidos($condicao, $mensagem, $conexao)
{
    if (!$condicao) {
        mysqli_rollback($conexao);
        mysqli_close($conexao);
        fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
        exit(1);
    }
    echo "OK: {$mensagem}" . PHP_EOL;
}
function inserir_empresa_teste($conexao, $sufixo, $letra)
{
    $base = str_pad((string) random_int(0, 999999999999), 12, "0", STR_PAD_LEFT);
    $pesosUm = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $pesosDois = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    foreach ($pesosUm as $indice => $peso) {
        $soma += (int) $base[$indice] * $peso;
    }
    $digitoUm = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    $soma = 0;
    foreach ($pesosDois as $indice => $peso) {
        $soma += (int) ($base . $digitoUm)[$indice] * $peso;
    }
    $cnpj = $base . $digitoUm . ($soma % 11 < 2 ? 0 : 11 - ($soma % 11));
    $comando = mysqli_prepare(
        $conexao,
        "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES (?, ?, ?, ?, ?)",
    );
    $nome = "Empresa {$letra} {$sufixo}";
    $email = strtolower($letra) . ".{$sufixo}@workhub.test";
    $telefone = "11999999999";
    mysqli_stmt_bind_param($comando, "sssss", $nome, $nome, $cnpj, $email, $telefone);
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}
$conexao = conectar_banco_dados();
if ($conexao === false || !mysqli_begin_transaction($conexao)) {
    exit(1);
}
iniciar_sessao_segura();
$_SESSION["tokens_csrf"]["cadastro_pedido"] = "token-correto";
$sufixo = bin2hex(random_bytes(5));
$empresaA = inserir_empresa_teste($conexao, $sufixo, "A");
$empresaB = inserir_empresa_teste($conexao, $sufixo, "B");
$senha = password_hash("Senha123!", PASSWORD_BCRYPT);
$usuarios = [];
foreach (["PROPRIETARIO", "GERENTE", "FUNCIONARIO"] as $perfil) {
    afirmar_pedidos(
        cadastrar_usuario(
            $conexao,
            $empresaA,
            $perfil,
            strtolower($perfil) . ".{$sufixo}@workhub.test",
            $senha,
            $perfil,
        ),
        "{$perfil} pode registrar pedidos",
        $conexao,
    );
    $usuarios[$perfil] = mysqli_insert_id($conexao);
}
afirmar_pedidos(
    cadastrar_cliente($conexao, $empresaA, "Cliente A", "11999999999", null),
    "cliente de teste criado",
    $conexao,
);
$clienteA = mysqli_insert_id($conexao);
afirmar_pedidos(
    cadastrar_cliente($conexao, $empresaB, "Cliente B", "11999999998", null),
    "cliente de outra empresa criado",
    $conexao,
);
$clienteB = mysqli_insert_id($conexao);
afirmar_pedidos(cadastrar_produto($conexao, $empresaA, "Produto A", "", "10.25"), "produto A criado", $conexao);
$produtoA = mysqli_insert_id($conexao);
afirmar_pedidos(cadastrar_produto($conexao, $empresaA, "Produto B", "", "2.50"), "produto B criado", $conexao);
$produtoB = mysqli_insert_id($conexao);
afirmar_pedidos(cadastrar_produto($conexao, $empresaB, "Produto C", "", "5.00"), "produto C criado", $conexao);
$produtoBEmpresa = mysqli_insert_id($conexao);

foreach ($usuarios as $perfil => $idUsuario) {
    $resultado = cadastrar_pedido(
        $conexao,
        $empresaA,
        $idUsuario,
        $clienteA,
        "2026-12-20",
        [["produto_id" => $produtoA, "quantidade" => 1]],
        false,
    );
    afirmar_pedidos($resultado["sucesso"], "{$perfil} cadastra pedido", $conexao);
}
$resultado = cadastrar_pedido(
    $conexao,
    $empresaA,
    $usuarios["FUNCIONARIO"],
    $clienteA,
    "2026-12-21",
    [["produto_id" => $produtoA, "quantidade" => 2], ["produto_id" => $produtoB, "quantidade" => 3]],
    false,
);
afirmar_pedidos($resultado["sucesso"], "pedido com múltiplos itens é cadastrado", $conexao);
$idPedido = $resultado["id_pedido"];
$pedido = buscar_pedido_por_id($conexao, $idPedido, $empresaA);
$itens = listar_itens_pedido_empresa($conexao, $idPedido, $empresaA);
afirmar_pedidos(
    $pedido["status"] === "RECEBIDO" &&
        $pedido["valor_total"] === "28.00" &&
        count($itens) === 2 &&
        $itens[0]["subtotal"] === "20.50" &&
        $itens[1]["subtotal"] === "7.50",
    "status, subtotais e total são calculados em centavos",
    $conexao,
);
afirmar_pedidos(
    atualizar_produto_empresa($conexao, $produtoA, $empresaA, "Produto A", "", "99.99") &&
        listar_itens_pedido_empresa($conexao, $idPedido, $empresaA)[0]["preco_unitario"] === "10.25",
    "preço registrado permanece preservado após alteração do produto",
    $conexao,
);
afirmar_pedidos(
    validar_itens_pedido([], [])[1] !== [] &&
        validar_itens_pedido([$produtoA], [0])[1] !== [] &&
        validar_itens_pedido([$produtoA, $produtoA], [1, 1])[1] !== [],
    "pedido sem itens, quantidade inválida e item duplicado são rejeitados",
    $conexao,
);
afirmar_pedidos(
    !cadastrar_pedido(
        $conexao,
        $empresaA,
        $usuarios["GERENTE"],
        $clienteB,
        "2026-12-22",
        [["produto_id" => $produtoA, "quantidade" => 1]],
        false,
    )["sucesso"],
    "cliente de outra empresa é rejeitado",
    $conexao,
);
afirmar_pedidos(
    !cadastrar_pedido(
        $conexao,
        $empresaA,
        $usuarios["GERENTE"],
        $clienteA,
        "2026-12-22",
        [["produto_id" => $produtoBEmpresa, "quantidade" => 1]],
        false,
    )["sucesso"],
    "produto de outra empresa é rejeitado",
    $conexao,
);
alterar_situacao_cliente_empresa($conexao, $clienteA, $empresaA, 0);
afirmar_pedidos(
    !cadastrar_pedido(
        $conexao,
        $empresaA,
        $usuarios["GERENTE"],
        $clienteA,
        "2026-12-22",
        [["produto_id" => $produtoA, "quantidade" => 1]],
        false,
    )["sucesso"],
    "cliente inativo é rejeitado",
    $conexao,
);
alterar_situacao_cliente_empresa($conexao, $clienteA, $empresaA, 1);
alterar_situacao_produto_empresa($conexao, $produtoB, $empresaA, 0);
afirmar_pedidos(
    !cadastrar_pedido(
        $conexao,
        $empresaA,
        $usuarios["GERENTE"],
        $clienteA,
        "2026-12-22",
        [["produto_id" => $produtoB, "quantidade" => 1]],
        false,
    )["sucesso"],
    "produto inativo é rejeitado",
    $conexao,
);
alterar_situacao_produto_empresa($conexao, $produtoB, $empresaA, 1);
$antes = count(
    listar_pedidos_empresa($conexao, $empresaA, [
        "id" => null,
        "status" => "",
        "data_inicial" => "",
        "data_final" => "",
    ]),
);
$falha = cadastrar_pedido(
    $conexao,
    $empresaA,
    $usuarios["GERENTE"],
    $clienteA,
    "2026-12-22",
    [["produto_id" => $produtoA, "quantidade" => 0]],
    false,
);
$depois = count(
    listar_pedidos_empresa($conexao, $empresaA, [
        "id" => null,
        "status" => "",
        "data_inicial" => "",
        "data_final" => "",
    ]),
);
afirmar_pedidos(!$falha["sucesso"] && $antes === $depois, "falha no item desfaz toda a transação", $conexao);
$filtrados = listar_pedidos_empresa($conexao, $empresaA, [
    "id" => $idPedido,
    "status" => "RECEBIDO",
    "data_inicial" => "2026-01-01",
    "data_final" => "2026-12-31",
]);
afirmar_pedidos(
    count($filtrados) === 1 && buscar_pedido_por_id($conexao, $idPedido, $empresaB) === false,
    "busca, filtros, detalhes e isolamento multiempresa funcionam",
    $conexao,
);
afirmar_pedidos(!validar_token_csrf("cadastro_pedido", "token-invalido"), "CSRF inválido é rejeitado", $conexao);
$edicao = atualizar_pedido(
    $conexao,
    $idPedido,
    $empresaA,
    $clienteA,
    "2026-12-30",
    [["produto_id" => $produtoA, "quantidade" => 1], ["produto_id" => $produtoB, "quantidade" => 4]],
);
$itensEditados = listar_itens_pedido_empresa($conexao, $idPedido, $empresaA);
$pedidoEditado = buscar_pedido_por_id($conexao, $idPedido, $empresaA);
afirmar_pedidos(
    $edicao["sucesso"] && $pedidoEditado["valor_total"] === "20.25" && $itensEditados[0]["preco_unitario"] === "10.25",
    "edição recalcula total e preserva preço histórico",
    $conexao,
);
$status = alterar_status_pedido($conexao, $idPedido, $empresaA, $usuarios["FUNCIONARIO"], "PRONTO");
$historico = listar_historico_pedido_empresa($conexao, $idPedido, $empresaA);
afirmar_pedidos(
    $status["sucesso"] && count($historico) === 1 && $historico[0]["status_novo"] === "PRONTO",
    "avanço com salto registra histórico atômico",
    $conexao,
);
afirmar_pedidos(
    !alterar_status_pedido($conexao, $idPedido, $empresaA, $usuarios["FUNCIONARIO"], "RECEBIDO")["sucesso"],
    "retrocesso de status é rejeitado",
    $conexao,
);
$cancelamento = alterar_status_pedido($conexao, $idPedido, $empresaA, $usuarios["GERENTE"], "CANCELADO", true);
afirmar_pedidos(
    $cancelamento["sucesso"] && !alterar_status_pedido($conexao, $idPedido, $empresaA, $usuarios["GERENTE"], "ENTREGUE")["sucesso"],
    "cancelamento bloqueia novas transições",
    $conexao,
);
afirmar_pedidos(
    indicador_prazo_pedido(["status" => "RECEBIDO", "data_entrega" => date("Y-m-d", strtotime("+2 days"))]) === "proximo" &&
        indicador_prazo_pedido(["status" => "RECEBIDO", "data_entrega" => date("Y-m-d", strtotime("-1 day"))]) === "atrasado" &&
        indicador_prazo_pedido(["status" => "ENTREGUE", "data_entrega" => date("Y-m-d", strtotime("-1 day"))]) === "",
    "alertas de prazo respeitam status e intervalo",
    $conexao,
);
mysqli_rollback($conexao);
mysqli_close($conexao);
echo "OK: testes de pedidos concluídos sem manter dados fictícios." . PHP_EOL;
