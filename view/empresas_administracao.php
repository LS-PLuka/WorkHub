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
        <header class="d-flex justify-content-between align-items-center gap-3 mb-4">
            <div>
                <p class="text-primary text-uppercase fw-semibold mb-1">WorkHub</p>
                <h1 class="h2 mb-0">Empresas cadastradas</h1>
            </div>
            <a class="btn btn-outline-primary" href="/administracao">Visão geral</a>
        </header>
        <?php if (isset($_GET["sucesso"])): ?>
            <div class="alert alert-success">Situação da empresa alterada com sucesso.</div>
        <?php endif; ?>
        <?php if (isset($_GET["erro"])): ?>
            <div class="alert alert-danger"><?= escapar_html($_GET["erro"]) ?></div>
        <?php endif; ?>
        <form class="row g-2 mb-4" method="get" action="/administracao/empresas">
            <div class="col-md-6">
                <label class="visually-hidden" for="pesquisa">Pesquisar</label>
                <input class="form-control" id="pesquisa" name="pesquisa" value="<?= escapar_html($pesquisa) ?>"
                    placeholder="Nome fantasia, razão social ou CNPJ">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="situacao">
                    <option value="">Todas as situações</option>
                    <option value="1" <?= $situacao === "1" ? "selected" : "" ?>>Ativas</option>
                    <option value="0" <?= $situacao === "0" ? "selected" : "" ?>>Desativadas</option>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-primary" type="submit">Filtrar</button></div>
        </form>
        <section class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr><th>ID</th><th>Nome fantasia</th><th>Razão social</th><th>CNPJ</th><th>E-mail</th><th>Telefone</th><th>Cadastro</th><th>Situação</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr>
                                <td><?= (int) $empresa["id"] ?></td>
                                <td><?= escapar_html($empresa["nome_fantasia"]) ?></td>
                                <td><?= escapar_html($empresa["razao_social"]) ?></td>
                                <td><?= escapar_html($empresa["cnpj"]) ?></td>
                                <td><?= escapar_html($empresa["email"]) ?></td>
                                <td><?= escapar_html($empresa["telefone"]) ?></td>
                                <td><?= escapar_html(date("d/m/Y H:i", strtotime($empresa["data_cadastro"]))) ?></td>
                                <td><?= (int) $empresa["ativo"] === 1 ? "Ativa" : "Desativada" ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="/administracao/empresas/detalhes?id=<?= (int) $empresa["id"] ?>">Consultar</a>
                                    <form class="d-inline" method="post" action="/administracao/empresas/situacao">
                                        <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSituacao) ?>">
                                        <input type="hidden" name="id_empresa" value="<?= (int) $empresa["id"] ?>">
                                        <input type="hidden" name="ativo" value="<?= (int) $empresa["ativo"] === 1 ? 0 : 1 ?>">
                                        <button class="btn btn-sm <?= (int) $empresa["ativo"] === 1 ? "btn-outline-danger" : "btn-outline-success" ?>" type="submit">
                                            <?= (int) $empresa["ativo"] === 1 ? "Desativar" : "Ativar" ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$empresas): ?>
                            <tr><td class="text-center text-secondary" colspan="9">Nenhuma empresa encontrada.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
