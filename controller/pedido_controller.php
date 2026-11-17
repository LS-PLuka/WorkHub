<?php
require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../model/pedido.php";
require_once __DIR__ . "/empresa_controller.php";

function formatar_valor_pedido($valor)
{
    return number_format((float) $valor, 2, ",", ".");
}

function data_pedido_valida($data)
{
    $objeto = DateTime::createFromFormat("!Y-m-d", $data);
    return $objeto !== false && $objeto->format("Y-m-d") === $data;
}

function validar_itens_pedido($produtos, $quantidades)
{
    if (
        !is_array($produtos) ||
        !is_array($quantidades) ||
        count($produtos) === 0 ||
        count($produtos) !== count($quantidades)
    ) {
        return [[], ["Informe pelo menos um produto e sua quantidade."]];
    }
    $itens = [];
    $erros = [];
    $vistos = [];
    foreach ($produtos as $indice => $produto) {
        if (trim((string) $produto) === "" && trim((string) ($quantidades[$indice] ?? "")) === "") {
            continue;
        }
        $idProduto = filter_var($produto, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $quantidade = filter_var($quantidades[$indice] ?? null, FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1, "max_range" => 2147483647],
        ]);
        if ($idProduto === false || $quantidade === false) {
            $erros[] = "Cada item deve ter produto e quantidade positiva válidos.";
            continue;
        }
        if (isset($vistos[$idProduto])) {
            $erros[] = "Não informe o mesmo produto mais de uma vez.";
            continue;
        }
        $vistos[$idProduto] = true;
        $itens[] = ["produto_id" => $idProduto, "quantidade" => $quantidade];
    }
    if (!$itens && !$erros) {
        $erros[] = "Informe pelo menos um produto e sua quantidade.";
    }
    return [$itens, $erros];
}

function carregar_opcoes_pedido($idEmpresa)
{
    $conexao = conectar_banco_dados();
    if ($conexao === false) {
        return [[], []];
    }
    $clientes = listar_clientes_ativos_empresa($conexao, $idEmpresa);
    $produtos = listar_produtos_ativos_empresa($conexao, $idEmpresa);
    mysqli_close($conexao);
    return [$clientes, $produtos];
}

function exibir_cadastro_pedido()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE", "FUNCIONARIO"]);
    $tituloPagina = "Cadastrar pedido | WorkHub";
    $erros = [];
    $dadosPedido = ["cliente_id" => "", "data_entrega" => "", "produtos" => [""], "quantidades" => ["1"]];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $dadosPedido = [
            "cliente_id" => $_POST["cliente_id"] ?? "",
            "data_entrega" => trim($_POST["data_entrega"] ?? ""),
            "produtos" => $_POST["produto_id"] ?? [],
            "quantidades" => $_POST["quantidade"] ?? [],
        ];
        if (!validar_token_csrf("cadastro_pedido", $_POST["token_csrf"] ?? "")) {
            $erros[] = "Não foi possível validar o envio do formulário.";
        }
        $idCliente = filter_var($dadosPedido["cliente_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($idCliente === false) {
            $erros[] = "Selecione um cliente válido.";
        }
        if (!data_pedido_valida($dadosPedido["data_entrega"])) {
            $erros[] = "Informe uma data prevista de entrega válida.";
        }
        [$itens, $errosItens] = validar_itens_pedido($dadosPedido["produtos"], $dadosPedido["quantidades"]);
        $erros = array_merge($erros, $errosItens);
        if (!$erros) {
            $conexao = conectar_banco_dados();
            $resultado =
                $conexao === false
                    ? ["sucesso" => false, "erro" => "Não foi possível concluir o cadastro."]
                    : cadastrar_pedido(
                        $conexao,
                        (int) $contexto["empresa_id"],
                        (int) $contexto["id_usuario"],
                        $idCliente,
                        $dadosPedido["data_entrega"],
                        $itens,
                    );
            if ($conexao !== false) {
                mysqli_close($conexao);
            }
            if ($resultado["sucesso"]) {
                header("Location: /pedidos?cadastro=1");
                exit();
            }
            $erros[] = $resultado["erro"];
        }
    }
    [$clientes, $produtos] = carregar_opcoes_pedido((int) $contexto["empresa_id"]);
    $tokenCsrf = gerar_token_csrf("cadastro_pedido");
    require __DIR__ . "/../view/cadastro_pedido.php";
}

