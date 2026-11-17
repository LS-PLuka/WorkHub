<?php

function consultar_pedido($conexao, $sql, $tipos, &...$valores)
{
    $comando = mysqli_prepare($conexao, $sql);
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, $tipos, ...$valores);
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return false;
    }
    $resultado = mysqli_stmt_get_result($comando);
    $dados = $resultado === false ? false : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return $dados;
}

function listar_clientes_ativos_empresa($conexao, $idEmpresa)
{
    return consultar_pedido($conexao, "SELECT id, nome FROM clientes WHERE empresa_id = ? AND ativo = 1 ORDER BY nome", "i", $idEmpresa) ?: [];
}

function listar_produtos_ativos_empresa($conexao, $idEmpresa)
{
    return consultar_pedido($conexao, "SELECT id, nome, preco FROM produtos WHERE empresa_id = ? AND ativo = 1 ORDER BY nome", "i", $idEmpresa) ?: [];
}

function valor_decimal_para_centavos($valor)
{
    if (!is_string($valor) || !preg_match('/^(\d{1,10})\.(\d{2})$/', $valor, $partes)) {
        return false;
    }
    return (int) $partes[1] * 100 + (int) $partes[2];
}

function centavos_para_decimal($centavos)
{
    return intdiv($centavos, 100) . "." . str_pad((string) ($centavos % 100), 2, "0", STR_PAD_LEFT);
}

function buscar_linha_pedido_transacao($conexao, $idPedido, $idEmpresa)
{
    $dados = consultar_pedido(
        $conexao,
        "SELECT id, cliente_id, status, data_entrega, valor_total FROM pedidos WHERE id = ? AND empresa_id = ? AND ativo = 1 FOR UPDATE",
        "ii",
        $idPedido,
        $idEmpresa,
    );
    return $dados[0] ?? false;
}

function buscar_cliente_ativo_transacao($conexao, $idCliente, $idEmpresa)
{
    $dados = consultar_pedido($conexao, "SELECT id FROM clientes WHERE id = ? AND empresa_id = ? AND ativo = 1 FOR UPDATE", "ii", $idCliente, $idEmpresa);
    return $dados[0] ?? false;
}

function buscar_produto_transacao($conexao, $idProduto, $idEmpresa, $somenteAtivo = true)
{
    $sql = "SELECT id, nome, preco, ativo FROM produtos WHERE id = ? AND empresa_id = ?";
    if ($somenteAtivo) {
        $sql .= " AND ativo = 1";
    }
    $sql .= " FOR UPDATE";
    $dados = consultar_pedido($conexao, $sql, "ii", $idProduto, $idEmpresa);
    return $dados[0] ?? false;
}

function listar_itens_pedido_empresa($conexao, $idPedido, $idEmpresa, $incluirInativos = false)
{
    $sql = "SELECT itens_pedido.id, itens_pedido.produto_id, produtos.nome AS produto, produtos.ativo AS produto_ativo,
                   itens_pedido.quantidade, itens_pedido.preco_unitario, itens_pedido.subtotal, itens_pedido.ativo
            FROM itens_pedido INNER JOIN produtos ON produtos.id = itens_pedido.produto_id
                AND produtos.empresa_id = itens_pedido.empresa_id
            WHERE itens_pedido.pedido_id = ? AND itens_pedido.empresa_id = ?";
    if (!$incluirInativos) {
        $sql .= " AND itens_pedido.ativo = 1";
    }
    $sql .= " ORDER BY itens_pedido.id";
    return consultar_pedido($conexao, $sql, "ii", $idPedido, $idEmpresa) ?: [];
}

function calcular_item_pedido($produto, $quantidade, $precoHistorico = null)
{
    $preco = $precoHistorico ?? $produto["preco"];
    $precoCentavos = valor_decimal_para_centavos($preco);
    if ($precoCentavos === false || $quantidade > intdiv(999999999999, max(1, $precoCentavos))) {
        return false;
    }
    $subtotalCentavos = $quantidade * $precoCentavos;
    return ["produto_id" => (int) $produto["id"], "quantidade" => $quantidade,
        "preco" => centavos_para_decimal($precoCentavos), "subtotal" => centavos_para_decimal($subtotalCentavos),
        "centavos" => $subtotalCentavos];
}

