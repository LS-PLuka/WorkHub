<?php

function buscar_administrador_por_email($conexao, $email)
{
    $comando = mysqli_prepare(
        $conexao,
        "SELECT id, nome, email, senha, ativo
         FROM administradores_plataforma
         WHERE email = ?
         LIMIT 1",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "s", $email);
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return false;
    }
    $resultado = mysqli_stmt_get_result($comando);
    $administrador = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return is_array($administrador) ? $administrador : false;
}

function buscar_contexto_administrador_ativo($conexao, $idAdministrador)
{
    $comando = mysqli_prepare(
        $conexao,
        "SELECT id, nome, email
         FROM administradores_plataforma
         WHERE id = ? AND ativo = 1
         LIMIT 1",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "i", $idAdministrador);
    if (!mysqli_stmt_execute($comando)) {
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

function cadastrar_administrador_plataforma($conexao, $nome, $email, $hashSenha)
{
    if (buscar_administrador_por_email($conexao, $email) !== false) {
        return false;
    }
    $comando = mysqli_prepare(
        $conexao,
        "INSERT INTO administradores_plataforma (nome, email, senha, ativo) VALUES (?, ?, ?, 1)",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "sss", $nome, $email, $hashSenha);
    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return $sucesso;
}

function obter_indicadores_administracao($conexao)
{
    $resultado = mysqli_query(
        $conexao,
        "SELECT COUNT(*) AS total_empresas,
                COALESCE(SUM(ativo = 1), 0) AS empresas_ativas,
                COALESCE(SUM(ativo = 0), 0) AS empresas_desativadas
         FROM empresas",
    );
    if ($resultado === false) {
        return false;
    }
    $indicadores = mysqli_fetch_assoc($resultado);
    mysqli_free_result($resultado);
    return $indicadores;
}

function listar_empresas_administracao($conexao, $pesquisa, $situacao)
{
    $sql = "SELECT id, nome_fantasia, razao_social, cnpj, email, telefone, ativo, data_cadastro
            FROM empresas
            WHERE 1 = 1";
    $tipos = "";
    $valores = [];
    if ($pesquisa !== "") {
        $sql .= " AND (nome_fantasia LIKE ? OR razao_social LIKE ? OR cnpj LIKE ?)";
        $termo = "%" . $pesquisa . "%";
        $tipos .= "sss";
        $valores = [$termo, $termo, $termo];
    }
    if ($situacao !== "") {
        $sql .= " AND ativo = ?";
        $tipos .= "i";
        $valores[] = (int) $situacao;
    }
    $sql .= " ORDER BY data_cadastro DESC, id DESC";
    $comando = mysqli_prepare($conexao, $sql);
    if ($comando === false) {
        return [];
    }
    if ($tipos !== "") {
        $referencias = [$comando, $tipos];
        foreach ($valores as $indice => $valor) {
            $referencias[] = &$valores[$indice];
        }
        call_user_func_array("mysqli_stmt_bind_param", $referencias);
    }
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return [];
    }
    $resultado = mysqli_stmt_get_result($comando);
    $empresas = $resultado === false ? [] : mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return $empresas;
}

function buscar_empresa_administracao($conexao, $idEmpresa)
{
    $comando = mysqli_prepare(
        $conexao,
        "SELECT id, nome_fantasia, razao_social, cnpj, email, telefone, ativo, data_cadastro
         FROM empresas
         WHERE id = ?
         LIMIT 1",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "i", $idEmpresa);
    if (!mysqli_stmt_execute($comando)) {
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

function empresa_possui_um_proprietario_ativo($conexao, $idEmpresa)
{
    $comando = mysqli_prepare(
        $conexao,
        "SELECT COUNT(*) AS total
         FROM usuarios
         WHERE empresa_id = ? AND perfil = 'PROPRIETARIO' AND ativo = 1",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "i", $idEmpresa);
    if (!mysqli_stmt_execute($comando)) {
        mysqli_stmt_close($comando);
        return false;
    }
    $resultado = mysqli_stmt_get_result($comando);
    $dados = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);
    return $dados !== false && (int) $dados["total"] === 1;
}

function alterar_situacao_empresa_administracao($conexao, $idEmpresa, $ativo)
{
    $empresa = buscar_empresa_administracao($conexao, $idEmpresa);
    if ($empresa === false) {
        return ["sucesso" => false, "erro" => "Empresa não encontrada."];
    }
    if ($ativo === 1 && !empresa_possui_um_proprietario_ativo($conexao, $idEmpresa)) {
        return ["sucesso" => false, "erro" => "A empresa precisa possuir exatamente um Proprietário ativo para ser reativada."];
    }
    $comando = mysqli_prepare($conexao, "UPDATE empresas SET ativo = ? WHERE id = ?");
    if ($comando === false) {
        return ["sucesso" => false, "erro" => "Não foi possível alterar a situação da empresa."];
    }
    mysqli_stmt_bind_param($comando, "ii", $ativo, $idEmpresa);
    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return $sucesso
        ? ["sucesso" => true, "empresa" => $empresa]
        : ["sucesso" => false, "erro" => "Não foi possível alterar a situação da empresa."];
}
