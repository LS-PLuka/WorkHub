<?php

if (!isset($tokenCsrfSaida)) {
    $tokenCsrfSaida = gerar_token_csrf("saida");
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
    <div class="workhub-app-shell">
        <aside class="workhub-sidebar" id="menu-principal" aria-label="Navegação principal">
            <div class="workhub-sidebar__brand">
                <a class="workhub-brand" href="/painel" aria-label="WorkHub - Dashboard">WorkHub</a>
                <p>Gestão empresarial</p>
            </div>

            <nav class="workhub-sidebar__nav">
                <a class="workhub-nav-link <?= $paginaAtual === "dashboard" ? "is-active" : "" ?>" href="/painel">
                    Dashboard
                </a>
                <?php if ($contexto["perfil"] === "PROPRIETARIO"): ?>
                    <a class="workhub-nav-link <?= $paginaAtual === "usuarios" ? "is-active" : "" ?>" href="/usuarios">
                        Usuários
                    </a>
                <?php endif; ?>
                <a class="workhub-nav-link <?= $paginaAtual === "produtos" ? "is-active" : "" ?>" href="/produtos">
                    Produtos
                </a>
                <a class="workhub-nav-link <?= $paginaAtual === "clientes" ? "is-active" : "" ?>" href="/clientes">
                    Clientes
                </a>
                <a class="workhub-nav-link <?= $paginaAtual === "pedidos" ? "is-active" : "" ?>" href="/pedidos">
                    Pedidos
                </a>
                <a class="workhub-nav-link <?= $paginaAtual === "producao" ? "is-active" : "" ?>" href="/producao">
                    Produção
                </a>
            </nav>

            <div class="workhub-sidebar__footer">
                <p class="workhub-sidebar__company"><?= escapar_html($contexto["nome_empresa"]) ?></p>
                <form method="post" action="/sair">
                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSaida) ?>">
                    <button class="workhub-logout" type="submit">Sair da conta</button>
                </form>
            </div>
        </aside>

        <div class="workhub-page">
            <header class="workhub-topbar">
                <button class="workhub-menu-toggle" type="button" aria-controls="menu-principal" aria-expanded="false">
                    Menu
                </button>
                <div class="workhub-topbar__context">
                    <span><?= escapar_html($contexto["nome_empresa"]) ?></span>
                </div>
                <div class="workhub-user">
                    <span class="workhub-user__initial" aria-hidden="true"><?= escapar_html(
                        strtoupper(substr($contexto["nome_usuario"], 0, 1)),
                    ) ?></span>
                    <span>
                        <strong><?= escapar_html($contexto["nome_usuario"]) ?></strong>
                        <small><?= escapar_html(ucfirst(strtolower($contexto["perfil"]))) ?></small>
                    </span>
                </div>
            </header>

            <main class="workhub-content">