function cadastrar_pedido($conexao, $idEmpresa, $idUsuario, $idCliente, $dataEntrega, $itens, $gerenciarTransacao = true)
{
    if ($gerenciarTransacao && !mysqli_begin_transaction($conexao)) {
        return ["sucesso" => false, "erro" => "Não foi possível iniciar o cadastro do pedido."];
    }
    try {
        if (buscar_cliente_ativo_transacao($conexao, $idCliente, $idEmpresa) === false) {
            throw new RuntimeException("O cliente informado não existe, está inativo ou não pertence à empresa.");
        }
        $calculados = [];
        $total = 0;
        foreach ($itens as $item) {
            if (!isset($item["quantidade"]) || $item["quantidade"] < 1) {
                throw new RuntimeException("A quantidade informada é inválida.");
            }
            $produto = buscar_produto_transacao($conexao, $item["produto_id"], $idEmpresa);
            $calculado = $produto === false ? false : calcular_item_pedido($produto, $item["quantidade"]);
            if ($calculado === false || $total > 999999999999 - $calculado["centavos"]) {
                throw new RuntimeException("Produto ou valor do pedido inválido.");
            }
            $total += $calculado["centavos"];
            $calculados[] = $calculado;
        }
        $valorTotal = centavos_para_decimal($total);
        $pedido = mysqli_prepare($conexao, "INSERT INTO pedidos (empresa_id, cliente_id, usuario_id, data_entrega, status, valor_total, ativo) VALUES (?, ?, ?, ?, 'RECEBIDO', ?, 1)");
        mysqli_stmt_bind_param($pedido, "iiiss", $idEmpresa, $idCliente, $idUsuario, $dataEntrega, $valorTotal);
        if ($pedido === false || !mysqli_stmt_execute($pedido)) {
            throw new RuntimeException("Não foi possível registrar o pedido.");
        }
        $idPedido = mysqli_insert_id($conexao);
        mysqli_stmt_close($pedido);
        $comando = mysqli_prepare($conexao, "INSERT INTO itens_pedido (pedido_id, produto_id, empresa_id, quantidade, preco_unitario, subtotal, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        foreach ($calculados as $item) {
            mysqli_stmt_bind_param($comando, "iiiiss", $idPedido, $item["produto_id"], $idEmpresa, $item["quantidade"], $item["preco"], $item["subtotal"]);
            if ($comando === false || !mysqli_stmt_execute($comando)) {
                throw new RuntimeException("Não foi possível registrar os itens do pedido.");
            }
        }
        mysqli_stmt_close($comando);
        if ($gerenciarTransacao && !mysqli_commit($conexao)) {
            throw new RuntimeException("Não foi possível concluir o cadastro do pedido.");
        }
        return ["sucesso" => true, "id_pedido" => $idPedido];
    } catch (Throwable $erro) {
        if ($gerenciarTransacao) {
            mysqli_rollback($conexao);
        }
        return ["sucesso" => false, "erro" => $erro->getMessage()];
    }
}

function listar_pedidos_empresa($conexao, $idEmpresa, $filtros)
{
    $sql = "SELECT pedidos.id, clientes.nome AS cliente, pedidos.data_criacao, pedidos.data_entrega,
                   pedidos.status, pedidos.valor_total
            FROM pedidos INNER JOIN clientes ON clientes.id = pedidos.cliente_id
                AND clientes.empresa_id = pedidos.empresa_id
            WHERE pedidos.empresa_id = ? AND pedidos.ativo = 1";
    $tipos = "i";
    $valores = [$idEmpresa];
    if (($filtros["id"] ?? null) !== null) {
        $sql .= " AND pedidos.id = ?";
        $tipos .= "i";
        $valores[] = $filtros["id"];
    }
    if (($filtros["status"] ?? "") !== "") {
        $sql .= " AND pedidos.status = ?";
        $tipos .= "s";
        $valores[] = $filtros["status"];
    }
    if (($filtros["data_inicial"] ?? "") !== "") {
        $sql .= " AND DATE(pedidos.data_criacao) >= ?";
        $tipos .= "s";
        $valores[] = $filtros["data_inicial"];
    }
    if (($filtros["data_final"] ?? "") !== "") {
        $sql .= " AND DATE(pedidos.data_criacao) <= ?";
        $tipos .= "s";
        $valores[] = $filtros["data_final"];
    }
    $sql .= " ORDER BY pedidos.data_criacao DESC, pedidos.id DESC";
    $comando = mysqli_prepare($conexao, $sql);
    if ($comando === false) {
        return [];
    }
    $referencias = [$comando, $tipos];
    foreach ($valores as $indice => $valor) {
        $referencias[] = &$valores[$indice];
    }
    call_user_func_array("mysqli_stmt_bind_param", $referencias);
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return [];
    }
    $resultado = mysqli_stmt_get_result($comando);
    $pedidos = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return $pedidos;
}

