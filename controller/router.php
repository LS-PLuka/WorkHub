<?php

function route_request($requestUri)
{
    $path = parse_url($requestUri, PHP_URL_PATH);
    $path = rtrim($path, '/');

    if ($path === '') {
        require __DIR__ . '/home_controller.php';
        show_home();
        return;
    }

    if ($path === '/cadastro-empresa') {
        require __DIR__ . '/company_controller.php';
        company_register_show();
        return;
    }

    http_response_code(404);
    echo 'Página não encontrada.';
}
