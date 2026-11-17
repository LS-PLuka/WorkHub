<?php

require __DIR__ . "/../controller/dashboard_controller.php";
require_once __DIR__ . "/../model/usuario.php";

ob_start();

function afirmar_dashboard($condicao, $mensagem, $conexao)
{
    if (!$condicao) {
        mysqli_rollback($conexao);
        mysqli_close($conexao);
        fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
        exit(1);
    }
    echo "OK: {$mensagem}" . PHP_EOL;
}

function criar_empresa_dashboard($conexao, $sufixo, $letra)
{
    $base = str_pad((string) random_int(0, 999999999999), 12, "0", STR_PAD_LEFT);
    $pesosUm = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $pesosDois = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    foreach ($pesosUm as $indice => $peso) {
        $soma += (int) $base[$indice] * $peso;
    }
    $digitoUm = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    $soma = 0;
    foreach ($pesosDois as $indice => $peso) {
        $soma += (int) ($base . $digitoUm)[$indice] * $peso;
    }
    $digitoDois = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    $cnpj = $base . $digitoUm . $digitoDois;
    $nome = "Empresa {$letra} {$sufixo}";
    $email = strtolower($letra) . ".{$sufixo}@workhub.test";
    $telefone = "11999999999";
    $comando = mysqli_prepare(
        $conexao,
        "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES (?, ?, ?, ?, ?)",
    );
    mysqli_stmt_bind_param($comando, "sssss", $nome, $nome, $cnpj, $email, $telefone);
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}

function criar_usuario_dashboard($conexao, $idEmpresa, $perfil, $sufixo)
{
    $nome = "Usuário {$perfil}";
    $email = strtolower($perfil) . ".{$sufixo}.{$idEmpresa}@workhub.test";
    $senha = password_hash("Senha123!", PASSWORD_BCRYPT);
    $comando = mysqli_prepare(
        $conexao,
        "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES (?, ?, ?, ?, ?)",
    );
    mysqli_stmt_bind_param($comando, "issss", $idEmpresa, $nome, $email, $senha, $perfil);
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}

