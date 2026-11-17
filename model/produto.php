<?php
function listar_produtos_empresa($conexao, $idEmpresa, $pesquisa = "")
{
    $termoPesquisa = "%" . $pesquisa . "%";
    $comando = mysqli_prepare(
        $conexao,
        "SELECT id, nome, descricao, preco, ativo FROM produtos WHERE empresa_id = ? AND nome LIKE ? ORDER BY nome",
    );
    if ($comando === false) {
        return [];
    }
    mysqli_stmt_bind_param($comando, "is", $idEmpresa, $termoPesquisa);
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return [];
    }
    $resultado = mysqli_stmt_get_result($comando);
    $produtos = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return $produtos;
}

function buscar_produto_empresa($conexao, $idProduto, $idEmpresa)
{
    $comando = mysqli_prepare(
        $conexao,
        "SELECT id, empresa_id, nome, descricao, preco, ativo FROM produtos WHERE id = ? AND empresa_id = ? LIMIT 1",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "ii", $idProduto, $idEmpresa);
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return false;
    }
    $resultado = mysqli_stmt_get_result($comando);
    $produto = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return is_array($produto) ? $produto : false;
}

function cadastrar_produto($conexao, $idEmpresa, $nome, $descricao, $preco)
{
    $comando = mysqli_prepare(
        $conexao,
        "INSERT INTO produtos (empresa_id, nome, descricao, preco, ativo) VALUES (?, ?, ?, ?, 1)",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "isss", $idEmpresa, $nome, $descricao, $preco);
    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return $sucesso;
}

function atualizar_produto_empresa($conexao, $idProduto, $idEmpresa, $nome, $descricao, $preco)
{
    $comando = mysqli_prepare(
        $conexao,
        "UPDATE produtos SET nome = ?, descricao = ?, preco = ? WHERE id = ? AND empresa_id = ?",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "sssii", $nome, $descricao, $preco, $idProduto, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return $sucesso;
}

function alterar_situacao_produto_empresa($conexao, $idProduto, $idEmpresa, $ativo)
{
    $comando = mysqli_prepare($conexao, "UPDATE produtos SET ativo = ? WHERE id = ? AND empresa_id = ?");
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "iii", $ativo, $idProduto, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    $linhas = mysqli_stmt_affected_rows($comando);
    mysqli_stmt_close($comando);
    return $sucesso && $linhas === 1;
}
