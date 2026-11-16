<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar_html($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <main class="container py-5">
        <section class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <p class="text-primary text-uppercase fw-semibold mb-2"><?= escapar_html($contexto['nome_empresa']) ?></p>
                <h1 class="h2">Olá, <?= escapar_html($contexto['nome_usuario']) ?>.</h1>
                <p class="text-secondary">Você está conectado como <?= escapar_html($contexto['perfil']) ?>.</p>
                <p>Bem-vindo ao WorkHub.</p>
                <a class="btn btn-outline-primary mb-3" href="/produtos">Produtos</a>
                <a class="btn btn-outline-primary mb-3" href="/clientes">Clientes</a>
                <a class="btn btn-outline-primary mb-3" href="/pedidos">Pedidos</a>
                <?php if ($contexto['perfil'] === 'PROPRIETARIO'): ?><a class="btn btn-primary mb-3" href="/usuarios">Gerenciar usuários</a><?php endif; ?>
                <form method="post" action="/sair">
                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSaida) ?>">
                    <button class="btn btn-outline-danger" type="submit">Sair</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>

