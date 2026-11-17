<?php
require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../model/cliente.php";
require_once __DIR__ . "/../model/pedido.php";
require_once __DIR__ . "/empresa_controller.php";
function validar_dados_cliente($dados)
{
    $erros = [];
    if ($dados["nome"] === "") {
        $erros[] = "Nome é obrigatório.";
    }
    if (strlen($dados["nome"]) > 150) {
        $erros[] = "Nome ultrapassa o tamanho permitido.";
    }
    if (!telefone_celular_eh_valido($dados["telefone"])) {
        $erros[] = "Informe um celular brasileiro válido no formato (99) 99999-9999.";
    }
    if (
        $dados["email"] !== "" &&
        (!email_tem_formato_valido($dados["email"]) || strlen($dados["email"]) > 150)
    ) {
        $erros[] = "E-mail inválido.";
    }
    return $erros;
}

function pode_gerenciar_clientes($contexto)
{
    return verificar_permissao(["PROPRIETARIO", "GERENTE"], $contexto);
}

function exibir_clientes()
{
    $contexto = exigir_autenticacao();
    $pesquisa = trim($_GET["pesquisa"] ?? "");
    $conexao = conectar_banco_dados();
    $clientes = $conexao === false ? [] : listar_clientes_empresa($conexao, (int) $contexto["empresa_id"], $pesquisa);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    $tituloPagina = "Clientes | WorkHub";
    $podeGerenciar = pode_gerenciar_clientes($contexto);
    $mensagem = isset($_GET["cadastro"])
        ? "Cliente cadastrado com sucesso."
        : (isset($_GET["edicao"])
            ? "Cliente atualizado com sucesso."
            : "");
    require __DIR__ . "/../view/clientes.php";
}

function exibir_cadastro_cliente()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE"]);
    $tituloPagina = "Cadastrar cliente | WorkHub";
    $erros = [];
    $cliente = ["nome" => "", "telefone" => "", "email" => ""];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $cliente = [
            "nome" => trim($_POST["nome"] ?? ""),
            "telefone" => trim($_POST["telefone"] ?? ""),
            "email" => strtolower(trim($_POST["email"] ?? "")),
        ];
        if (!validar_token_csrf("cadastro_cliente", $_POST["token_csrf"] ?? "")) {
            $erros[] = "Não foi possível validar o envio do formulário.";
        }
        $erros = array_merge($erros, validar_dados_cliente($cliente));
        $cliente["telefone"] = normalizar_telefone($cliente["telefone"]);
        if (!$erros) {
            $conexao = conectar_banco_dados();
            $s =
                $conexao !== false &&
                cadastrar_cliente(
                    $conexao,
                    (int) $contexto["empresa_id"],
                    $cliente["nome"],
                    $cliente["telefone"],
                    $cliente["email"] === "" ? null : $cliente["email"],
                );
            if ($conexao !== false) {
                mysqli_close($conexao);
            }
            if ($s) {
                header("Location: /clientes?cadastro=1");
                exit();
            }
            $erros[] = "Não foi possível concluir o cadastro.";
        }
    }
    $tokenCsrf = gerar_token_csrf("cadastro_cliente");
    require __DIR__ . "/../view/cadastro_cliente.php";
}

function exibir_edicao_cliente()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE"]);
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    $conexao = conectar_banco_dados();
    $cliente = $id && $conexao !== false ? buscar_cliente_empresa($conexao, $id, (int) $contexto["empresa_id"]) : false;
    if ($cliente === false) {
        if ($conexao !== false) {
            mysqli_close($conexao);
        }
        http_response_code(404);
        echo "Cliente não encontrado.";
        return;
    }
    $tituloPagina = "Editar cliente | WorkHub";
    $erros = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $dados = [
            "nome" => trim($_POST["nome"] ?? ""),
            "telefone" => trim($_POST["telefone"] ?? ""),
            "email" => strtolower(trim($_POST["email"] ?? "")),
        ];
        if (!validar_token_csrf("edicao_cliente_" . $cliente["id"], $_POST["token_csrf"] ?? "")) {
            $erros[] = "Não foi possível validar o envio do formulário.";
        }
        $erros = array_merge($erros, validar_dados_cliente($dados));
        $dados["telefone"] = normalizar_telefone($dados["telefone"]);
        if (
            !$erros &&
            atualizar_cliente_empresa(
                $conexao,
                $cliente["id"],
                (int) $contexto["empresa_id"],
                $dados["nome"],
                $dados["telefone"],
                $dados["email"] === "" ? null : $dados["email"],
            )
        ) {
            mysqli_close($conexao);
            header("Location: /clientes?edicao=1");
            exit();
        }
        if (!$erros) {
            $erros[] = "Não foi possível atualizar o cliente.";
        }
        $cliente = array_merge($cliente, $dados);
    }
    mysqli_close($conexao);
    $tokenCsrf = gerar_token_csrf("edicao_cliente_" . $cliente["id"]);
    require __DIR__ . "/../view/edicao_cliente.php";
}

function alterar_situacao_cliente()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE"]);
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !validar_token_csrf("situacao_cliente", $_POST["token_csrf"] ?? "")) {
        http_response_code(403);
        echo "Solicitação inválida.";
        return;
    }
    $id = filter_input(INPUT_POST, "id_cliente", FILTER_VALIDATE_INT);
    $acao = $_POST["acao"] ?? "";
    if (!$id || !in_array($acao, ["ativar", "desativar"], true)) {
        http_response_code(404);
        echo "Cliente não encontrado.";
        return;
    }
    $conexao = conectar_banco_dados();
    $cliente = $conexao === false ? false : buscar_cliente_empresa($conexao, $id, (int) $contexto["empresa_id"]);
    if ($cliente === false) {
        if ($conexao !== false) {
            mysqli_close($conexao);
        }
        http_response_code(404);
        echo "Cliente não encontrado.";
        return;
    }
    alterar_situacao_cliente_empresa($conexao, $id, (int) $contexto["empresa_id"], $acao === "ativar" ? 1 : 0);
    mysqli_close($conexao);
    header("Location: /clientes");
    exit();
}

function exibir_detalhes_cliente()
{
    $contexto = exigir_autenticacao();
    $idCliente = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $conexao = conectar_banco_dados();
    $cliente = $idCliente && $conexao !== false
        ? buscar_cliente_empresa($conexao, $idCliente, (int) $contexto["empresa_id"])
        : false;
    $pedidos = $cliente === false || $conexao === false
        ? []
        : listar_pedidos_cliente_empresa($conexao, $idCliente, (int) $contexto["empresa_id"]);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    if ($cliente === false) {
        http_response_code(404);
        echo "Cliente não encontrado.";
        return;
    }
    $tituloPagina = "Histórico do cliente | WorkHub";
    require __DIR__ . "/../view/detalhes_cliente.php";
}