function criar_cliente_dashboard($conexao, $idEmpresa, $nome)
{
    $telefone = "11999999999";
    $comando = mysqli_prepare($conexao, "INSERT INTO clientes (empresa_id, nome, telefone) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($comando, "iss", $idEmpresa, $nome, $telefone);
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}

function criar_pedido_dashboard($conexao, $idEmpresa, $idCliente, $idUsuario, $criacao, $entrega, $status, $valor)
{
    $comando = mysqli_prepare(
        $conexao,
        "INSERT INTO pedidos
            (empresa_id, cliente_id, usuario_id, data_criacao, data_entrega, status, valor_total, ativo)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
    );
    mysqli_stmt_bind_param(
        $comando,
        "iiissss",
        $idEmpresa,
        $idCliente,
        $idUsuario,
        $criacao,
        $entrega,
        $status,
        $valor,
    );
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}

$conexao = conectar_banco_dados();
if ($conexao === false || !mysqli_begin_transaction($conexao)) {
    fwrite(STDERR, "FALHOU: não foi possível iniciar o teste de dashboard." . PHP_EOL);
    exit(1);
}

$sufixo = bin2hex(random_bytes(5));
$empresaA = criar_empresa_dashboard($conexao, $sufixo, "A");
$empresaB = criar_empresa_dashboard($conexao, $sufixo, "B");
$empresaVazia = criar_empresa_dashboard($conexao, $sufixo, "V");
$usuariosA = [];
foreach (["PROPRIETARIO", "GERENTE", "FUNCIONARIO"] as $perfil) {
    $usuariosA[$perfil] = criar_usuario_dashboard($conexao, $empresaA, $perfil, $sufixo);
}
$usuarioB = criar_usuario_dashboard($conexao, $empresaB, "PROPRIETARIO", $sufixo);
$clienteA = criar_cliente_dashboard($conexao, $empresaA, "Cliente A");
$clienteB = criar_cliente_dashboard($conexao, $empresaB, "Cliente B");
$hoje = new DateTimeImmutable("today");
$dataHoje = $hoje->format("Y-m-d");
$criacao = $dataHoje . " 12:00:00";

$pedidosPrincipais = [
    ["PROPRIETARIO", "+2 days", "EM_PRODUCAO", "10.00"],
    ["GERENTE", null, "PRONTO", "20.00"],
    ["FUNCIONARIO", "-1 day", "ENTREGUE", "30.00"],
    ["FUNCIONARIO", "-1 day", "CANCELADO", "40.00"],
    ["FUNCIONARIO", "-1 day", "RECEBIDO", "50.00"],
    ["FUNCIONARIO", "+3 days", "RECEBIDO", "60.00"],
];
$idsA = [];
foreach ($pedidosPrincipais as [$perfil, $modificadorData, $status, $valor]) {
    $entrega = $modificadorData === null ? $dataHoje : $hoje->modify($modificadorData)->format("Y-m-d");
    $idsA[] = criar_pedido_dashboard(
        $conexao,
        $empresaA,
        $clienteA,
        $usuariosA[$perfil],
        $criacao,
        $entrega,
        $status,
        $valor,
    );
}
for ($indice = 0; $indice < 11; $indice++) {
    $idsA[] = criar_pedido_dashboard(
        $conexao,
        $empresaA,
        $clienteA,
        $usuariosA["FUNCIONARIO"],
        $criacao,
        $hoje->modify("+10 days")->format("Y-m-d"),
        "RECEBIDO",
        "1.00",
    );
}
criar_pedido_dashboard($conexao, $empresaB, $clienteB, $usuarioB, $criacao, $dataHoje, "EM_PRODUCAO", "999.99");

foreach (["PROPRIETARIO", "GERENTE", "FUNCIONARIO"] as $perfil) {
    afirmar_dashboard(
        verificar_permissao([$perfil], ["perfil" => $perfil]),
        "dashboard é acessível ao perfil {$perfil}",
        $conexao,
    );
}
iniciar_sessao_segura();
$_SESSION = [];
afirmar_dashboard(!usuario_autenticado(), "usuário sem sessão não está autenticado", $conexao);

$indicadoresA = obter_indicadores_dashboard($conexao, $empresaA, $dataHoje, $dataHoje, $dataHoje);
afirmar_dashboard(
    (int) $indicadoresA["total_pedidos"] === 17 &&
        (int) $indicadoresA["pedidos_em_producao"] === 1 &&
        (int) $indicadoresA["pedidos_prontos"] === 1 &&
        (int) $indicadoresA["pedidos_entregues"] === 1 &&
        $indicadoresA["valor_total_periodo"] === "181.00",
    "totais, status, cancelados e soma monetária são calculados corretamente",
    $conexao,
);
$indicadoresB = obter_indicadores_dashboard($conexao, $empresaB, $dataHoje, $dataHoje, $dataHoje);
afirmar_dashboard(
    (int) $indicadoresB["total_pedidos"] === 1 && $indicadoresB["valor_total_periodo"] === "999.99",
    "empresa A não visualiza indicadores da empresa B",
    $conexao,
);
$indicadoresOutroPeriodo = obter_indicadores_dashboard(
    $conexao,
    $empresaA,
    $hoje->modify("-2 days")->format("Y-m-d"),
    $hoje->modify("-1 day")->format("Y-m-d"),
    $dataHoje,
);
afirmar_dashboard(
    $indicadoresOutroPeriodo["valor_total_periodo"] === "0.00",
    "filtro financeiro por período funciona",
    $conexao,
);
$periodoInvalido = obter_periodo_dashboard(["data_inicial" => "2026-12-02", "data_final" => "2026-12-01"]);
afirmar_dashboard($periodoInvalido["erros"] !== [], "intervalo de datas inválido é rejeitado", $conexao);

$recentes = listar_pedidos_recentes_dashboard($conexao, $empresaA);
afirmar_dashboard(
    count($recentes) === 10 &&
        (int) $recentes[0]["id"] === $idsA[16] &&
        (int) $recentes[9]["id"] === $idsA[7],
    "pedidos recentes respeitam ordem decrescente e limite de 10",
    $conexao,
);
$alertas = listar_alertas_prazo_dashboard($conexao, $empresaA, $dataHoje);
$situacoes = array_column($alertas, "situacao_prazo");
$idsAlertas = array_map("intval", array_column($alertas, "id"));
afirmar_dashboard(
    count($alertas) === 3 &&
        count(array_filter($situacoes, static fn ($situacao) => $situacao === "proximo")) === 2 &&
        count(array_filter($situacoes, static fn ($situacao) => $situacao === "atrasado")) === 1,
    "prazos próximos em até dois dias e atrasados são identificados",
    $conexao,
);
afirmar_dashboard(
    !in_array($idsA[2], $idsAlertas, true) &&
        !in_array($idsA[3], $idsAlertas, true),
    "pedidos entregues e cancelados não geram alertas",
    $conexao,
);
$indicadoresVazios = obter_indicadores_dashboard($conexao, $empresaVazia, $dataHoje, $dataHoje, $dataHoje);
afirmar_dashboard(
    (int) $indicadoresVazios["total_pedidos"] === 0 &&
        listar_pedidos_recentes_dashboard($conexao, $empresaVazia) === [] &&
        listar_alertas_prazo_dashboard($conexao, $empresaVazia, $dataHoje) === [],
    "dashboard vazio retorna indicadores e listas vazias",
    $conexao,
);

mysqli_rollback($conexao);
mysqli_close($conexao);
echo "OK: testes de dashboard concluídos sem manter dados fictícios." . PHP_EOL;
ob_end_flush();
