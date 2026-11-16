<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= company_escape($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <a class="text-decoration-none" href="/">← Voltar para a página inicial</a>
                <section class="card border-0 shadow-sm mt-3">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="h2 mb-2">Cadastre sua empresa</h1>
                        <p class="text-secondary mb-4">Crie a empresa e o acesso do seu responsável proprietário.</p>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                Empresa cadastrada com sucesso. O acesso do Proprietário foi criado; o login estará disponível em uma próxima etapa.
                            </div>
                        <?php endif; ?>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="mb-1 fw-semibold">Revise os dados informados:</p>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= company_escape($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="/cadastro-empresa" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= company_escape($_SESSION['company_register_csrf']) ?>">

                            <h2 class="h5 border-bottom pb-2 mb-3">Dados da empresa</h2>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="nome_fantasia">Nome fantasia</label>
                                    <input class="form-control" id="nome_fantasia" name="nome_fantasia" maxlength="150" required value="<?= company_escape($form['nome_fantasia']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="razao_social">Razão social</label>
                                    <input class="form-control" id="razao_social" name="razao_social" maxlength="200" required value="<?= company_escape($form['razao_social']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="cnpj">CNPJ</label>
                                    <input class="form-control" id="cnpj" name="cnpj" inputmode="numeric" maxlength="18" required value="<?= company_escape($form['cnpj']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="telefone">Telefone</label>
                                    <input class="form-control" id="telefone" name="telefone" maxlength="20" required value="<?= company_escape($form['telefone']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="email_empresa">E-mail da empresa</label>
                                    <input class="form-control" id="email_empresa" name="email_empresa" type="email" maxlength="150" required value="<?= company_escape($form['email_empresa']) ?>">
                                </div>
                            </div>

                            <h2 class="h5 border-bottom pb-2 mb-3">Responsável proprietário</h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="nome_responsavel">Nome completo</label>
                                    <input class="form-control" id="nome_responsavel" name="nome_responsavel" maxlength="150" required value="<?= company_escape($form['nome_responsavel']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="email_responsavel">E-mail</label>
                                    <input class="form-control" id="email_responsavel" name="email_responsavel" type="email" maxlength="150" required value="<?= company_escape($form['email_responsavel']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="senha">Senha</label>
                                    <input class="form-control" id="senha" name="senha" type="password" minlength="8" required autocomplete="new-password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="confirmacao_senha">Confirmação de senha</label>
                                    <input class="form-control" id="confirmacao_senha" name="confirmacao_senha" type="password" minlength="8" required autocomplete="new-password">
                                </div>
                            </div>
                            <button class="btn btn-primary mt-4" type="submit">Cadastrar empresa</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
