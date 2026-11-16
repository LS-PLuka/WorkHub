<?php

function buscar_usuario_por_email($conexao, $emailUsuario)
{
    $comando = mysqli_prepare(
        $conexao,
        'SELECT usuarios.id, usuarios.empresa_id, usuarios.nome, usuarios.email, usuarios.senha, usuarios.perfil, usuarios.ativo AS usuario_ativo, empresas.ativo AS empresa_ativa
         FROM usuarios
         INNER JOIN empresas ON empresas.id = usuarios.empresa_id
         WHERE usuarios.email = ?
         LIMIT 1'
    );

    if ($comando === false) {
        return false;
    }

    mysqli_stmt_bind_param($comando, 's', $emailUsuario);

    if (mysqli_stmt_execute($comando) === false) {
        mysqli_stmt_close($comando);
        return false;
    }

    $resultado = mysqli_stmt_get_result($comando);
    $dadosUsuario = $resultado === false ? false : mysqli_fetch_assoc($resultado);

    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);

    return is_array($dadosUsuario) ? $dadosUsuario : false;
}

function buscar_contexto_usuario_ativo($conexao, $idUsuario)
{
    $comando = mysqli_prepare(
        $conexao,
        'SELECT usuarios.id AS id_usuario, usuarios.empresa_id, usuarios.nome AS nome_usuario, usuarios.perfil, empresas.nome_fantasia AS nome_empresa
         FROM usuarios
         INNER JOIN empresas ON empresas.id = usuarios.empresa_id
         WHERE usuarios.id = ? AND usuarios.ativo = 1 AND empresas.ativo = 1
         LIMIT 1'
    );

    if ($comando === false) {
        return false;
    }

    mysqli_stmt_bind_param($comando, 'i', $idUsuario);

    if (mysqli_stmt_execute($comando) === false) {
        mysqli_stmt_close($comando);
        return false;
    }

    $resultado = mysqli_stmt_get_result($comando);
    $contexto = $resultado === false ? false : mysqli_fetch_assoc($resultado);

    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);

    return is_array($contexto) ? $contexto : false;
}

