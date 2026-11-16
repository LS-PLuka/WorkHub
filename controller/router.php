<?php

function rotear_requisicao($uriRequisicao)
{
    $caminho = parse_url($uriRequisicao, PHP_URL_PATH);
    $caminho = rtrim($caminho, '/');

    if ($caminho === '') {
        require __DIR__ . '/inicio_controller.php';
        exibir_inicio();
        return;
    }

    if ($caminho === '/cadastro-empresa') {
        require __DIR__ . '/empresa_controller.php';
        exibir_cadastro_empresa();
        return;
    }

    if ($caminho === '/entrar') {
        require __DIR__ . '/autenticacao_controller.php';
        exibir_entrada();
        return;
    }

    if ($caminho === '/painel') {
        require __DIR__ . '/autenticacao_controller.php';
        exibir_painel();
        return;
    }

    if ($caminho === '/sair') {
        require __DIR__ . '/autenticacao_controller.php';
        sair();
        return;
    }

    if ($caminho === '/usuarios') {
        require __DIR__ . '/usuario_controller.php';
        exibir_usuarios();
        return;
    }

    if ($caminho === '/usuarios/cadastrar') {
        require __DIR__ . '/usuario_controller.php';
        exibir_cadastro_usuario();
        return;
    }

    if ($caminho === '/usuarios/editar') {
        require __DIR__ . '/usuario_controller.php';
        exibir_edicao_usuario();
        return;
    }

    if ($caminho === '/usuarios/situacao') {
        require __DIR__ . '/usuario_controller.php';
        alterar_situacao_usuario();
        return;
    }

    http_response_code(404);
    echo 'Página não encontrada.';
}
