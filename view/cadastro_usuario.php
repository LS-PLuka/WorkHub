<?php

$paginaAtual = "usuarios";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Usuários</p>
    <h1>Novo usuário</h1>
    <p>Cadastre um acesso para a sua equipe.</p>
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
                    value="<?= escapar_html($dadosUsuario["nome"]) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="email">E-mail <span aria-hidden="true">*</span></label>
                <input class="form-control" id="email" type="email" name="email" maxlength="150" required
                    value="<?= escapar_html($dadosUsuario["email"]) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="perfil">Perfil <span aria-hidden="true">*</span></label>
                <select class="form-select" id="perfil" name="perfil">
                    <option value="GERENTE" <?= $dadosUsuario["perfil"] === "GERENTE" ? "selected" : "" ?>>Gerente</option>
                    <option value="FUNCIONARIO" <?= $dadosUsuario["perfil"] === "FUNCIONARIO" ? "selected" : "" ?>>Funcionário</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="senha">Senha <span aria-hidden="true">*</span></label>
                <input class="form-control" id="senha" type="password" name="senha" minlength="8" maxlength="72" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="confirmacao_senha">Confirmação de senha <span aria-hidden="true">*</span></label>
                <input class="form-control" id="confirmacao_senha" type="password" name="confirmacao_senha" minlength="8" maxlength="72" required>
            </div>
        </div>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit">Salvar usuário</button>
            <a class="btn btn-link" href="/usuarios">Cancelar</a>
        </div>
    </form>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