function exibir_pedidos()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE", "FUNCIONARIO"]);
    $filtros = [
        "id" => filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) ?: null,
        "status" => $_GET["status"] ?? "",
        "data_inicial" => trim($_GET["data_inicial"] ?? ""),
        "data_final" => trim($_GET["data_final"] ?? ""),
    ];
    $statusPermitidos = ["RECEBIDO", "EM_PRODUCAO", "PRONTO", "ENTREGUE", "CANCELADO"];
    if (!in_array($filtros["status"], $statusPermitidos, true)) {
        $filtros["status"] = "";
    }
    if ($filtros["data_inicial"] !== "" && !data_pedido_valida($filtros["data_inicial"])) {
        $filtros["data_inicial"] = "";
    }
    if ($filtros["data_final"] !== "" && !data_pedido_valida($filtros["data_final"])) {
        $filtros["data_final"] = "";
    }
    $conexao = conectar_banco_dados();
    $pedidos = $conexao === false ? [] : listar_pedidos_empresa($conexao, (int) $contexto["empresa_id"], $filtros);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    $tituloPagina = "Pedidos | WorkHub";
    $mensagem = isset($_GET["cadastro"]) ? "Pedido cadastrado com sucesso." : "";
    require __DIR__ . "/../view/pedidos.php";
}

function exibir_detalhes_pedido()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE", "FUNCIONARIO"]);
    $idPedido = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $conexao = conectar_banco_dados();
    $pedido =
        $idPedido && $conexao !== false
            ? buscar_pedido_por_id($conexao, $idPedido, (int) $contexto["empresa_id"])
            : false;
    $itens =
        $pedido === false || $conexao === false
            ? []
            : listar_itens_pedido_empresa($conexao, $idPedido, (int) $contexto["empresa_id"]);
    $historico =
        $pedido === false || $conexao === false
            ? []
            : listar_historico_pedido_empresa($conexao, $idPedido, (int) $contexto["empresa_id"]);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    if ($pedido === false) {
        http_response_code(404);
        echo "Pedido não encontrado.";
        return;
    }
    $tituloPagina = "Detalhes do pedido | WorkHub";
    $podeEditar = pode_gerenciar_pedido($contexto) && in_array($pedido["status"], ["RECEBIDO", "EM_PRODUCAO"], true);
    require __DIR__ . "/../view/detalhes_pedido.php";
}

function pode_gerenciar_pedido($contexto)
{
    return verificar_permissao(["PROPRIETARIO", "GERENTE"], $contexto);
}

