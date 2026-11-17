<?php

if (!isset($tokenCsrfSaida)) {
    $tokenCsrfSaida = gerar_token_csrf("saida_administracao");
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar_html($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="workhub-body">
    <div class="workhub-admin-shell">
        <aside class="workhub-admin-sidebar" aria-label="Navegação administrativa">
            <div>
                <a class="workhub-brand" href="/administracao">WorkHub</a>
                <p>Administração da plataforma</p>
            </div>
            <nav class="workhub-sidebar__nav">
                <a class="workhub-nav-link <?= $paginaAtualAdministracao === "visao_geral" ? "is-active" : "" ?>" href="/administracao">
                    Visão geral
                </a>
                <a class="workhub-nav-link <?= $paginaAtualAdministracao === "empresas" ? "is-active" : "" ?>" href="/administracao/empresas">
                    Empresas
                </a>
            </nav>
            <div class="workhub-sidebar__footer">
                <p class="workhub-sidebar__company"><?= escapar_html($contextoAdministrador["nome"]) ?></p>
                <form method="post" action="/administracao/sair">
                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSaida) ?>">
                    <button class="workhub-logout" type="submit">Sair da administração</button>
                </form>
            </div>
        </aside>
        <div class="workhub-page">
            <header class="workhub-topbar">
                <div class="workhub-topbar__context">Administração global</div>
                <div class="workhub-user">
                    <span class="workhub-user__initial" aria-hidden="true"><?= escapar_html(
                        strtoupper(substr($contextoAdministrador["nome"], 0, 1)),
                    ) ?></span>
                    <span><strong><?= escapar_html($contextoAdministrador["nome"]) ?></strong><small>Administrador</small></span>
                </div>
            </header>
            <main class="workhub-content">
