<?php

function cnpj_empresa_existe($conexao, $cnpj)
{
    $comando = mysqli_prepare($conexao, "SELECT id FROM empresas WHERE cnpj = ? LIMIT 1");
    if ($comando === false) {
        return false;
    }

    mysqli_stmt_bind_param($comando, "s", $cnpj);
    $executado = mysqli_stmt_execute($comando);
    mysqli_stmt_store_result($comando);
    $existe = $executado && mysqli_stmt_num_rows($comando) > 0;
    mysqli_stmt_close($comando);

    return $existe;
}

function email_usuario_existe($conexao, $email)
{
    $comando = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    if ($comando === false) {
        return false;
    }

    mysqli_stmt_bind_param($comando, "s", $email);
    $executado = mysqli_stmt_execute($comando);
    mysqli_stmt_store_result($comando);
    $existe = $executado && mysqli_stmt_num_rows($comando) > 0;
    mysqli_stmt_close($comando);

    return $existe;
}

function buscar_empresa_por_id($conexao, $idEmpresa)
{
    $comando = mysqli_prepare(
        $conexao,
        "SELECT id, nome_fantasia, razao_social, cnpj, email, telefone, ativo
         FROM empresas
         WHERE id = ?
         LIMIT 1",
    );
    if ($comando === false) {
        return false;
    }

    mysqli_stmt_bind_param($comando, "i", $idEmpresa);
    if (mysqli_stmt_execute($comando) === false) {
        mysqli_stmt_close($comando);
        return false;
    }

    $resultado = mysqli_stmt_get_result($comando);
    $empresa = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);

    return is_array($empresa) ? $empresa : false;
}

function atualizar_dados_cadastrais_empresa($conexao, $idEmpresa, $nomeFantasia, $razaoSocial, $email, $telefone)
{
    $comando = mysqli_prepare(
        $conexao,
        "UPDATE empresas
         SET nome_fantasia = ?, razao_social = ?, email = ?, telefone = ?
         WHERE id = ?",
    );
    if ($comando === false) {
        return false;
    }

    mysqli_stmt_bind_param($comando, "ssssi", $nomeFantasia, $razaoSocial, $email, $telefone, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);

    return $sucesso;
}

/** Cria empresa ativa e seu Proprietário ativo na mesma transação. */
function cadastrar_empresa_com_proprietario($conexao, $empresa, $proprietario, $confirmarTransacao = true)
{
    if (mysqli_begin_transaction($conexao) === false) {
        return ["sucesso" => false, "motivo" => "transacao"];
    }

    $comandoEmpresa = mysqli_prepare(
        $conexao,
        "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone, ativo) VALUES (?, ?, ?, ?, ?, 1)",
    );
    if ($comandoEmpresa === false) {
        mysqli_rollback($conexao);
        return ["sucesso" => false, "motivo" => "empresa"];
    }

    mysqli_stmt_bind_param(
        $comandoEmpresa,
        "sssss",
        $empresa["nome_fantasia"],
        $empresa["razao_social"],
        $empresa["cnpj"],
        $empresa["email"],
        $empresa["telefone"],
    );
    if (mysqli_stmt_execute($comandoEmpresa) === false) {
        mysqli_stmt_close($comandoEmpresa);
        mysqli_rollback($conexao);
        return ["sucesso" => false, "motivo" => "empresa"];
    }

    $idEmpresa = mysqli_insert_id($conexao);
    mysqli_stmt_close($comandoEmpresa);
    $comandoProprietario = mysqli_prepare(
        $conexao,
        "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil, ativo) VALUES (?, ?, ?, ?, 'PROPRIETARIO', 1)",
    );
    if ($comandoProprietario === false) {
        mysqli_rollback($conexao);
        return ["sucesso" => false, "motivo" => "proprietario"];
    }

    mysqli_stmt_bind_param(
        $comandoProprietario,
        "isss",
        $idEmpresa,
        $proprietario["nome"],
        $proprietario["email"],
        $proprietario["hash_senha"],
    );
    if (mysqli_stmt_execute($comandoProprietario) === false) {
        mysqli_stmt_close($comandoProprietario);
        mysqli_rollback($conexao);
        return ["sucesso" => false, "motivo" => "proprietario"];
    }

    $idProprietario = mysqli_insert_id($conexao);
    mysqli_stmt_close($comandoProprietario);

    if ($confirmarTransacao && mysqli_commit($conexao) === false) {
        mysqli_rollback($conexao);
        return ["sucesso" => false, "motivo" => "confirmacao"];
    }

    return ["sucesso" => true, "id_empresa" => $idEmpresa, "id_proprietario" => $idProprietario];
}
