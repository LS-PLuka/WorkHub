<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, "UTF-8") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="workhub-public-body">
    <header class="workhub-public-header">
        <a class="workhub-public-brand" href="/" aria-label="WorkHub - Página inicial">
            <span>WorkHub</span>
            <small>Gestão empresarial</small>
        </a>
        <nav class="workhub-public-nav" aria-label="Navegação pública">
            <a href="/#funcionalidades">Funcionalidades</a>
            <a href="/#como-funciona">Como funciona</a>
            <a href="/#para-quem">Para quem é</a>
        </nav>
        <div class="workhub-public-header__actions">
            <a class="workhub-public-header__link" href="/entrar">Entrar</a>
            <a class="btn btn-primary btn-sm" href="/cadastro-empresa">Cadastrar empresa</a>
        </div>
    </header>
    <main class="workhub-public-main">
