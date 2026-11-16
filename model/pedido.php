<?php

function listar_clientes_ativos_empresa($conexao, $idEmpresa)
{
    $comando = mysqli_prepare($conexao, 'SELECT id, nome FROM clientes WHERE empresa_id = ? AND ativo = 1 ORDER BY nome');
    if ($comando === false) { return []; }
    mysqli_stmt_bind_param($comando, 'i', $idEmpresa);
    if (!mysqli_stmt_execute($comando)) { mysqli_stmt_close($comando); return []; }
    $resultado = mysqli_stmt_get_result($comando);
    $clientes = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) { mysqli_free_result($resultado); }
    mysqli_stmt_close($comando);
    return $clientes;
}

function listar_produtos_ativos_empresa($conexao, $idEmpresa)
{
    $comando = mysqli_prepare($conexao, 'SELECT id, nome, preco FROM produtos WHERE empresa_id = ? AND ativo = 1 ORDER BY nome');
    if ($comando === false) { return []; }
    mysqli_stmt_bind_param($comando, 'i', $idEmpresa);
    if (!mysqli_stmt_execute($comando)) { mysqli_stmt_close($comando); return []; }
    $resultado = mysqli_stmt_get_result($comando);
    $produtos = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) { mysqli_free_result($resultado); }
    mysqli_stmt_close($comando);
    return $produtos;
}

function valor_decimal_para_centavos($valor)
{
    if (!is_string($valor) || !preg_match('/^(\d{1,10})\.(\d{2})$/', $valor, $partes)) { return false; }
    return ((int) $partes[1] * 100) + (int) $partes[2];
}

function centavos_para_decimal($centavos)
{
    return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
}

function buscar_cliente_ativo_transacao($conexao, $idCliente, $idEmpresa)
{
    $comando = mysqli_prepare($conexao, 'SELECT id FROM clientes WHERE id = ? AND empresa_id = ? AND ativo = 1 FOR UPDATE');
    if ($comando === false) { return false; }
    mysqli_stmt_bind_param($comando, 'ii', $idCliente, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    $resultado = $sucesso ? mysqli_stmt_get_result($comando) : false;
    $cliente = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) { mysqli_free_result($resultado); }
    mysqli_stmt_close($comando);
    return is_array($cliente) ? $cliente : false;
}

function buscar_produto_ativo_transacao($conexao, $idProduto, $idEmpresa)
{
    $comando = mysqli_prepare($conexao, 'SELECT id, nome, preco FROM produtos WHERE id = ? AND empresa_id = ? AND ativo = 1 FOR UPDATE');
    if ($comando === false) { return false; }
    mysqli_stmt_bind_param($comando, 'ii', $idProduto, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    $resultado = $sucesso ? mysqli_stmt_get_result($comando) : false;
    $produto = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) { mysqli_free_result($resultado); }
    mysqli_stmt_close($comando);
    return is_array($produto) ? $produto : false;
}

function cadastrar_pedido($conexao, $idEmpresa, $idUsuario, $idCliente, $dataEntrega, $itens, $gerenciarTransacao = true)
{
    if ($gerenciarTransacao && !mysqli_begin_transaction($conexao)) { return ['sucesso' => false, 'erro' => 'Não foi possível iniciar o cadastro do pedido.']; }
    if (!$gerenciarTransacao && !mysqli_query($conexao, 'SAVEPOINT cadastro_pedido')) { return ['sucesso' => false, 'erro' => 'Não foi possível iniciar o cadastro do pedido.']; }
    try {
        if (buscar_cliente_ativo_transacao($conexao, $idCliente, $idEmpresa) === false) {
            throw new RuntimeException('O cliente informado não existe, está inativo ou não pertence à empresa.');
        }
        $itensCalculados = [];
        $totalCentavos = 0;
        foreach ($itens as $item) {
            $produto = buscar_produto_ativo_transacao($conexao, $item['produto_id'], $idEmpresa);
            if ($produto === false) { throw new RuntimeException('Um dos produtos informados não existe, está inativo ou não pertence à empresa.'); }
            $precoCentavos = valor_decimal_para_centavos($produto['preco']);
            if ($precoCentavos === false || $item['quantidade'] > intdiv(999999999999, max(1, $precoCentavos))) {
                throw new RuntimeException('A quantidade informada ultrapassa o limite permitido.');
            }
            $subtotalCentavos = $item['quantidade'] * $precoCentavos;
            if ($totalCentavos > 999999999999 - $subtotalCentavos) { throw new RuntimeException('O valor total ultrapassa o limite permitido.'); }
            $totalCentavos += $subtotalCentavos;
            $itensCalculados[] = ['produto_id' => (int) $produto['id'], 'quantidade' => $item['quantidade'], 'preco' => centavos_para_decimal($precoCentavos), 'subtotal' => centavos_para_decimal($subtotalCentavos)];
        }
        $valorTotal = centavos_para_decimal($totalCentavos);
        $comandoPedido = mysqli_prepare($conexao, "INSERT INTO pedidos (empresa_id, cliente_id, usuario_id, data_entrega, status, valor_total, ativo) VALUES (?, ?, ?, ?, 'RECEBIDO', ?, 1)");
        if ($comandoPedido === false) { throw new RuntimeException('Não foi possível registrar o pedido.'); }
        mysqli_stmt_bind_param($comandoPedido, 'iiiss', $idEmpresa, $idCliente, $idUsuario, $dataEntrega, $valorTotal);
        if (!mysqli_stmt_execute($comandoPedido)) { mysqli_stmt_close($comandoPedido); throw new RuntimeException('Não foi possível registrar o pedido.'); }
        $idPedido = mysqli_insert_id($conexao);
        mysqli_stmt_close($comandoPedido);
        $comandoItem = mysqli_prepare($conexao, 'INSERT INTO itens_pedido (pedido_id, produto_id, empresa_id, quantidade, preco_unitario, subtotal, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)');
        if ($comandoItem === false) { throw new RuntimeException('Não foi possível registrar os itens do pedido.'); }
        foreach ($itensCalculados as $item) {
            mysqli_stmt_bind_param($comandoItem, 'iiiiss', $idPedido, $item['produto_id'], $idEmpresa, $item['quantidade'], $item['preco'], $item['subtotal']);
            if (!mysqli_stmt_execute($comandoItem)) { mysqli_stmt_close($comandoItem); throw new RuntimeException('Não foi possível registrar os itens do pedido.'); }
        }
        mysqli_stmt_close($comandoItem);
        if ($gerenciarTransacao && !mysqli_commit($conexao)) { throw new RuntimeException('Não foi possível concluir o cadastro do pedido.'); }
        return ['sucesso' => true, 'id_pedido' => $idPedido];
    } catch (Throwable $erro) {
        if ($gerenciarTransacao) { mysqli_rollback($conexao); } else { mysqli_query($conexao, 'ROLLBACK TO SAVEPOINT cadastro_pedido'); }
        return ['sucesso' => false, 'erro' => $erro->getMessage()];
    }
}