function buscar_pedido_por_id($conexao, $idPedido, $idEmpresa)
{
    $sql = "SELECT pedidos.id, pedidos.cliente_id, pedidos.data_criacao, pedidos.data_entrega, pedidos.status, pedidos.valor_total, clientes.nome AS cliente, clientes.telefone AS telefone_cliente, clientes.email AS email_cliente, usuarios.nome AS usuario FROM pedidos INNER JOIN clientes ON clientes.id = pedidos.cliente_id AND clientes.empresa_id = pedidos.empresa_id INNER JOIN usuarios ON usuarios.id = pedidos.usuario_id AND usuarios.empresa_id = pedidos.empresa_id WHERE pedidos.id = ? AND pedidos.empresa_id = ? AND pedidos.ativo = 1";
    $dados = consultar_pedido($conexao, $sql, "ii", $idPedido, $idEmpresa);
    return $dados[0] ?? false;
}

function atualizar_pedido($conexao, $idPedido, $idEmpresa, $idCliente, $dataEntrega, $itens)
{
    if (!mysqli_begin_transaction($conexao)) {
        return ["sucesso" => false, "erro" => "Não foi possível iniciar a edição."];
    }
    try {
        $pedido = buscar_linha_pedido_transacao($conexao, $idPedido, $idEmpresa);
        if ($pedido === false || !in_array($pedido["status"], ["RECEBIDO", "EM_PRODUCAO"], true)) {
            throw new RuntimeException("Este pedido não pode ser editado.");
        }
        if (buscar_cliente_ativo_transacao($conexao, $idCliente, $idEmpresa) === false) {
            throw new RuntimeException("Cliente inválido.");
        }
        $existentes = [];
        foreach (listar_itens_pedido_empresa($conexao, $idPedido, $idEmpresa, true) as $item) {
            if ((int) $item["ativo"] === 1) {
                $existentes[(int) $item["produto_id"]] = $item;
            }
        }
        $calculados = [];
        $total = 0;
        foreach ($itens as $item) {
            if (!isset($item["quantidade"]) || $item["quantidade"] < 1) {
                throw new RuntimeException("A quantidade informada é inválida.");
            }
            $existente = $existentes[(int) $item["produto_id"]] ?? null;
            $produto = buscar_produto_transacao($conexao, $item["produto_id"], $idEmpresa, $existente === null);
            $calculado = $produto === false ? false : calcular_item_pedido($produto, $item["quantidade"], $existente["preco_unitario"] ?? null);
            if ($calculado === false || $total > 999999999999 - $calculado["centavos"]) {
                throw new RuntimeException("Produto ou valor do pedido inválido.");
            }
            $calculado["existente"] = $existente;
            $total += $calculado["centavos"];
            $calculados[] = $calculado;
        }
        if (!$calculados) {
            throw new RuntimeException("O pedido deve possuir ao menos um item ativo.");
        }
        $desativar = mysqli_prepare($conexao, "UPDATE itens_pedido SET ativo = 0 WHERE pedido_id = ? AND empresa_id = ? AND ativo = 1");
        mysqli_stmt_bind_param($desativar, "ii", $idPedido, $idEmpresa);
        if (!mysqli_stmt_execute($desativar)) {
            throw new RuntimeException("Não foi possível atualizar os itens.");
        }
        mysqli_stmt_close($desativar);
        $reativar = mysqli_prepare($conexao, "UPDATE itens_pedido SET quantidade = ?, preco_unitario = ?, subtotal = ?, ativo = 1 WHERE id = ? AND pedido_id = ? AND empresa_id = ?");
        $inserir = mysqli_prepare($conexao, "INSERT INTO itens_pedido (pedido_id, produto_id, empresa_id, quantidade, preco_unitario, subtotal, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        foreach ($calculados as $item) {
            if ($item["existente"] !== null) {
                $idItem = (int) $item["existente"]["id"];
                mysqli_stmt_bind_param($reativar, "issiii", $item["quantidade"], $item["preco"], $item["subtotal"], $idItem, $idPedido, $idEmpresa);
                $sucesso = mysqli_stmt_execute($reativar);
            } else {
                mysqli_stmt_bind_param($inserir, "iiiiss", $idPedido, $item["produto_id"], $idEmpresa, $item["quantidade"], $item["preco"], $item["subtotal"]);
                $sucesso = mysqli_stmt_execute($inserir);
            }
            if (!$sucesso) {
                throw new RuntimeException("Não foi possível atualizar os itens.");
            }
        }
        mysqli_stmt_close($reativar);
        mysqli_stmt_close($inserir);
        $valorTotal = centavos_para_decimal($total);
        $comando = mysqli_prepare($conexao, "UPDATE pedidos SET cliente_id = ?, data_entrega = ?, valor_total = ? WHERE id = ? AND empresa_id = ?");
        mysqli_stmt_bind_param($comando, "issii", $idCliente, $dataEntrega, $valorTotal, $idPedido, $idEmpresa);
        if (!mysqli_stmt_execute($comando) || !mysqli_commit($conexao)) {
            throw new RuntimeException("Não foi possível concluir a edição.");
        }
        mysqli_stmt_close($comando);
        return ["sucesso" => true];
    } catch (Throwable $erro) {
        mysqli_rollback($conexao);
        return ["sucesso" => false, "erro" => $erro->getMessage()];
    }
}

