<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar_html($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <section class="card border-0 shadow-sm w-100" style="max-width: 28rem;">
            <div class="card-body p-4">
                <h1 class="h3 mb-2">Entrar no WorkHub</h1>
                <p class="text-secondary mb-4">Acesse a gestão da sua empresa.</p>
                <?php if ($mensagemSaida): ?><div class="alert alert-success">Sessão encerrada com sucesso.</div><?php endif; ?>
                <?php if ($erros): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erros as $erro): ?><li><?= escapar_html($erro) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                <form method="post" action="/entrar">
                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
                    <div class="mb-3"><label class="form-label" for="email_usuario">E-mail</label><input class="form-control" id="email_usuario" name="email_usuario" type="email" maxlength="150" required value="<?= escapar_html($emailUsuario) ?>" autocomplete="email"></div>
                    <div class="mb-4"><label class="form-label" for="senha">Senha</label><input class="form-control" id="senha" name="senha" type="password" required autocomplete="current-password"></div>
                    <button class="btn btn-primary w-100" type="submit">Entrar</button>
                </form>
                <p class="text-center mt-3 mb-0"><a href="/cadastro-empresa">Cadastrar empresa</a></p>
            </div>
        </section>
    </main>
</body>
</html>

