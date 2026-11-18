<?php

function ambiente_de_producao()
{
    return in_array(strtolower((string) getenv("APP_ENV")), ["production", "producao"], true);
}

function exibir_erro_interno()
{
    if (!headers_sent()) {
        http_response_code(500);
        header("Content-Type: text/html; charset=UTF-8");
    }

    require __DIR__ . "/../view/erro_interno.php";
}

function registrar_e_tratar_erro($tipo, $mensagem, $arquivo, $linha)
{
    error_log(sprintf("WorkHub [%s] %s em %s:%d", $tipo, $mensagem, $arquivo, $linha));

    if (ambiente_de_producao()) {
        exibir_erro_interno();
    }
}

function configurar_tratamento_de_erros()
{
    error_reporting(E_ALL);
    ini_set("log_errors", "1");

    if (!ambiente_de_producao()) {
        ini_set("display_errors", "1");
        return;
    }

    ini_set("display_errors", "0");
    set_exception_handler(function (Throwable $erro) {
        registrar_e_tratar_erro("excecao", $erro->getMessage(), $erro->getFile(), $erro->getLine());
    });
    set_error_handler(function ($nivel, $mensagem, $arquivo, $linha) {
        if (!(error_reporting() & $nivel)) {
            return false;
        }

        registrar_e_tratar_erro("php", $mensagem, $arquivo, $linha);
        return true;
    });
    register_shutdown_function(function () {
        $erro = error_get_last();
        $errosFatais = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if ($erro !== null && in_array($erro["type"], $errosFatais, true)) {
            registrar_e_tratar_erro("fatal", $erro["message"], $erro["file"], $erro["line"]);
        }
    });
}
