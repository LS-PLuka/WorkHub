<?php

$paginaAtual = "pedidos";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Pedido #<?= (int) $pedido["id"] ?></p>
        <h1>Editar pedido</h1>
        <p>Atualize as informações e os itens antes de avançar a produção.</p>
    </div>
    <span class="workhub-status workhub-status--<?= strtolower($pedido["status"]) ?>"><?= escapar_html($pedido["status"]) ?></span>
</header>

<?php if ($erros): ?>
    <div class="alert alert-danger" role="alert"><ul class="mb-0">
        <?php foreach ($erros as $erro): ?>
            <li><?= escapar_html($erro) ?></li>
        <?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form method="post" class="workhub-order-form">
    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
    <section class="workhub-panel mb-4">
        <div class="workhub-section-heading">
            <div>
                <h2>Informações gerais</h2>
                <p>Cliente e previsão de entrega.</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label" for="cliente_id">Cliente <span aria-hidden="true">*</span></label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int) $cliente["id"] ?>" <?= (int) $pedido["cliente_id"] === (int) $cliente["id"] ? "selected" : "" ?>>
                            <?= escapar_html($cliente["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="data_entrega">Previsão de entrega <span aria-hidden="true">*</span></label>
                <input class="form-control" id="data_entrega" type="date" name="data_entrega" required
                    value="<?= escapar_html($pedido["data_entrega"]) ?>">
            </div>
        </div>
    </section>

    <section class="workhub-panel mb-4">
        <div class="workhub-section-heading">
            <div>
                <h2>Itens</h2>
                <p>Altere produtos e quantidades antes de salvar.</p>
            </div>
        </div>
        <div class="workhub-order-items">
            <?php foreach ($itens as $indice => $item): ?>
                <div class="workhub-order-item">
                    <div class="workhub-order-item__product">
                        <label class="form-label" for="produto_<?= (int) $indice ?>">Produto</label>
                        <select class="form-select" id="produto_<?= (int) $indice ?>" name="produto_id[]" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= (int) $produto["id"] ?>" <?= (int) $item["produto_id"] === (int) $produto["id"] ? "selected" : "" ?>>
                                    <?= escapar_html($produto["nome"]) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (isset($item["produto_ativo"]) && !(int) $item["produto_ativo"]): ?>
                                <option value="<?= (int) $item["produto_id"] ?>" selected>
                                    <?= escapar_html($item["produto"]) ?> (inativo, preço preservado)
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="workhub-order-item__quantity">
                        <label class="form-label" for="quantidade_<?= (int) $indice ?>">Quantidade</label>
                        <input class="form-control" id="quantidade_<?= (int) $indice ?>" type="number" min="1" name="quantidade[]"
                            value="<?= (int) $item["quantidade"] ?>" required>
                    </div>
                    <?php if (isset($item["preco_unitario"])): ?>
                        <span class="workhub-order-item__price">Histórico: R$ <?= escapar_html(
                            formatar_valor_pedido($item["preco_unitario"]),
                        ) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="workhub-table-note">Adicione novas linhas sem perder os dados preenchidos.</p>
        <button class="btn btn-outline-primary" type="submit" name="acao" value="adicionar_item" formnovalidate>
            Adicionar produto
        </button>
    </section>

    <section class="workhub-order-summary">
        <div>
            <strong>Resumo financeiro</strong>
            <span>O valor final é recalculado no servidor ao salvar.</span>
        </div>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit" name="acao" value="salvar">Salvar alterações</button>
            <a class="btn btn-link" href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">Cancelar</a>
        </div>
    </section>
</form>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
