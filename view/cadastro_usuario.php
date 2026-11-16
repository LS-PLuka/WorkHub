<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= escapar_html(
    $tituloPagina,
) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><main class="container py-5"><div class="row justify-content-center"><div class="col-md-7"><a href="/usuarios">← Voltar aos usuários</a><section class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3">Cadastrar usuário</h1><?php if (
    $erros
): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($erros as $erro): ?><li><?= escapar_html(
    $erro,
) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post"><input type="hidden" name="token_csrf" value="<?= escapar_html(
    $tokenCsrf,
) ?>"><div class="mb-3"><label class="form-label">Nome completo</label><input class="form-control" name="nome" maxlength="150" required value="<?= escapar_html(
    $dadosUsuario["nome"],
) ?>"></div><div class="mb-3"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email" maxlength="150" required value="<?= escapar_html(
    $dadosUsuario["email"],
) ?>"></div><div class="mb-3"><label class="form-label">Perfil</label><select class="form-select" name="perfil"><option value="GERENTE" <?= $dadosUsuario[
    "perfil"
] === "GERENTE"
    ? "selected"
    : "" ?>>Gerente</option><option value="FUNCIONARIO" <?= $dadosUsuario["perfil"] === "FUNCIONARIO"
    ? "selected"
    : "" ?>>Funcionário</option></select></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Senha</label><input class="form-control" type="password" name="senha" minlength="8" required></div><div class="col-md-6 mb-3"><label class="form-label">Confirmação de senha</label><input class="form-control" type="password" name="confirmacao_senha" minlength="8" required></div></div><button class="btn btn-primary">Cadastrar</button></form></div></section></div></div></main></body></html>

