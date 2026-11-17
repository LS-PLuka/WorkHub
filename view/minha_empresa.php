<?php

$paginaAtual = "minha_empresa";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Configurações empresariais</p>
    <h1>Minha empresa</h1>
    <p>Consulte e mantenha atualizados os dados cadastrais da sua empresa.</p>
</header>

<section class="workhub-form-panel" aria-labelledby="titulo-dados-cadastrais">
    <div class="workhub-section-heading">
        <div>
            <h2 id="titulo-dados-cadastrais">Dados cadastrais</h2>
            <p>O CNPJ identifica juridicamente a empresa e não pode ser alterado por este formulário.</p>
        </div>
    </div>

    <?php if ($mensagem !== ""): ?>
        <div class="alert alert-success" role="status"><?= escapar_html($mensagem) ?></div>
    <?php endif; ?>
    <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($erros as $erro): ?>
                    <li><?= escapar_html($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label" for="razao_social">Razão social <span aria-hidden="true">*</span></label>
                <input class="form-control" id="razao_social" name="razao_social" maxlength="200" required
                    value="<?= escapar_html($empresa["razao_social"]) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="nome_fantasia">Nome fantasia <span aria-hidden="true">*</span></label>
                <input class="form-control" id="nome_fantasia" name="nome_fantasia" maxlength="150" required
                    value="<?= escapar_html($empresa["nome_fantasia"]) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cnpj">CNPJ</label>
                <input class="form-control" id="cnpj" readonly value="<?= escapar_html(formatar_cnpj($empresa["cnpj"])) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="situacao">Situação cadastral</label>
                <input class="form-control" id="situacao" readonly value="<?= (int) $empresa["ativo"] === 1 ? "Ativa" : "Inativa" ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">E-mail de contato <span aria-hidden="true">*</span></label>
                <input class="form-control" id="email" type="email" name="email" maxlength="150" required
                    value="<?= escapar_html($empresa["email"]) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telefone">Telefone <span aria-hidden="true">*</span></label>
                <input class="form-control" id="telefone" name="telefone" inputmode="numeric" maxlength="15" required
                    data-mascara="telefone" value="<?= escapar_html(formatar_telefone($empresa["telefone"])) ?>">
            </div>
        </div>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit">Salvar alterações</button>
        </div>
    </form>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
