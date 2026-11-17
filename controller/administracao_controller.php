<?php

require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../model/administracao.php";
require_once __DIR__ . "/empresa_controller.php";

function autenticar_administrador($email, $senha, $conexaoExistente = null)
{
    $conexao = $conexaoExistente ?: conectar_banco_dados();
    $deveFecharConexao = $conexaoExistente === null;
    if ($conexao === false) {
        return false;
    }
    $administrador = buscar_administrador_por_email($conexao, $email);
    if ($deveFecharConexao) {
        mysqli_close($conexao);
    }
    if (
        $administrador === false ||
        (int) $administrador["ativo"] !== 1 ||
        !password_verify($senha, $administrador["senha"])
    ) {
        return false;
    }
    iniciar_sessao_segura();
    session_regenerate_id(true);
    $_SESSION = [
        "tipo_autenticacao" => "administrativo",
        "id_administrador_plataforma" => (int) $administrador["id"],
        "ultima_atividade_administrativa" => time(),
    ];
    return true;
}

function exibir_entrada_administracao()
{
    iniciar_sessao_segura();
    if (obter_contexto_administrativo() !== false) {
        header("Location: /administracao");
        exit();
    }
    $tituloPagina = "Entrar na administração | WorkHub";
    $erros = [];
    $email = "";
    $mensagemSaida = isset($_GET["saida"]);
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST["email"] ?? "");
        $senha = $_POST["senha"] ?? "";
        if (!validar_token_csrf("entrada_administracao", $_POST["token_csrf"] ?? "")) {
            $erros[] = "Não foi possível validar o envio do formulário. Atualize a página e tente novamente.";
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $senha === "") {
            $erros[] = "E-mail ou senha inválidos.";
        } elseif (!autenticar_administrador(strtolower($email), $senha)) {
            $erros[] = "E-mail ou senha inválidos.";
        } else {
            header("Location: /administracao");
            exit();
        }
    }
    $tokenCsrf = gerar_token_csrf("entrada_administracao");
    require __DIR__ . "/../view/entrada_administracao.php";
}

function exibir_painel_administracao()
{
    $contextoAdministrador = exigir_autenticacao_administrativa();
    $conexao = conectar_banco_dados();
    $indicadores = $conexao === false ? false : obter_indicadores_administracao($conexao);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    $tituloPagina = "Administração da plataforma | WorkHub";
    $tokenCsrfSaida = gerar_token_csrf("saida_administracao");
    require __DIR__ . "/../view/painel_administracao.php";
}

function exibir_empresas_administracao()
{
    $contextoAdministrador = exigir_autenticacao_administrativa();
    $pesquisa = trim((string) ($_GET["pesquisa"] ?? ""));
    $situacao = (string) ($_GET["situacao"] ?? "");
    if (!in_array($situacao, ["", "0", "1"], true)) {
        $situacao = "";
    }
    $conexao = conectar_banco_dados();
    $empresas = $conexao === false ? [] : listar_empresas_administracao($conexao, $pesquisa, $situacao);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    $tituloPagina = "Empresas cadastradas | Administração WorkHub";
    $tokenCsrfSituacao = gerar_token_csrf("situacao_empresa_administracao");
    require __DIR__ . "/../view/empresas_administracao.php";
}

function exibir_detalhes_empresa_administracao()
{
    $contextoAdministrador = exigir_autenticacao_administrativa();
    $idEmpresa = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $conexao = conectar_banco_dados();
    $empresa = $idEmpresa === false || $conexao === false ? false : buscar_empresa_administracao($conexao, $idEmpresa);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    if ($empresa === false) {
        http_response_code(404);
        echo "Empresa não encontrada.";
        return;
    }
    $tituloPagina = "Dados da empresa | Administração WorkHub";
    $tokenCsrfSituacao = gerar_token_csrf("situacao_empresa_administracao");
    require __DIR__ . "/../view/detalhes_empresa_administracao.php";
}

function alterar_situacao_empresa_administracao_requisicao()
{
    exigir_autenticacao_administrativa();
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo "Método não permitido.";
        return;
    }
    if (!validar_token_csrf("situacao_empresa_administracao", $_POST["token_csrf"] ?? "")) {
        http_response_code(403);
        echo "Solicitação inválida.";
        return;
    }
    $idEmpresa = filter_input(INPUT_POST, "id_empresa", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $ativo = filter_input(INPUT_POST, "ativo", FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
    if ($idEmpresa === false || $ativo === false) {
        http_response_code(400);
        echo "Dados inválidos.";
        return;
    }
    $conexao = conectar_banco_dados();
    $resultado = $conexao === false
        ? ["sucesso" => false, "erro" => "Não foi possível alterar a situação da empresa."]
        : alterar_situacao_empresa_administracao($conexao, $idEmpresa, $ativo);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    if (!$resultado["sucesso"]) {
        header("Location: /administracao/empresas?erro=" . urlencode($resultado["erro"]));
        exit();
    }
    header("Location: /administracao/empresas?sucesso=1");
    exit();
}

function sair_administracao()
{
    exigir_autenticacao_administrativa();
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo "Método não permitido.";
        return;
    }
    if (!validar_token_csrf("saida_administracao", $_POST["token_csrf"] ?? "")) {
        http_response_code(403);
        echo "Solicitação inválida.";
        return;
    }
    encerrar_sessao();
    header("Location: /administracao/entrar?saida=1");
    exit();
}
