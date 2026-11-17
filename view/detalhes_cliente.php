<?php

$paginaAtual = "clientes";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Clientes</p>
        <h1><?= escapar_html($cliente["nome"]) ?></h1>
        <p>Informações cadastrais e histórico de pedidos.</p>
    </div>
    <a class="btn btn-outline-primary" href="/clientes">Voltar para clientes</a>
</header>

<section class="workhub-panel mb-4">
    <div class="workhub-section-heading">
        <div>
            <h2>Informações cadastrais</h2>
            <p>Dados de contato registrados para este cliente.</p>
        </div>
    </div>
    <dl class="workhub-details-grid mb-0">
        <div><dt>Telefone</dt><dd><?= escapar_html(formatar_telefone($cliente["telefone"])) ?></dd></div>
        <div><dt>E-mail</dt><dd><?= escapar_html($cliente["email"] ?? "Não informado") ?></dd></div>
    </dl>
</section>

<section class="workhub-panel">
    <div class="workhub-section-heading">
        <div>
            <h2>Histórico de pedidos</h2>
            <p>Pedidos vinculados a este cliente.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr><th>Pedido</th><th>Data</th><th>Status</th><th>Total</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td class="fw-semibold">#<?= (int) $pedido["id"] ?></td>
                        <td><?= escapar_html(date("d/m/Y", strtotime($pedido["data_criacao"]))) ?></td>
                        <td><span class="workhub-status"><?= escapar_html($pedido["status"]) ?></span></td>
                        <td>R$ <?= escapar_html(formatar_valor_pedido($pedido["valor_total"])) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">
                                Detalhes
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pedidos): ?>
                    <tr><td class="workhub-empty-state" colspan="5">Este cliente ainda não possui pedidos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
