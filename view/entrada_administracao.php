<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar_html($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <section class="card border-0 shadow-sm mx-auto" style="max-width: 480px;">
            <div class="card-body p-4 p-md-5">
                <p class="text-primary text-uppercase fw-semibold">WorkHub</p>
                <h1 class="h3 mb-3">Administração da plataforma</h1>
                <?php if ($mensagemSaida): ?>
                    <div class="alert alert-success">Sessão administrativa encerrada.</div>
                <?php endif; ?>
                <?php foreach ($erros as $erro): ?>
                    <div class="alert alert-danger"><?= escapar_html($erro) ?></div>
                <?php endforeach; ?>
                <form method="post" action="/administracao/entrar">
                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <input class="form-control" id="email" name="email" type="email" required
                            value="<?= escapar_html($email) ?>" autocomplete="email">
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="senha">Senha</label>
                        <input class="form-control" id="senha" name="senha" type="password" required
                            autocomplete="current-password">
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Entrar na administração</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
