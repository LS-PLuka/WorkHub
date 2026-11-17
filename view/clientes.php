<?php

$paginaAtual = "clientes";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Relacionamento</p>
        <h1>Clientes</h1>
        <p>Consulte os dados de contato e mantenha a base de clientes atualizada.</p>
    </div>
    <?php if ($podeGerenciar): ?>
        <a class="btn btn-primary" href="/clientes/cadastrar">Novo cliente</a>
    <?php endif; ?>
</header>

<?php if ($mensagem): ?>
    <div class="alert alert-success" role="alert"><?= escapar_html($mensagem) ?></div>
<?php endif; ?>

<section class="workhub-panel">
    <form class="workhub-toolbar" method="get" action="/clientes">
        <div class="workhub-toolbar__field">
            <label class="visually-hidden" for="pesquisa">Buscar por nome</label>
            <input class="form-control" id="pesquisa" name="pesquisa" value="<?= escapar_html($pesquisa) ?>"
                placeholder="Buscar por nome">
        </div>
        <button class="btn btn-outline-primary" type="submit">Buscar</button>
    </form>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr><th>Nome</th><th>Telefone</th><th>E-mail</th><th>Situação</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><strong><?= escapar_html($cliente["nome"]) ?></strong></td>
                        <td><?= escapar_html(formatar_telefone($cliente["telefone"])) ?></td>
                        <td><?= escapar_html($cliente["email"] ?? "") ?></td>
                        <td>
                            <span class="workhub-status <?= $cliente["ativo"] ? "workhub-status--success" : "" ?>">
                                <?= $cliente["ativo"] ? "Ativo" : "Inativo" ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="workhub-table-actions">
                                <a class="btn btn-sm btn-outline-primary" href="/clientes/detalhes?id=<?= (int) $cliente["id"] ?>">
                                    Histórico
                                </a>
                                <?php if ($podeGerenciar): ?>
                                    <a class="btn btn-sm btn-outline-primary" href="/clientes/editar?id=<?= (int) $cliente["id"] ?>">
                                        Editar
                                    </a>
                                    <form method="post" action="/clientes/situacao">
                                        <input type="hidden" name="token_csrf" value="<?= escapar_html(
                                            gerar_token_csrf("situacao_cliente"),
                                        ) ?>">
                                        <input type="hidden" name="id_cliente" value="<?= (int) $cliente["id"] ?>">
                                        <input type="hidden" name="acao" value="<?= $cliente["ativo"] ? "desativar" : "ativar" ?>">
                                        <button class="btn btn-sm btn-outline-<?= $cliente["ativo"] ? "danger" : "success" ?>"
                                            type="submit">
                                            <?= $cliente["ativo"] ? "Desativar" : "Ativar" ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$clientes): ?>
                    <tr><td class="workhub-empty-state" colspan="5">Nenhum cliente encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
