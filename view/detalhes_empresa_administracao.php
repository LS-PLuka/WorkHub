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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 mb-0">Dados cadastrais da empresa</h1>
            <a class="btn btn-outline-primary" href="/administracao/empresas">Voltar</a>
        </div>
        <section class="card border-0 shadow-sm">
            <div class="card-body">
                <dl class="row mb-4">
                    <dt class="col-sm-3">ID</dt><dd class="col-sm-9"><?= (int) $empresa["id"] ?></dd>
                    <dt class="col-sm-3">Nome fantasia</dt><dd class="col-sm-9"><?= escapar_html($empresa["nome_fantasia"]) ?></dd>
                    <dt class="col-sm-3">Razão social</dt><dd class="col-sm-9"><?= escapar_html($empresa["razao_social"]) ?></dd>
                    <dt class="col-sm-3">CNPJ</dt><dd class="col-sm-9"><?= escapar_html($empresa["cnpj"]) ?></dd>
                    <dt class="col-sm-3">E-mail</dt><dd class="col-sm-9"><?= escapar_html($empresa["email"]) ?></dd>
                    <dt class="col-sm-3">Telefone</dt><dd class="col-sm-9"><?= escapar_html($empresa["telefone"]) ?></dd>
                    <dt class="col-sm-3">Cadastro</dt><dd class="col-sm-9"><?= escapar_html(date("d/m/Y H:i", strtotime($empresa["data_cadastro"]))) ?></dd>
                    <dt class="col-sm-3">Situação</dt><dd class="col-sm-9"><?= (int) $empresa["ativo"] === 1 ? "Ativa" : "Desativada" ?></dd>
                </dl>
                <form method="post" action="/administracao/empresas/situacao">
                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSituacao) ?>">
                    <input type="hidden" name="id_empresa" value="<?= (int) $empresa["id"] ?>">
                    <input type="hidden" name="ativo" value="<?= (int) $empresa["ativo"] === 1 ? 0 : 1 ?>">
                    <button class="btn <?= (int) $empresa["ativo"] === 1 ? "btn-danger" : "btn-success" ?>" type="submit">
                        <?= (int) $empresa["ativo"] === 1 ? "Desativar empresa" : "Ativar empresa" ?>
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
