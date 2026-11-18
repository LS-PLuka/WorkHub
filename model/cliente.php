<?php
function listar_clientes_empresa($conexao, $idEmpresa, $pesquisa = "")
{
    $termo = "%" . $pesquisa . "%";
    $c = mysqli_prepare(
        $conexao,
        "SELECT id,nome,telefone,email,ativo FROM clientes WHERE empresa_id=? AND nome LIKE ? ORDER BY nome",
    );
    if (!$c) {
        return [];
    }
    mysqli_stmt_bind_param($c, "is", $idEmpresa, $termo);
    if (!mysqli_stmt_execute($c)) {
        mysqli_stmt_close($c);
        return [];
    }
    $r = mysqli_stmt_get_result($c);
    $dados = $r === false ? [] : mysqli_fetch_all($r, MYSQLI_ASSOC);
    if ($r !== false) {
        mysqli_free_result($r);
    }
    mysqli_stmt_close($c);
    return $dados;
}

function buscar_cliente_empresa($conexao, $idCliente, $idEmpresa)
{
    $c = mysqli_prepare(
        $conexao,
        "SELECT id,empresa_id,nome,telefone,email,ativo FROM clientes WHERE id=? AND empresa_id=? LIMIT 1",
    );
    if (!$c) {
        return false;
    }
    mysqli_stmt_bind_param($c, "ii", $idCliente, $idEmpresa);
    if (!mysqli_stmt_execute($c)) {
        mysqli_stmt_close($c);
        return false;
    }
    $r = mysqli_stmt_get_result($c);
    $d = $r === false ? false : mysqli_fetch_assoc($r);
    if ($r !== false) {
        mysqli_free_result($r);
    }
    mysqli_stmt_close($c);
    return is_array($d) ? $d : false;
}

function cadastrar_cliente($conexao, $idEmpresa, $nome, $telefone, $email)
{
    $c = mysqli_prepare($conexao, "INSERT INTO clientes (empresa_id,nome,telefone,email,ativo) VALUES (?,?,?,?,1)");
    if (!$c) {
        return false;
    }
    mysqli_stmt_bind_param($c, "isss", $idEmpresa, $nome, $telefone, $email);
    $s = mysqli_stmt_execute($c);
    mysqli_stmt_close($c);
    return $s;
}

function atualizar_cliente_empresa($conexao, $idCliente, $idEmpresa, $nome, $telefone, $email)
{
    $c = mysqli_prepare($conexao, "UPDATE clientes SET nome=?,telefone=?,email=? WHERE id=? AND empresa_id=? AND ativo=1");
    if (!$c) {
        return false;
    }
    mysqli_stmt_bind_param($c, "sssii", $nome, $telefone, $email, $idCliente, $idEmpresa);
    $s = mysqli_stmt_execute($c);
    $linhasAfetadas = mysqli_stmt_affected_rows($c);
    mysqli_stmt_close($c);
    if (!$s || $linhasAfetadas > 0) {
        return $s;
    }

    $cliente = buscar_cliente_empresa($conexao, $idCliente, $idEmpresa);
    return $cliente !== false && (int) $cliente["ativo"] === 1;
}

function alterar_situacao_cliente_empresa($conexao, $idCliente, $idEmpresa, $ativo)
{
    $c = mysqli_prepare($conexao, "UPDATE clientes SET ativo=? WHERE id=? AND empresa_id=?");
    if (!$c) {
        return false;
    }
    mysqli_stmt_bind_param($c, "iii", $ativo, $idCliente, $idEmpresa);
    $s = mysqli_stmt_execute($c);
    $l = mysqli_stmt_affected_rows($c);
    mysqli_stmt_close($c);
    return $s && $l === 1;
}
