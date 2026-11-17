<?php

$paginaAtual = "produtos";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Produtos</p>
    <h1>Novo produto</h1>
    <p>Informe os dados que serão usados na composição dos pedidos.</p>
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
                    value="<?= escapar_html($dadosProduto["nome"]) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="descricao">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="4"><?= escapar_html(
                    $dadosProduto["descricao"],
                ) ?></textarea>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="preco">Preço <span aria-hidden="true">*</span></label>
                <input class="form-control" id="preco" name="preco" inputmode="decimal" required
                    value="<?= escapar_html($dadosProduto["preco"] === false ? "" : $dadosProduto["preco"]) ?>">
            </div>
        </div>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit">Salvar produto</button>
            <a class="btn btn-link" href="/produtos">Cancelar</a>
        </div>
    </form>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