function exibir_edicao_pedido()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE"]);
    $idPedido = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $conexao = conectar_banco_dados();
    $pedido = $idPedido && $conexao !== false
        ? buscar_pedido_por_id($conexao, $idPedido, (int) $contexto["empresa_id"])
        : false;
    if ($pedido === false || !in_array($pedido["status"], ["RECEBIDO", "EM_PRODUCAO"], true)) {
        if ($conexao !== false) {
            mysqli_close($conexao);
        }
        http_response_code(403);
        echo "Este pedido não pode ser editado.";
        return;
    }
    $itens = listar_itens_pedido_empresa($conexao, $idPedido, (int) $contexto["empresa_id"]);
    $erros = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $dadosPedido = [
            "cliente_id" => $_POST["cliente_id"] ?? "",
            "data_entrega" => trim($_POST["data_entrega"] ?? ""),
            "produtos" => $_POST["produto_id"] ?? [],
            "quantidades" => $_POST["quantidade"] ?? [],
        ];
        $acao = $_POST["acao"] ?? "salvar";
        if (!validar_token_csrf("edicao_pedido_" . $idPedido, $_POST["token_csrf"] ?? "")) {
            $erros[] = "Não foi possível validar o envio do formulário.";
        }
        foreach ($dadosPedido["produtos"] as $indice => $idProduto) {
            $itens[$indice]["produto_id"] = $idProduto;
            $itens[$indice]["quantidade"] = $dadosPedido["quantidades"][$indice] ?? "";
        }
        if ($acao === "adicionar_item") {
            if (!$erros) {
                $itens[] = ["produto_id" => "", "quantidade" => ""];
            }
        } elseif ($acao === "salvar") {
            $idCliente = filter_var($dadosPedido["cliente_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if ($idCliente === false || !data_pedido_valida($dadosPedido["data_entrega"])) {
                $erros[] = "Cliente ou data de entrega inválidos.";
            }
            [$itensNovos, $errosItens] = validar_itens_pedido($dadosPedido["produtos"], $dadosPedido["quantidades"]);
            $erros = array_merge($erros, $errosItens);
            if (!$erros) {
                $resultado = atualizar_pedido(
                    $conexao,
                    $idPedido,
                    (int) $contexto["empresa_id"],
                    $idCliente,
                    $dadosPedido["data_entrega"],
                    $itensNovos,
                );
                if ($resultado["sucesso"]) {
                    mysqli_close($conexao);
                    header("Location: /pedidos/detalhes?id=" . $idPedido . "&edicao=1");
                    exit();
                }
                $erros[] = $resultado["erro"];
            }
        } else {
            $erros[] = "Ação inválida.";
        }
        $pedido["cliente_id"] = $dadosPedido["cliente_id"];
        $pedido["data_entrega"] = $dadosPedido["data_entrega"];
    }
    $clientes = listar_clientes_ativos_empresa($conexao, (int) $contexto["empresa_id"]);
    $produtos = listar_produtos_ativos_empresa($conexao, (int) $contexto["empresa_id"]);
    mysqli_close($conexao);
    $tituloPagina = "Editar pedido | WorkHub";
    $tokenCsrf = gerar_token_csrf("edicao_pedido_" . $idPedido);
    require __DIR__ . "/../view/edicao_pedido.php";
}

function alterar_status_pedido_requisicao($cancelar = false)
{
    $perfis = $cancelar ? ["PROPRIETARIO", "GERENTE"] : ["PROPRIETARIO", "GERENTE", "FUNCIONARIO"];
    $contexto = exigir_permissao($perfis);
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !validar_token_csrf("status_pedido", $_POST["token_csrf"] ?? "")) {
        http_response_code(403);
        echo "Solicitação inválida.";
        return;
    }
    $idPedido = filter_input(INPUT_POST, "id_pedido", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $novoStatus = $cancelar ? "CANCELADO" : ($_POST["status"] ?? "");
    if ($idPedido === false || !in_array($novoStatus, ["EM_PRODUCAO", "PRONTO", "ENTREGUE", "CANCELADO"], true)) {
        http_response_code(400);
        echo "Dados inválidos.";
        return;
    }
    $conexao = conectar_banco_dados();
    $resultado = $conexao === false
        ? ["sucesso" => false, "erro" => "Não foi possível atualizar o pedido."]
        : alterar_status_pedido($conexao, $idPedido, (int) $contexto["empresa_id"], (int) $contexto["id_usuario"], $novoStatus, $cancelar);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    if (!$resultado["sucesso"]) {
        http_response_code(400);
        echo escapar_html($resultado["erro"]);
        return;
    }
    header("Location: /producao?status=1");
    exit();
}

function atualizar_status_pedido()
{
    alterar_status_pedido_requisicao();
}

function cancelar_pedido_requisicao()
{
    alterar_status_pedido_requisicao(true);
}
