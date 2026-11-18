<?php

$paginaAtual = "pedidos";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Pedido #<?= (int) $pedido["id"] ?></p>
        <h1>Detalhes do pedido</h1>
        <p>Consulta dos dados, itens e movimentações registradas.</p>
    </div>
    <div class="workhub-header-actions">
        <a class="btn btn-outline-primary" href="/pedidos">Voltar para pedidos</a>
        <?php if ($podeEditar): ?>
            <a class="btn btn-primary" href="/pedidos/editar?id=<?= (int) $pedido["id"] ?>">Editar pedido</a>
        <?php endif; ?>
    </div>
</header>

<section class="workhub-panel mb-4">
    <div class="workhub-section-heading">
        <div>
            <h2>Identificação</h2>
            <p>Informações registradas no cadastro do pedido.</p>
        </div>
        <span class="workhub-status workhub-status--<?= strtolower($pedido["status"]) ?>">
            <?= escapar_html($pedido["status"]) ?>
        </span>
    </div>
    <dl class="workhub-details-grid workhub-details-grid--three mb-0">
        <div><dt>Cliente</dt><dd><?= escapar_html($pedido["cliente"]) ?></dd></div>
        <div><dt>Responsável</dt><dd><?= escapar_html($pedido["usuario"]) ?></dd></div>
        <div><dt>Criação</dt><dd><?= escapar_html(date("d/m/Y H:i", strtotime($pedido["data_criacao"]))) ?></dd></div>
        <div><dt>Entrega prevista</dt><dd><?= $pedido["data_entrega"] ? escapar_html(date("d/m/Y", strtotime($pedido["data_entrega"]))) : "—" ?></dd></div>
        <div><dt>Status atual</dt><dd><?= escapar_html($pedido["status"]) ?></dd></div>
        <div><dt>Valor total</dt><dd class="fw-semibold">R$ <?= escapar_html(formatar_valor_pedido($pedido["valor_total"])) ?></dd></div>
    </dl>
</section>

<section class="workhub-panel mb-4">
    <div class="workhub-section-heading">
        <div>
            <h2>Itens do pedido</h2>
            <p>Preços históricos preservados no momento do pedido.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço unitário</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= escapar_html($item["produto"]) ?></td>
                        <td><?= (int) $item["quantidade"] ?></td>
                        <td>R$ <?= escapar_html(formatar_valor_pedido($item["preco_unitario"])) ?></td>
                        <td class="fw-semibold">R$ <?= escapar_html(formatar_valor_pedido($item["subtotal"])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="workhub-panel">
    <div class="workhub-section-heading">
        <div>
            <h2>Histórico de produção</h2>
            <p>Alterações de status registradas para o pedido.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Status anterior</th>
                    <th>Novo status</th>
                    <th>Usuário</th>
                    <th>Data e horário</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico as $alteracao): ?>
                    <tr>
                        <td><?= escapar_html($alteracao["status_anterior"]) ?></td>
                        <td><span class="workhub-status"><?= escapar_html($alteracao["status_novo"]) ?></span></td>
                        <td><?= escapar_html($alteracao["usuario"]) ?></td>
                        <td><?= escapar_html(date("d/m/Y H:i", strtotime($alteracao["data_alteracao"]))) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$historico): ?>
                    <tr><td class="workhub-empty-state" colspan="4">Ainda não há alterações de status registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
