<?php

function rotear_requisicao($uriRequisicao)
{
    $caminho = parse_url($uriRequisicao, PHP_URL_PATH);
    $caminho = rtrim($caminho, "/");

    if ($caminho === "") {
        require __DIR__ . "/inicio_controller.php";
        exibir_inicio();
        return;
    }

    if ($caminho === "/cadastro-empresa") {
        require __DIR__ . "/empresa_controller.php";
        exibir_cadastro_empresa();
        return;
    }

    if ($caminho === "/entrar") {
        require __DIR__ . "/autenticacao_controller.php";
        exibir_entrada();
        return;
    }

    if ($caminho === "/administracao/entrar") {
        require __DIR__ . "/administracao_controller.php";
        exibir_entrada_administracao();
        return;
    }

    if ($caminho === "/administracao") {
        require __DIR__ . "/administracao_controller.php";
        exibir_painel_administracao();
        return;
    }

    if ($caminho === "/administracao/empresas") {
        require __DIR__ . "/administracao_controller.php";
        exibir_empresas_administracao();
        return;
    }

    if ($caminho === "/administracao/empresas/detalhes") {
        require __DIR__ . "/administracao_controller.php";
        exibir_detalhes_empresa_administracao();
        return;
    }

    if ($caminho === "/administracao/empresas/situacao") {
        require __DIR__ . "/administracao_controller.php";
        alterar_situacao_empresa_administracao_requisicao();
        return;
    }

    if ($caminho === "/administracao/sair") {
        require __DIR__ . "/administracao_controller.php";
        sair_administracao();
        return;
    }

    if ($caminho === "/painel" || $caminho === "/dashboard") {
        require __DIR__ . "/dashboard_controller.php";
        exibir_dashboard();
        return;
    }

    if ($caminho === "/minha-empresa") {
        require __DIR__ . "/empresa_controller.php";
        exibir_minha_empresa();
        return;
    }

    if ($caminho === "/sair") {
        require __DIR__ . "/autenticacao_controller.php";
        sair();
        return;
    }

    if ($caminho === "/usuarios") {
        require __DIR__ . "/usuario_controller.php";
        exibir_usuarios();
        return;
    }

    if ($caminho === "/usuarios/cadastrar") {
        require __DIR__ . "/usuario_controller.php";
        exibir_cadastro_usuario();
        return;
    }

    if ($caminho === "/usuarios/editar") {
        require __DIR__ . "/usuario_controller.php";
        exibir_edicao_usuario();
        return;
    }

    if ($caminho === "/usuarios/situacao") {
        require __DIR__ . "/usuario_controller.php";
        alterar_situacao_usuario();
        return;
    }

    if ($caminho === "/produtos") {
        require __DIR__ . "/produto_controller.php";
        exibir_produtos();
        return;
    }
    if ($caminho === "/produtos/cadastrar") {
        require __DIR__ . "/produto_controller.php";
        exibir_cadastro_produto();
        return;
    }
    if ($caminho === "/produtos/editar") {
        require __DIR__ . "/produto_controller.php";
        exibir_edicao_produto();
        return;
    }
    if ($caminho === "/produtos/situacao") {
        require __DIR__ . "/produto_controller.php";
        alterar_situacao_produto();
        return;
    }
    if ($caminho === "/clientes") {
        require __DIR__ . "/cliente_controller.php";
        exibir_clientes();
        return;
    }
    if ($caminho === "/clientes/cadastrar") {
        require __DIR__ . "/cliente_controller.php";
        exibir_cadastro_cliente();
        return;
    }
    if ($caminho === "/clientes/editar") {
        require __DIR__ . "/cliente_controller.php";
        exibir_edicao_cliente();
        return;
    }
    if ($caminho === "/clientes/detalhes") {
        require __DIR__ . "/cliente_controller.php";
        exibir_detalhes_cliente();
        return;
    }
    if ($caminho === "/clientes/situacao") {
        require __DIR__ . "/cliente_controller.php";
        alterar_situacao_cliente();
        return;
    }
    if ($caminho === "/pedidos") {
        require __DIR__ . "/pedido_controller.php";
        exibir_pedidos();
        return;
    }
    if ($caminho === "/pedidos/cadastrar") {
        require __DIR__ . "/pedido_controller.php";
        exibir_cadastro_pedido();
        return;
    }
    if ($caminho === "/pedidos/detalhes") {
        require __DIR__ . "/pedido_controller.php";
        exibir_detalhes_pedido();
        return;
    }
    if ($caminho === "/pedidos/editar") {
        require __DIR__ . "/pedido_controller.php";
        exibir_edicao_pedido();
        return;
    }
    if ($caminho === "/pedidos/status") {
        require __DIR__ . "/pedido_controller.php";
        atualizar_status_pedido();
        return;
    }
    if ($caminho === "/pedidos/cancelar") {
        require __DIR__ . "/pedido_controller.php";
        cancelar_pedido_requisicao();
        return;
    }
    if ($caminho === "/producao") {
        require __DIR__ . "/producao_controller.php";
        exibir_producao();
        return;
    }

    http_response_code(404);
    echo "Página não encontrada.";
}
