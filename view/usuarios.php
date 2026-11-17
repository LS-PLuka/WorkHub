<?php

$paginaAtual = "usuarios";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Administração</p>
        <h1>Usuários</h1>
        <p>Gerencie os acessos e perfis vinculados à sua empresa.</p>
    </div>
    <a class="btn btn-primary" href="/usuarios/cadastrar">Novo usuário</a>
</header>

<?php if ($mensagem): ?>
    <div class="alert alert-success" role="alert"><?= escapar_html($mensagem) ?></div>
<?php endif; ?>

<section class="workhub-panel">
    <form class="workhub-toolbar" method="get" action="/usuarios">
        <div class="workhub-toolbar__field">
            <label class="visually-hidden" for="pesquisa">Localizar por nome</label>
            <input class="form-control" id="pesquisa" name="pesquisa" value="<?= escapar_html($pesquisa) ?>"
                placeholder="Localizar por nome">
        </div>
        <button class="btn btn-outline-primary" type="submit">Buscar</button>
        <?php if ($pesquisa !== ""): ?>
            <a class="btn btn-link" href="/usuarios">Limpar</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <strong><?= escapar_html($usuario["nome"]) ?></strong>
                            <?php if ($usuario["perfil"] === "PROPRIETARIO"): ?>
                                <span class="workhub-table-note">Responsável pela empresa</span>
                            <?php endif; ?>
                        </td>
                        <td><?= escapar_html($usuario["email"]) ?></td>
                        <td><span class="workhub-status"><?= escapar_html($usuario["perfil"]) ?></span></td>
                        <td>
                            <span class="workhub-status <?= $usuario["ativo"] ? "workhub-status--success" : "" ?>">
                                <?= $usuario["ativo"] ? "Ativo" : "Inativo" ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($usuario["perfil"] !== "PROPRIETARIO"): ?>
                                <div class="workhub-table-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="/usuarios/editar?id=<?= (int) $usuario["id"] ?>">
                                        Editar
                                    </a>
                                    <form method="post" action="/usuarios/situacao">
                                        <input type="hidden" name="token_csrf" value="<?= escapar_html(
                                            gerar_token_csrf("situacao_usuario"),
                                        ) ?>">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $usuario["id"] ?>">
                                        <input type="hidden" name="acao" value="<?= $usuario["ativo"] ? "desativar" : "ativar" ?>">
                                        <button class="btn btn-sm btn-outline-<?= $usuario["ativo"] ? "danger" : "success" ?>"
                                            type="submit">
                                            <?= $usuario["ativo"] ? "Desativar" : "Ativar" ?>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="workhub-table-note">Sem ações</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$usuarios): ?>
                    <tr><td class="workhub-empty-state" colspan="5">Nenhum usuário encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
