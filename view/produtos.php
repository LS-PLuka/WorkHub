<?php

$paginaAtual = "produtos";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Catálogo</p>
        <h1>Produtos</h1>
        <p>Consulte e mantenha os produtos utilizados nos pedidos.</p>
    </div>
    <?php if ($podeGerenciar): ?>
        <a class="btn btn-primary" href="/produtos/cadastrar">Novo produto</a>
    <?php endif; ?>
</header>

<?php if ($mensagem): ?>
    <div class="alert alert-success" role="alert"><?= escapar_html($mensagem) ?></div>
<?php endif; ?>

<section class="workhub-panel">
    <form class="workhub-toolbar" method="get" action="/produtos">
        <div class="workhub-toolbar__field">
            <label class="visually-hidden" for="pesquisa">Buscar por nome</label>
            <input class="form-control" id="pesquisa" name="pesquisa" placeholder="Buscar por nome"
                value="<?= escapar_html($pesquisa) ?>">
        </div>
        <button class="btn btn-outline-primary" type="submit">Buscar</button>
    </form>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr><th>Nome</th><th>Descrição</th><th>Preço</th><th>Situação</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><strong><?= escapar_html($produto["nome"]) ?></strong></td>
                        <td><?= escapar_html($produto["descricao"] ?? "") ?></td>
                        <td>R$ <?= escapar_html(formatar_preco($produto["preco"])) ?></td>
                        <td>
                            <span class="workhub-status <?= $produto["ativo"] ? "workhub-status--success" : "" ?>">
                                <?= $produto["ativo"] ? "Ativo" : "Inativo" ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($podeGerenciar): ?>
                                <div class="workhub-table-actions">
                                    <?php if ((int) $produto["ativo"] === 1): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="/produtos/editar?id=<?= (int) $produto["id"] ?>">
                                            Editar
                                        </a>
                                    <?php endif; ?>
                                    <form method="post" action="/produtos/situacao">
                                        <input type="hidden" name="token_csrf" value="<?= escapar_html(
                                            gerar_token_csrf("situacao_produto"),
                                        ) ?>">
                                        <input type="hidden" name="id_produto" value="<?= (int) $produto["id"] ?>">
                                        <input type="hidden" name="acao" value="<?= $produto["ativo"] ? "desativar" : "ativar" ?>">
                                        <button class="btn btn-sm btn-outline-<?= $produto["ativo"] ? "danger" : "success" ?>"
                                            type="submit">
                                            <?= $produto["ativo"] ? "Desativar" : "Ativar" ?>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="workhub-table-note">Consulta</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$produtos): ?>
                    <tr><td class="workhub-empty-state" colspan="5">Nenhum produto encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
