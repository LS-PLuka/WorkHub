<?php

require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../model/dashboard.php";
require_once __DIR__ . "/empresa_controller.php";

function data_dashboard_valida($data)
{
    $objeto = DateTimeImmutable::createFromFormat("!Y-m-d", $data);
    return $objeto !== false && $objeto->format("Y-m-d") === $data;
}

function obter_periodo_dashboard($dados)
{
    $hoje = new DateTimeImmutable("today");
    $periodoPadrao = [
        "data_inicial" => $hoje->modify("first day of this month")->format("Y-m-d"),
        "data_final" => $hoje->modify("last day of this month")->format("Y-m-d"),
    ];
    $dataInicial = trim((string) ($dados["data_inicial"] ?? ""));
    $dataFinal = trim((string) ($dados["data_final"] ?? ""));
    if ($dataInicial === "" && $dataFinal === "") {
        return ["periodo" => $periodoPadrao, "erros" => []];
    }
    $erros = [];
    if (!data_dashboard_valida($dataInicial)) {
        $erros[] = "Informe uma data inicial válida.";
    }
    if (!data_dashboard_valida($dataFinal)) {
        $erros[] = "Informe uma data final válida.";
    }
    if (!$erros && $dataInicial > $dataFinal) {
        $erros[] = "A data inicial não pode ser posterior à data final.";
    }
    return [
        "periodo" => $erros ? $periodoPadrao : ["data_inicial" => $dataInicial, "data_final" => $dataFinal],
        "erros" => $erros,
    ];
}

function formatar_valor_dashboard($valor)
{
    $valor = (string) $valor;
    if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $valor, $partes)) {
        return "0,00";
    }
    $inteiro = $partes[1];
    $centavos = str_pad($partes[2] ?? "", 2, "0");
    return number_format((int) $inteiro, 0, ",", ".") . "," . $centavos;
}

function exibir_dashboard()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE", "FUNCIONARIO"]);
    $resultadoPeriodo = obter_periodo_dashboard($_GET);
    $periodo = $resultadoPeriodo["periodo"];
    $erros = $resultadoPeriodo["erros"];
    $dataAtual = (new DateTimeImmutable("today"))->format("Y-m-d");
    $indicadores = ["total_pedidos" => 0, "pedidos_em_producao" => 0, "pedidos_prontos" => 0,
        "pedidos_entregues" => 0, "pedidos_proximos_vencimento" => 0, "pedidos_atrasados" => 0,
        "valor_total_periodo" => "0.00"];
    $pedidosRecentes = [];
    $alertasPrazo = [];
    $conexao = conectar_banco_dados();
    if ($conexao === false) {
        $erros[] = "Não foi possível carregar os dados do dashboard.";
    } else {
        $dadosIndicadores = obter_indicadores_dashboard(
            $conexao,
            (int) $contexto["empresa_id"],
            $periodo["data_inicial"],
            $periodo["data_final"],
            $dataAtual,
        );
        if ($dadosIndicadores === false) {
            $erros[] = "Não foi possível carregar os indicadores do dashboard.";
        } else {
            $indicadores = $dadosIndicadores;
        }
        $pedidosRecentes = listar_pedidos_recentes_dashboard($conexao, (int) $contexto["empresa_id"]);
        $alertasPrazo = listar_alertas_prazo_dashboard($conexao, (int) $contexto["empresa_id"], $dataAtual);
        mysqli_close($conexao);
    }
    $tituloPagina = "Dashboard | WorkHub";
    $tokenCsrfSaida = gerar_token_csrf("saida");
    require __DIR__ . "/../view/painel.php";
}
