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



function listar_usuarios_empresa($conexao, $idEmpresa, $pesquisa = '')
{
    $termoPesquisa = '%' . $pesquisa . '%';
    $comando = mysqli_prepare($conexao, 'SELECT id, nome, email, perfil, ativo FROM usuarios WHERE empresa_id = ? AND nome LIKE ? ORDER BY CASE WHEN perfil = \'PROPRIETARIO\' THEN 0 ELSE 1 END, nome');
    if ($comando === false) { return []; }
    mysqli_stmt_bind_param($comando, 'is', $idEmpresa, $termoPesquisa);
    if (mysqli_stmt_execute($comando) === false) { mysqli_stmt_close($comando); return []; }
    $resultado = mysqli_stmt_get_result($comando);
    $usuarios = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) { mysqli_free_result($resultado); }
    mysqli_stmt_close($comando);
    return $usuarios;
}

function buscar_usuario_empresa($conexao, $idUsuario, $idEmpresa)
{
    $comando = mysqli_prepare($conexao, 'SELECT id, empresa_id, nome, email, perfil, ativo FROM usuarios WHERE id = ? AND empresa_id = ? LIMIT 1');
    if ($comando === false) { return false; }
    mysqli_stmt_bind_param($comando, 'ii', $idUsuario, $idEmpresa);
    if (mysqli_stmt_execute($comando) === false) { mysqli_stmt_close($comando); return false; }
    $resultado = mysqli_stmt_get_result($comando);
    $usuario = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) { mysqli_free_result($resultado); }
    mysqli_stmt_close($comando);
    return is_array($usuario) ? $usuario : false;
}

function email_usuario_existe_para_outro($conexao, $emailUsuario, $idUsuario)
{
    $comando = mysqli_prepare($conexao, 'SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
    if ($comando === false) { return false; }
    mysqli_stmt_bind_param($comando, 'si', $emailUsuario, $idUsuario);
    $executado = mysqli_stmt_execute($comando);
    mysqli_stmt_store_result($comando);
    $existe = $executado && mysqli_stmt_num_rows($comando) > 0;
    mysqli_stmt_close($comando);
    return $existe;
}

function cadastrar_usuario($conexao, $idEmpresa, $nome, $emailUsuario, $hashSenha, $perfil)
{
    $comando = mysqli_prepare($conexao, 'INSERT INTO usuarios (empresa_id, nome, email, senha, perfil, ativo) VALUES (?, ?, ?, ?, ?, 1)');
    if ($comando === false) { return false; }
    mysqli_stmt_bind_param($comando, 'issss', $idEmpresa, $nome, $emailUsuario, $hashSenha, $perfil);
    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return $sucesso;
}

function atualizar_usuario_empresa($conexao, $idUsuario, $idEmpresa, $nome, $emailUsuario, $perfil)
{
    $comando = mysqli_prepare($conexao, "UPDATE usuarios SET nome = ?, email = ?, perfil = ? WHERE id = ? AND empresa_id = ? AND perfil <> 'PROPRIETARIO'");
    if ($comando === false) { return false; }
    mysqli_stmt_bind_param($comando, 'sssii', $nome, $emailUsuario, $perfil, $idUsuario, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    $linhasAfetadas = mysqli_stmt_affected_rows($comando);
    mysqli_stmt_close($comando);
    return $sucesso && $linhasAfetadas >= 0;
}

function alterar_situacao_usuario_empresa($conexao, $idUsuario, $idEmpresa, $ativo)
{
    $comando = mysqli_prepare($conexao, "UPDATE usuarios SET ativo = ? WHERE id = ? AND empresa_id = ? AND perfil <> 'PROPRIETARIO'");
    if ($comando === false) { return false; }
    mysqli_stmt_bind_param($comando, 'iii', $ativo, $idUsuario, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    $linhasAfetadas = mysqli_stmt_affected_rows($comando);
    mysqli_stmt_close($comando);
    return $sucesso && $linhasAfetadas === 1;
}

