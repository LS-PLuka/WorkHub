<?php

$versaoEstilos = filemtime(__DIR__ . "/../public/assets/css/app.css");
$modoAdministracao = ($modoCabecalho ?? "") === "administracao";
$classeLanding = ($paginaLanding ?? false) ? " workhub-public-body--landing" : "";

?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, "UTF-8") ?></title>
    <link rel="icon" type="image/png" href="/assets/images/logo-sem-fundo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css?v=<?= (int) $versaoEstilos ?>" rel="stylesheet">
</head>
<body class="workhub-public-body<?= $modoAdministracao ? " workhub-public-body--admin" : "" ?><?= $classeLanding ?>">
    <header class="workhub-public-header<?= $modoAdministracao ? " workhub-public-header--admin" : "" ?>">
        <a class="workhub-public-brand" href="/" aria-label="WorkHub - Página inicial">
            <img src="/assets/images/logo-sem-fundo.png" alt="WorkHub">
            <?php if (!$modoAdministracao): ?>
                <small>Gestão empresarial</small>
            <?php endif; ?>
        </a>
        <?php if ($modoAdministracao): ?>
            <a class="workhub-public-header__link" href="/">Voltar ao site</a>
        <?php else: ?>
            <nav class="workhub-public-nav" aria-label="Navegação pública">
                <a href="/#funcionalidades">Funcionalidades</a>
                <a href="/#como-funciona">Como funciona</a>
                <a href="/#para-quem">Para quem é</a>
            </nav>
            <div class="workhub-public-header__actions">
                <a class="workhub-public-header__link" href="/entrar">Entrar</a>
                <a class="btn btn-primary btn-sm" href="/cadastro-empresa">Cadastrar empresa</a>
            </div>
        <?php endif; ?>
    </header>
    <main class="workhub-public-main<?= $modoAdministracao ? " workhub-public-main--admin" : "" ?>">
