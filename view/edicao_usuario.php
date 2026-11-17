<?php

$paginaAtual = "usuarios";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Usuários</p>
    <h1>Editar usuário</h1>
    <p>Atualize os dados de acesso e o perfil deste usuário.</p>
</header>

<section class="workhub-form-panel">
    <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            <?php foreach ($erros as $erro): ?>
                <li><?= escapar_html($erro) ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label" for="nome">Nome completo <span aria-hidden="true">*</span></label>
                <input class="form-control" id="nome" name="nome" maxlength="150" required
                    value="<?= escapar_html($usuario["nome"]) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="email">E-mail <span aria-hidden="true">*</span></label>
                <input class="form-control" id="email" type="email" name="email" maxlength="150" required
                    value="<?= escapar_html($usuario["email"]) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="perfil">Perfil <span aria-hidden="true">*</span></label>
                <select class="form-select" id="perfil" name="perfil">
                    <option value="GERENTE" <?= $usuario["perfil"] === "GERENTE" ? "selected" : "" ?>>Gerente</option>
                    <option value="FUNCIONARIO" <?= $usuario["perfil"] === "FUNCIONARIO" ? "selected" : "" ?>>Funcionário</option>
                </select>
            </div>
        </div>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit">Salvar alterações</button>
            <a class="btn btn-link" href="/usuarios">Cancelar</a>
        </div>
    </form>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
