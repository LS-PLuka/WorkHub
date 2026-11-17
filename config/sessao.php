<?php

require_once __DIR__ . "/banco_dados.php";

function iniciar_sessao_segura()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $ambiente = strtolower((string) getenv("APP_ENV"));
    $cookieSeguroConfigurado = strtolower((string) getenv("SESSAO_COOKIE_SECURE"));
    $usaHttps = in_array($ambiente, ["production", "producao"], true) ||
        in_array($cookieSeguroConfigurado, ["1", "true"], true) ||
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $usaHttps,
        "httponly" => true,
        "samesite" => "Lax",
    ]);
    session_start();
}

function gerar_token_csrf($chave)
{
    iniciar_sessao_segura();

    if (empty($_SESSION["tokens_csrf"][$chave])) {
        $_SESSION["tokens_csrf"][$chave] = bin2hex(random_bytes(32));
    }

    return $_SESSION["tokens_csrf"][$chave];
}

function validar_token_csrf($chave, $token)
{
    iniciar_sessao_segura();

    return is_string($token) &&
        !empty($_SESSION["tokens_csrf"][$chave]) &&
        hash_equals($_SESSION["tokens_csrf"][$chave], $token);
}

function encerrar_sessao()
{
    iniciar_sessao_segura();
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $parametros["path"],
            $parametros["domain"],
            $parametros["secure"],
            $parametros["httponly"],
        );
    }

    session_destroy();
}

function usuario_autenticado()
{
    iniciar_sessao_segura();

    return ($_SESSION["tipo_autenticacao"] ?? "") === "empresarial" && !empty($_SESSION["id_usuario"]);
}

function obter_contexto_autenticado()
{
    iniciar_sessao_segura();

    if (!usuario_autenticado()) {
        return false;
    }

    if (empty($_SESSION["ultima_atividade"]) || time() - $_SESSION["ultima_atividade"] > 1800) {
        encerrar_sessao();
        return false;
    }

    $conexao = conectar_banco_dados();
    if ($conexao === false) {
        return false;
    }

    require_once __DIR__ . "/../model/usuario.php";
    $contexto = buscar_contexto_usuario_ativo($conexao, (int) $_SESSION["id_usuario"]);
    mysqli_close($conexao);

    if ($contexto === false) {
        encerrar_sessao();
        return false;
    }

    $_SESSION["ultima_atividade"] = time();

    return $contexto;
}

function exigir_autenticacao()
{
    $contexto = obter_contexto_autenticado();

    if ($contexto === false) {
        header("Location: /entrar");
        exit();
    }

    return $contexto;
}

function verificar_permissao($perfisPermitidos, $contexto = null)
{
    if ($contexto === null) {
        $contexto = obter_contexto_autenticado();
    }

    return $contexto !== false && in_array($contexto["perfil"], $perfisPermitidos, true);
}

function exigir_permissao($perfisPermitidos)
{
    $contexto = exigir_autenticacao();

    if (!verificar_permissao($perfisPermitidos, $contexto)) {
        http_response_code(403);
        echo "Acesso não autorizado.";
        exit();
    }

    return $contexto;
}

function administrador_autenticado()
{
    iniciar_sessao_segura();

    return ($_SESSION["tipo_autenticacao"] ?? "") === "administrativo" &&
        !empty($_SESSION["id_administrador_plataforma"]);
}

function obter_contexto_administrativo()
{
    iniciar_sessao_segura();

    if (!administrador_autenticado()) {
        return false;
    }

    if (
        empty($_SESSION["ultima_atividade_administrativa"]) ||
        time() - $_SESSION["ultima_atividade_administrativa"] > 1800
    ) {
        encerrar_sessao();
        return false;
    }

    $conexao = conectar_banco_dados();
    if ($conexao === false) {
        return false;
    }

    require_once __DIR__ . "/../model/administracao.php";
    $contexto = buscar_contexto_administrador_ativo(
        $conexao,
        (int) $_SESSION["id_administrador_plataforma"],
    );
    mysqli_close($conexao);

    if ($contexto === false) {
        encerrar_sessao();
        return false;
    }

    $_SESSION["ultima_atividade_administrativa"] = time();

    return $contexto;
}

function exigir_autenticacao_administrativa()
{
    $contexto = obter_contexto_administrativo();

    if ($contexto === false) {
        header("Location: /administracao/entrar");
        exit();
    }

    return $contexto;
}
