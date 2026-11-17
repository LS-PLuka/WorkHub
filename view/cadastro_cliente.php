<?php

$paginaAtual = "clientes";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Clientes</p>
    <h1>Novo cliente</h1>
    <p>Cadastre os dados de contato para utilizá-los nos pedidos.</p>
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
                <label class="form-label" for="nome">Nome <span aria-hidden="true">*</span></label>
                <input class="form-control" id="nome" name="nome" maxlength="150" required
                    value="<?= escapar_html($cliente["nome"]) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telefone">Telefone <span aria-hidden="true">*</span></label>
                <input class="form-control" id="telefone" name="telefone" maxlength="20" required
                    value="<?= escapar_html($cliente["telefone"]) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" id="email" type="email" name="email" maxlength="150"
                    value="<?= escapar_html($cliente["email"]) ?>">
            </div>
        </div>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit">Salvar cliente</button>
            <a class="btn btn-link" href="/clientes">Cancelar</a>
        </div>
    </form>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
