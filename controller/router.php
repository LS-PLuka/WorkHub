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

    http_response_code(404);
    echo 'Página não encontrada.';
}
