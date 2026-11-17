<?php

require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../model/usuario.php";
require_once __DIR__ . "/empresa_controller.php";

function autenticar_usuario($emailUsuario, $senha, $conexaoExistente = null)
{
    $conexao = $conexaoExistente ?: conectar_banco_dados();
    $deveFecharConexao = $conexaoExistente === null;

    if ($conexao === false) {
        return false;
    }

    $dadosUsuario = buscar_usuario_por_email($conexao, $emailUsuario);
    if ($deveFecharConexao) {
        mysqli_close($conexao);
    }

    if (
        $dadosUsuario === false ||
        (int) $dadosUsuario["usuario_ativo"] !== 1 ||
        (int) $dadosUsuario["empresa_ativa"] !== 1 ||
        !password_verify($senha, $dadosUsuario["senha"])
    ) {
        return false;
    }

    iniciar_sessao_segura();
    session_regenerate_id(true);
    $_SESSION = [
        "id_usuario" => (int) $dadosUsuario["id"],
        "ultima_atividade" => time(),
    ];

    return true;
}

function exibir_entrada()
{
    iniciar_sessao_segura();

    if (obter_contexto_autenticado() !== false) {
        header("Location: /dashboard");
        exit();
    }

    $tituloPagina = "Entrar | WorkHub";
    $erros = [];
    $emailUsuario = "";
    $mensagemSaida = isset($_GET["saida"]);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $emailUsuario = trim($_POST["email_usuario"] ?? "");
        $senha = $_POST["senha"] ?? "";
        $tokenCsrf = $_POST["token_csrf"] ?? "";

        if (!validar_token_csrf("entrada", $tokenCsrf)) {
            $erros[] = "Não foi possível validar o envio do formulário. Atualize a página e tente novamente.";
        } elseif (filter_var($emailUsuario, FILTER_VALIDATE_EMAIL) === false || $senha === "") {
            $erros[] = "Informe e-mail e senha válidos.";
        } elseif (!autenticar_usuario(strtolower($emailUsuario), $senha)) {
            $erros[] = "E-mail ou senha inválidos.";
        } else {
            header("Location: /dashboard");
            exit();
        }
    }

    $tokenCsrf = gerar_token_csrf("entrada");
    require __DIR__ . "/../view/entrar.php";
}

function exibir_painel()
{
    require_once __DIR__ . "/dashboard_controller.php";
    exibir_dashboard();
}

function sair()
{
    iniciar_sessao_segura();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo "Método não permitido.";
        return;
    }

    if (!validar_token_csrf("saida", $_POST["token_csrf"] ?? "")) {
        http_response_code(403);
        echo "Solicitação inválida.";
        return;
    }

    encerrar_sessao();
    header("Location: /entrar?saida=1");
    exit();
}