function listar_pedidos_empresa($conexao, $idEmpresa, $filtros)
{
    $sql = 'SELECT pedidos.id, clientes.nome AS cliente, pedidos.data_criacao, pedidos.data_entrega, pedidos.status, pedidos.valor_total FROM pedidos INNER JOIN clientes ON clientes.id = pedidos.cliente_id AND clientes.empresa_id = pedidos.empresa_id WHERE pedidos.empresa_id = ? AND pedidos.ativo = 1';
    $tipos = 'i'; $valores = [$idEmpresa];
    if ($filtros['id'] !== null) { $sql .= ' AND pedidos.id = ?'; $tipos .= 'i'; $valores[] = $filtros['id']; }
    if ($filtros['status'] !== '') { $sql .= ' AND pedidos.status = ?'; $tipos .= 's'; $valores[] = $filtros['status']; }
    if ($filtros['data_inicial'] !== '') { $sql .= ' AND DATE(pedidos.data_criacao) >= ?'; $tipos .= 's'; $valores[] = $filtros['data_inicial']; }
    if ($filtros['data_final'] !== '') { $sql .= ' AND DATE(pedidos.data_criacao) <= ?'; $tipos .= 's'; $valores[] = $filtros['data_final']; }
    $sql .= ' ORDER BY pedidos.data_criacao DESC, pedidos.id DESC';
    $comando = mysqli_prepare($conexao, $sql); if ($comando === false) { return []; }
    $referencias = [$comando, $tipos]; foreach ($valores as $indice => $valor) { $referencias[] = &$valores[$indice]; }
    call_user_func_array('mysqli_stmt_bind_param', $referencias);
    if (!mysqli_stmt_execute($comando)) { mysqli_stmt_close($comando); return []; }
    $resultado = mysqli_stmt_get_result($comando); $pedidos = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) { mysqli_free_result($resultado); } mysqli_stmt_close($comando); return $pedidos;
}

function buscar_pedido_por_id($conexao, $idPedido, $idEmpresa)
{
    $comando = mysqli_prepare($conexao, 'SELECT pedidos.id, pedidos.data_criacao, pedidos.data_entrega, pedidos.status, pedidos.valor_total, clientes.nome AS cliente, clientes.telefone AS telefone_cliente, clientes.email AS email_cliente, usuarios.nome AS usuario FROM pedidos INNER JOIN clientes ON clientes.id = pedidos.cliente_id AND clientes.empresa_id = pedidos.empresa_id INNER JOIN usuarios ON usuarios.id = pedidos.usuario_id AND usuarios.empresa_id = pedidos.empresa_id WHERE pedidos.id = ? AND pedidos.empresa_id = ? AND pedidos.ativo = 1 LIMIT 1');
    if ($comando === false) { return false; } mysqli_stmt_bind_param($comando, 'ii', $idPedido, $idEmpresa);
    if (!mysqli_stmt_execute($comando)) { mysqli_stmt_close($comando); return false; }
    $resultado = mysqli_stmt_get_result($comando); $pedido = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) { mysqli_free_result($resultado); } mysqli_stmt_close($comando); return is_array($pedido) ? $pedido : false;
}

function listar_itens_pedido_empresa($conexao, $idPedido, $idEmpresa)
{
    $comando = mysqli_prepare($conexao, 'SELECT itens_pedido.produto_id, produtos.nome AS produto, itens_pedido.quantidade, itens_pedido.preco_unitario, itens_pedido.subtotal FROM itens_pedido INNER JOIN produtos ON produtos.id = itens_pedido.produto_id AND produtos.empresa_id = itens_pedido.empresa_id INNER JOIN pedidos ON pedidos.id = itens_pedido.pedido_id AND pedidos.empresa_id = itens_pedido.empresa_id WHERE itens_pedido.pedido_id = ? AND itens_pedido.empresa_id = ? AND itens_pedido.ativo = 1 AND pedidos.ativo = 1 ORDER BY itens_pedido.id');
    if ($comando === false) { return []; } mysqli_stmt_bind_param($comando, 'ii', $idPedido, $idEmpresa);
    if (!mysqli_stmt_execute($comando)) { mysqli_stmt_close($comando); return []; }
    $resultado = mysqli_stmt_get_result($comando); $itens = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) { mysqli_free_result($resultado); } mysqli_stmt_close($comando); return $itens;
}
