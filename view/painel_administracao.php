<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar_html($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-4 py-md-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-primary text-uppercase fw-semibold mb-1">WorkHub</p>
                <h1 class="h2 mb-1">Administração da plataforma</h1>
                <p class="text-secondary mb-0">Administrador: <?= escapar_html($contextoAdministrador["nome"]) ?></p>
            </div>
            <form method="post" action="/administracao/sair">
                <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSaida) ?>">
                <button class="btn btn-outline-danger" type="submit">Sair</button>
            </form>
        </header>
        <nav class="mb-4" aria-label="Navegação administrativa">
            <a class="btn btn-primary" href="/administracao">Visão geral</a>
            <a class="btn btn-outline-primary" href="/administracao/empresas">Empresas cadastradas</a>
        </nav>
        <?php if ($indicadores === false): ?>
            <div class="alert alert-danger">Não foi possível carregar os indicadores administrativos.</div>
        <?php else: ?>
            <section class="row g-3" aria-label="Indicadores de empresas">
                <?php
                $cards = [
                    ["Total de empresas", $indicadores["total_empresas"], "text-primary"],
                    ["Empresas ativas", $indicadores["empresas_ativas"], "text-success"],
                    ["Empresas desativadas", $indicadores["empresas_desativadas"], "text-secondary"],
                ];
                foreach ($cards as [$rotulo, $valor, $classe]):
                ?>
                    <div class="col-md-4">
                        <article class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <p class="text-secondary mb-2"><?= escapar_html($rotulo) ?></p>
                                <p class="display-6 fw-semibold mb-0 <?= escapar_html($classe) ?>"><?= (int) $valor ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
