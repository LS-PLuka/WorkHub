<?php

require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../model/pedido.php";
require_once __DIR__ . "/pedido_controller.php";

function exibir_producao()
{
    $contexto = exigir_permissao(["PROPRIETARIO", "GERENTE", "FUNCIONARIO"]);
    $conexao = conectar_banco_dados();
    $pedidos = $conexao === false ? [] : listar_pedidos_producao_empresa($conexao, (int) $contexto["empresa_id"]);
    if ($conexao !== false) {
        mysqli_close($conexao);
    }
    foreach ($pedidos as &$pedido) {
        $pedido["indicador_prazo"] = indicador_prazo_pedido($pedido);
        $pedido["status_permitidos"] = listar_status_pedido_permitidos($pedido["status"]);
    }
    unset($pedido);
    $tituloPagina = "Acompanhamento da produção | WorkHub";
    $podeCancelar = pode_gerenciar_pedido($contexto);
    $tokenCsrf = gerar_token_csrf("status_pedido");
    require __DIR__ . "/../view/producao.php";
}
