<?php

$paginaAtual = "pedidos";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Pedidos</p>
    <h1>Novo pedido</h1>
    <p>Informe o cliente, a entrega prevista e os produtos solicitados.</p>
</header>

<?php if ($erros): ?>
    <div class="alert alert-danger" role="alert"><ul class="mb-0">
        <?php foreach ($erros as $erro): ?>
            <li><?= escapar_html($erro) ?></li>
        <?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<?php if (!$clientes || !$produtos): ?>
    <div class="alert alert-warning" role="alert">
        É necessário ter ao menos um cliente e um produto ativos para cadastrar pedidos.
    </div>
<?php endif; ?>

<form method="post" class="workhub-order-form">
    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
    <section class="workhub-panel mb-4">
        <div class="workhub-section-heading">
            <div>
                <h2>Informações gerais</h2>
                <p>Defina o cliente e a previsão de entrega.</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label" for="cliente_id">Cliente <span aria-hidden="true">*</span></label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= (int) $cliente["id"] ?>" <?= (string) $dadosPedido["cliente_id"] === (string) $cliente["id"] ? "selected" : "" ?>>
                            <?= escapar_html($cliente["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="data_entrega">Data prevista de entrega <span aria-hidden="true">*</span></label>
                <input class="form-control" type="date" id="data_entrega" name="data_entrega" required
                    value="<?= escapar_html($dadosPedido["data_entrega"]) ?>">
            </div>
        </div>
    </section>

    <section class="workhub-panel mb-4">
        <div class="workhub-section-heading">
            <div>
                <h2>Produtos do pedido</h2>
                <p>Os preços informados nas opções são apenas referência; o cálculo oficial ocorre no envio.</p>
            </div>
            <button class="btn btn-outline-primary btn-sm" type="button" id="adicionar-item">Adicionar produto</button>
        </div>
        <div class="workhub-order-items" id="itens-pedido">
            <?php foreach ($dadosPedido["produtos"] as $indice => $idProduto): ?>
                <div class="workhub-order-item">
                    <div class="workhub-order-item__product">
                        <label class="form-label" for="produto_<?= (int) $indice ?>">Produto</label>
                        <select class="form-select" id="produto_<?= (int) $indice ?>" name="produto_id[]" <?= $indice === 0 ? "required" : "" ?>>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= (int) $produto["id"] ?>" <?= (string) $idProduto === (string) $produto["id"] ? "selected" : "" ?>>
                                    <?= escapar_html($produto["nome"]) ?> — R$ <?= escapar_html(formatar_valor_pedido($produto["preco"])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="workhub-order-item__quantity">
                        <label class="form-label" for="quantidade_<?= (int) $indice ?>">Quantidade</label>
                        <input class="form-control" id="quantidade_<?= (int) $indice ?>" type="number" min="1" max="2147483647"
                            name="quantidade[]" value="<?= escapar_html($dadosPedido["quantidades"][$indice] ?? "") ?>" <?= $indice === 0 ? "required" : "" ?>>
                    </div>
                    <button class="btn btn-link workhub-order-item__remove" type="button" aria-label="Remover produto">Remover</button>
                </div>
            <?php endforeach; ?>
        </div>
        <template id="modelo-item-pedido">
            <div class="workhub-order-item">
                <div class="workhub-order-item__product">
                    <label class="form-label">Produto</label>
                    <select class="form-select" name="produto_id[]">
                        <option value="">Selecione um produto</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?= (int) $produto["id"] ?>">
                                <?= escapar_html($produto["nome"]) ?> — R$ <?= escapar_html(formatar_valor_pedido($produto["preco"])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="workhub-order-item__quantity">
                    <label class="form-label">Quantidade</label>
                    <input class="form-control" type="number" min="1" max="2147483647" name="quantidade[]">
                </div>
                <button class="btn btn-link workhub-order-item__remove" type="button">Remover</button>
            </div>
        </template>
        <noscript><p class="workhub-table-note">O primeiro item pode ser cadastrado sem JavaScript.</p></noscript>
    </section>

    <section class="workhub-order-summary">
        <div>
            <strong>Resumo do pedido</strong>
            <span>Os valores finais são calculados no servidor.</span>
        </div>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit" <?= !$clientes || !$produtos ? "disabled" : "" ?>>Cadastrar pedido</button>
            <a class="btn btn-link" href="/pedidos">Cancelar</a>
        </div>
    </section>
</form>
<script>
    const itensPedido = document.querySelector("#itens-pedido");
    const modeloItemPedido = document.querySelector("#modelo-item-pedido");
    const botaoAdicionarItem = document.querySelector("#adicionar-item");

    if (itensPedido && modeloItemPedido && botaoAdicionarItem) {
        const atualizarRemocaoItens = () => {
            itensPedido.querySelectorAll(".workhub-order-item__remove").forEach((botao) => {
                botao.onclick = () => {
                    if (itensPedido.children.length > 1) {
                        botao.closest(".workhub-order-item").remove();
                    }
                };
            });
        };

        botaoAdicionarItem.addEventListener("click", () => {
            itensPedido.append(modeloItemPedido.content.cloneNode(true));
            atualizarRemocaoItens();
        });

        atualizarRemocaoItens();
    }
</script>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
