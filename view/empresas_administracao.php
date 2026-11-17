<?php

$paginaAtualAdministracao = "empresas";
require __DIR__ . "/layout_administracao_inicio.php";

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Administração global</p>
    <h1>Empresas cadastradas</h1>
    <p>Consulte os dados cadastrais e a situação de cada empresa.</p>
</header>

<?php if (isset($_GET["sucesso"])): ?>
    <div class="alert alert-success" role="alert">Situação da empresa alterada com sucesso.</div>
<?php endif; ?>
<?php if (isset($_GET["erro"])): ?>
    <div class="alert alert-danger" role="alert"><?= escapar_html($_GET["erro"]) ?></div>
<?php endif; ?>

<section class="workhub-panel workhub-admin-companies">
    <div class="workhub-admin-companies__toolbar">
        <div>
            <h2>Consulta de empresas</h2>
            <p>Pesquise por nome, razão social ou CNPJ e filtre por situação.</p>
        </div>
    </div>
    <form class="row g-3 align-items-end workhub-admin-filters" method="get" action="/administracao/empresas">
        <div class="col-md-6">
            <label class="form-label" for="pesquisa">Pesquisar</label>
            <input class="form-control" id="pesquisa" name="pesquisa" value="<?= escapar_html($pesquisa) ?>"
                placeholder="Nome fantasia, razão social ou CNPJ">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="situacao">Situação</label>
            <select class="form-select" id="situacao" name="situacao">
                <option value="">Todas as situações</option>
                <option value="1" <?= $situacao === "1" ? "selected" : "" ?>>Ativas</option>
                <option value="0" <?= $situacao === "0" ? "selected" : "" ?>>Desativadas</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100" type="submit">Aplicar filtros</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome fantasia</th>
                    <th>Razão social</th>
                    <th>CNPJ</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Cadastro</th>
                    <th>Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empresas as $empresa): ?>
                    <tr>
                        <td class="fw-semibold">#<?= (int) $empresa["id"] ?></td>
                        <td><strong><?= escapar_html($empresa["nome_fantasia"]) ?></strong></td>
                        <td><?= escapar_html($empresa["razao_social"]) ?></td>
                        <td><?= escapar_html($empresa["cnpj"]) ?></td>
                        <td><?= escapar_html($empresa["email"]) ?></td>
                        <td><?= escapar_html($empresa["telefone"]) ?></td>
                        <td><?= escapar_html(date("d/m/Y H:i", strtotime($empresa["data_cadastro"]))) ?></td>
                        <td>
                            <span class="workhub-status <?= (int) $empresa["ativo"] === 1 ? "workhub-status--success" : "" ?>">
                                <?= (int) $empresa["ativo"] === 1 ? "Ativa" : "Desativada" ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="workhub-table-actions">
                                <a class="btn btn-sm btn-outline-primary" href="/administracao/empresas/detalhes?id=<?= (int) $empresa["id"] ?>">
                                    Consultar
                                </a>
                                <form method="post" action="/administracao/empresas/situacao">
                                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSituacao) ?>">
                                    <input type="hidden" name="id_empresa" value="<?= (int) $empresa["id"] ?>">
                                    <input type="hidden" name="ativo" value="<?= (int) $empresa["ativo"] === 1 ? 0 : 1 ?>">
                                    <button class="btn btn-sm btn-outline-<?= (int) $empresa["ativo"] === 1 ? "danger" : "success" ?>"
                                        type="submit" title="A alteração de situação afeta o acesso empresarial.">
                                        <?= (int) $empresa["ativo"] === 1 ? "Desativar" : "Ativar" ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$empresas): ?>
                    <tr>
                        <td class="workhub-empty-state" colspan="9">
                            Nenhuma empresa encontrada para os filtros informados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_administracao_fim.php"; ?>
