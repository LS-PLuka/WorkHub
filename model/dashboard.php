<?php

function obter_indicadores_dashboard($conexao, $idEmpresa, $dataInicial, $dataFinal, $dataAtual)
{
    $sql = "SELECT
                COUNT(*) AS total_pedidos,
                COALESCE(SUM(status = 'EM_PRODUCAO'), 0) AS pedidos_em_producao,
                COALESCE(SUM(status = 'PRONTO'), 0) AS pedidos_prontos,
                COALESCE(SUM(status = 'ENTREGUE'), 0) AS pedidos_entregues,
                COALESCE(SUM(
                    status NOT IN ('ENTREGUE', 'CANCELADO')
                    AND data_entrega >= ?
                    AND data_entrega <= DATE_ADD(?, INTERVAL 2 DAY)
                ), 0) AS pedidos_proximos_vencimento,
                COALESCE(SUM(
                    status NOT IN ('ENTREGUE', 'CANCELADO')
                    AND data_entrega < ?
                ), 0) AS pedidos_atrasados,
                COALESCE(SUM(
                    CASE
                        WHEN status <> 'CANCELADO'
                            AND data_criacao >= ?
                            AND data_criacao < DATE_ADD(?, INTERVAL 1 DAY)
                        THEN valor_total
                        ELSE 0
                    END
                ), 0.00) AS valor_total_periodo
            FROM pedidos
            WHERE empresa_id = ?
                AND ativo = 1";
    $comando = mysqli_prepare($conexao, $sql);
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param(
        $comando,
        "sssssi",
        $dataAtual,
        $dataAtual,
        $dataAtual,
        $dataInicial,
        $dataFinal,
        $idEmpresa,
    );
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return false;
    }
    $resultado = mysqli_stmt_get_result($comando);
    $indicadores = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return $indicadores;
}

function listar_pedidos_recentes_dashboard($conexao, $idEmpresa)
{
    $sql = "SELECT pedidos.id, pedidos.data_criacao, pedidos.status, pedidos.valor_total,
                   clientes.nome AS cliente
            FROM pedidos
            INNER JOIN clientes ON clientes.id = pedidos.cliente_id
                AND clientes.empresa_id = pedidos.empresa_id
            WHERE pedidos.empresa_id = ?
                AND pedidos.ativo = 1
            ORDER BY pedidos.data_criacao DESC, pedidos.id DESC
            LIMIT 10";
    $comando = mysqli_prepare($conexao, $sql);
    if ($comando === false) {
        return [];
    }
    mysqli_stmt_bind_param($comando, "i", $idEmpresa);
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

function listar_alertas_prazo_dashboard($conexao, $idEmpresa, $dataAtual)
{
    $sql = "SELECT pedidos.id, pedidos.data_entrega, pedidos.status, clientes.nome AS cliente
            FROM pedidos
            INNER JOIN clientes ON clientes.id = pedidos.cliente_id
                AND clientes.empresa_id = pedidos.empresa_id
            WHERE pedidos.empresa_id = ?
                AND pedidos.ativo = 1
                AND pedidos.status NOT IN ('ENTREGUE', 'CANCELADO')
                AND pedidos.data_entrega IS NOT NULL
                AND pedidos.data_entrega <= DATE_ADD(?, INTERVAL 2 DAY)
            ORDER BY pedidos.data_entrega, pedidos.id";
    $comando = mysqli_prepare($conexao, $sql);
    if ($comando === false) {
        return [];
    }
    mysqli_stmt_bind_param($comando, "is", $idEmpresa, $dataAtual);
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return [];
    }
    $resultado = mysqli_stmt_get_result($comando);
    $alertas = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    foreach ($alertas as &$alerta) {
        $alerta["situacao_prazo"] = $alerta["data_entrega"] < $dataAtual ? "atrasado" : "proximo";
    }
    unset($alerta);
    return $alertas;
}
