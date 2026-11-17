<?php

const MAXIMO_FALHAS_LOGIN = 5;
const BLOQUEIO_LOGIN_MINUTOS = 15;

function obter_origem_requisicao()
{
    $origem = $_SERVER["REMOTE_ADDR"] ?? "desconhecida";
    $proxyConfiavel = strtolower((string) getenv("PROXY_REVERSO_CONFIAVEL"));

    if (in_array($proxyConfiavel, ["1", "true"], true) && !empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $origem = trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
    }

    return $origem;
}

function obter_chave_limite_login($tipoAutenticacao, $email)
{
    $segredo = (string) (getenv("APP_CHAVE_RATE_LIMIT") ?: getenv("DB_PASSWORD"));
    $dados = strtolower(trim($tipoAutenticacao)) . "|" . strtolower(trim($email)) . "|" . obter_origem_requisicao();

    return hash_hmac("sha256", $dados, $segredo);
}

function login_esta_bloqueado($conexao, $tipoAutenticacao, $email)
{
    $chave = obter_chave_limite_login($tipoAutenticacao, $email);
    $comando = mysqli_prepare(
        $conexao,
        "SELECT bloqueado_ate FROM tentativas_login WHERE tipo_autenticacao = ? AND chave = ? LIMIT 1",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "ss", $tipoAutenticacao, $chave);
    $sucesso = mysqli_stmt_execute($comando);
    $resultado = $sucesso ? mysqli_stmt_get_result($comando) : false;
    $tentativa = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comando);

    return is_array($tentativa) && $tentativa["bloqueado_ate"] !== null &&
        strtotime($tentativa["bloqueado_ate"]) > time();
}

function registrar_falha_login($conexao, $tipoAutenticacao, $email)
{
    $chave = obter_chave_limite_login($tipoAutenticacao, $email);
    $comandoConsulta = mysqli_prepare(
        $conexao,
        "SELECT falhas, bloqueado_ate FROM tentativas_login WHERE tipo_autenticacao = ? AND chave = ? LIMIT 1",
    );
    if ($comandoConsulta === false) {
        return false;
    }
    mysqli_stmt_bind_param($comandoConsulta, "ss", $tipoAutenticacao, $chave);
    $sucessoConsulta = mysqli_stmt_execute($comandoConsulta);
    $resultado = $sucessoConsulta ? mysqli_stmt_get_result($comandoConsulta) : false;
    $tentativa = $resultado === false ? false : mysqli_fetch_assoc($resultado);
    if ($resultado !== false) {
        mysqli_free_result($resultado);
    }
    mysqli_stmt_close($comandoConsulta);

    $bloqueioExpirou = is_array($tentativa) && $tentativa["bloqueado_ate"] !== null &&
        strtotime($tentativa["bloqueado_ate"]) <= time();
    $falhas = $bloqueioExpirou || !is_array($tentativa) ? 1 : (int) $tentativa["falhas"] + 1;
    $bloqueadoAte = $falhas >= MAXIMO_FALHAS_LOGIN
        ? date("Y-m-d H:i:s", time() + (BLOQUEIO_LOGIN_MINUTOS * 60))
        : null;

    if (is_array($tentativa)) {
        $comando = mysqli_prepare(
            $conexao,
            "UPDATE tentativas_login SET falhas = ?, bloqueado_ate = ? WHERE tipo_autenticacao = ? AND chave = ?",
        );
        if ($comando === false) {
            return false;
        }
        mysqli_stmt_bind_param($comando, "isss", $falhas, $bloqueadoAte, $tipoAutenticacao, $chave);
    } else {
        $comando = mysqli_prepare(
            $conexao,
            "INSERT INTO tentativas_login (tipo_autenticacao, chave, falhas, bloqueado_ate) VALUES (?, ?, ?, ?)",
        );
        if ($comando === false) {
            return false;
        }
        mysqli_stmt_bind_param($comando, "ssis", $tipoAutenticacao, $chave, $falhas, $bloqueadoAte);
    }

    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);

    return $sucesso;
}

function limpar_falhas_login($conexao, $tipoAutenticacao, $email)
{
    $chave = obter_chave_limite_login($tipoAutenticacao, $email);
    $comando = mysqli_prepare(
        $conexao,
        "DELETE FROM tentativas_login WHERE tipo_autenticacao = ? AND chave = ?",
    );
    if ($comando === false) {
        return false;
    }
    mysqli_stmt_bind_param($comando, "ss", $tipoAutenticacao, $chave);
    $sucesso = mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);

    return $sucesso;
}