function status_pedido_pode_avancar($atual, $novo)
{
    $ordem = ["RECEBIDO" => 0, "EM_PRODUCAO" => 1, "PRONTO" => 2, "ENTREGUE" => 3];
    return isset($ordem[$atual], $ordem[$novo]) && $ordem[$novo] > $ordem[$atual];
}

function alterar_status_pedido($conexao, $idPedido, $idEmpresa, $idUsuario, $novoStatus, $cancelar = false)
{
    if (!mysqli_begin_transaction($conexao)) {
        return ["sucesso" => false, "erro" => "Não foi possível iniciar a atualização."];
    }
    try {
        $pedido = buscar_linha_pedido_transacao($conexao, $idPedido, $idEmpresa);
        if ($pedido === false || in_array($pedido["status"], ["ENTREGUE", "CANCELADO"], true)) {
            throw new RuntimeException("Este pedido não aceita alterações.");
        }
        if ($cancelar) {
            if (!in_array($pedido["status"], ["RECEBIDO", "EM_PRODUCAO", "PRONTO"], true)) {
                throw new RuntimeException("Este pedido não pode ser cancelado.");
            }
        } elseif (!status_pedido_pode_avancar($pedido["status"], $novoStatus)) {
            throw new RuntimeException("A transição de status não é permitida.");
        }
        $comando = mysqli_prepare($conexao, "UPDATE pedidos SET status = ? WHERE id = ? AND empresa_id = ?");
        mysqli_stmt_bind_param($comando, "sii", $novoStatus, $idPedido, $idEmpresa);
        if (!mysqli_stmt_execute($comando)) {
            throw new RuntimeException("Não foi possível atualizar o status.");
        }
        mysqli_stmt_close($comando);
        $historico = mysqli_prepare($conexao, "INSERT INTO historico_status (pedido_id, usuario_id, empresa_id, status_anterior, status_novo) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($historico, "iiiss", $idPedido, $idUsuario, $idEmpresa, $pedido["status"], $novoStatus);
        if (!mysqli_stmt_execute($historico) || !mysqli_commit($conexao)) {
            throw new RuntimeException("Não foi possível registrar o histórico.");
        }
        mysqli_stmt_close($historico);
        return ["sucesso" => true];
    } catch (Throwable $erro) {
        mysqli_rollback($conexao);
        return ["sucesso" => false, "erro" => $erro->getMessage()];
    }
}

function listar_historico_pedido_empresa($conexao, $idPedido, $idEmpresa)
{
    $sql = "SELECT historico_status.status_anterior, historico_status.status_novo, historico_status.data_alteracao, usuarios.nome AS usuario FROM historico_status INNER JOIN usuarios ON usuarios.id = historico_status.usuario_id AND usuarios.empresa_id = historico_status.empresa_id WHERE historico_status.pedido_id = ? AND historico_status.empresa_id = ? ORDER BY historico_status.data_alteracao, historico_status.id";
    return consultar_pedido($conexao, $sql, "ii", $idPedido, $idEmpresa) ?: [];
}

function listar_pedidos_producao_empresa($conexao, $idEmpresa)
{
    $sql = "SELECT pedidos.id, pedidos.status, pedidos.data_entrega, clientes.nome AS cliente FROM pedidos INNER JOIN clientes ON clientes.id = pedidos.cliente_id AND clientes.empresa_id = pedidos.empresa_id WHERE pedidos.empresa_id = ? AND pedidos.ativo = 1 ORDER BY FIELD(pedidos.status, 'RECEBIDO', 'EM_PRODUCAO', 'PRONTO', 'ENTREGUE', 'CANCELADO'), pedidos.data_entrega";
    return consultar_pedido($conexao, $sql, "i", $idEmpresa) ?: [];
}

function indicador_prazo_pedido($pedido, $dataAtual = null)
{
    if (in_array($pedido["status"], ["ENTREGUE", "CANCELADO"], true) || empty($pedido["data_entrega"])) {
        return "";
    }
    $hoje = new DateTimeImmutable($dataAtual ?? "today");
    $entrega = DateTimeImmutable::createFromFormat("!Y-m-d", $pedido["data_entrega"]);
    if ($entrega < $hoje) {
        return "atrasado";
    }
    return $entrega <= $hoje->modify("+2 days") ? "proximo" : "";
}

function listar_pedidos_cliente_empresa($conexao, $idCliente, $idEmpresa)
{
    return consultar_pedido($conexao, "SELECT id, data_criacao, data_entrega, status, valor_total FROM pedidos WHERE cliente_id = ? AND empresa_id = ? AND ativo = 1 ORDER BY data_criacao DESC", "ii", $idCliente, $idEmpresa) ?: [];
}
