<?php

$paginaAtual = "dashboard";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading">
    <div>
        <p class="workhub-eyebrow">Visão geral</p>
        <h1>Dashboard</h1>
        <p>Olá, <?= escapar_html($contexto["nome_usuario"]) ?>. Acompanhe os pedidos e os prazos da empresa.</p>
    </div>
</header>

<?php foreach ($erros as $erro): ?>
    <div class="alert alert-danger" role="alert"><?= escapar_html($erro) ?></div>
<?php endforeach; ?>

<section class="workhub-panel workhub-filter-panel" aria-labelledby="titulo-periodo">
    <div class="workhub-section-heading">
        <div>
            <h2 id="titulo-periodo">Período de análise</h2>
            <p>Filtre o valor e os indicadores dos pedidos cadastrados.</p>
        </div>
    </div>
    <form class="row gy-3 gx-md-3 align-items-end" method="get" action="/dashboard">
        <div class="col-sm-5 col-md-3">
            <label class="form-label" for="data_inicial">Data inicial</label>
            <input class="form-control" id="data_inicial" name="data_inicial" type="date"
                value="<?= escapar_html($periodo["data_inicial"]) ?>">
        </div>
        <div class="col-sm-5 col-md-3">
            <label class="form-label" for="data_final">Data final</label>
            <input class="form-control" id="data_final" name="data_final" type="date"
                value="<?= escapar_html($periodo["data_final"]) ?>">
        </div>
        <div class="col-sm-2 col-md-auto">
            <button class="btn btn-primary w-100" type="submit">Aplicar filtro</button>
        </div>
    </form>
    <p class="workhub-filter-panel__summary mb-0">Pedidos cadastrados de <?= escapar_html(
        date("d/m/Y", strtotime($periodo["data_inicial"])),
    ) ?> até <?= escapar_html(date("d/m/Y", strtotime($periodo["data_final"]))) ?>.</p>
</section>

<section class="workhub-metrics" aria-label="Indicadores de pedidos">
    <?php
    $cards = [
        ["Total de pedidos", $indicadores["total_pedidos"], "primary"],
        ["Em produção", $indicadores["pedidos_em_producao"], "info"],
        ["Prontos", $indicadores["pedidos_prontos"], "success"],
        ["Entregues", $indicadores["pedidos_entregues"], "neutral"],
        ["Próximos do vencimento", $indicadores["pedidos_proximos_vencimento"], "warning"],
        ["Atrasados", $indicadores["pedidos_atrasados"], "danger"],
    ];
    foreach ($cards as [$rotulo, $valor, $tom]):
    ?>
        <article class="workhub-metric workhub-metric--<?= escapar_html($tom) ?>">
            <p><?= escapar_html($rotulo) ?></p>
            <strong><?= (int) $valor ?></strong>
        </article>
    <?php endforeach; ?>
    <article class="workhub-metric workhub-metric--value">
        <p>Valor total no período</p>
        <strong>R$ <?= escapar_html(formatar_valor_dashboard($indicadores["valor_total_periodo"])) ?></strong>
    </article>
</section>

<section class="workhub-panel mb-4">
    <div class="workhub-section-heading">
        <div>
            <h2>Pedidos recentes</h2>
            <p>Registros criados mais recentemente.</p>
        </div>
        <a class="btn btn-outline-primary btn-sm" href="/pedidos">Ver todos</a>
    </div>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr><th>ID</th><th>Cliente</th><th>Criação</th><th>Status</th><th>Valor total</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($pedidosRecentes as $pedido): ?>
                    <tr>
                        <td class="fw-semibold">#<?= (int) $pedido["id"] ?></td>
                        <td><?= escapar_html($pedido["cliente"]) ?></td>
                        <td><?= escapar_html(date("d/m/Y H:i", strtotime($pedido["data_criacao"]))) ?></td>
                        <td><span class="workhub-status"><?= escapar_html($pedido["status"]) ?></span></td>
                        <td>R$ <?= escapar_html(formatar_valor_dashboard($pedido["valor_total"])) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">
                                Detalhes
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pedidosRecentes): ?>
                    <tr><td class="workhub-empty-state" colspan="6">Nenhum pedido cadastrado no momento.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="workhub-panel">
    <div class="workhub-section-heading">
        <div>
            <h2>Alertas de prazo</h2>
            <p>Pedidos que merecem acompanhamento prioritário.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr><th>Pedido</th><th>Cliente</th><th>Entrega prevista</th><th>Status</th><th>Situação</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($alertasPrazo as $pedido): ?>
                    <tr class="<?= $pedido["situacao_prazo"] === "atrasado" ? "workhub-row--late" : "" ?>">
                        <td class="fw-semibold">#<?= (int) $pedido["id"] ?></td>
                        <td><?= escapar_html($pedido["cliente"]) ?></td>
                        <td><?= escapar_html(date("d/m/Y", strtotime($pedido["data_entrega"]))) ?></td>
                        <td><span class="workhub-status"><?= escapar_html($pedido["status"]) ?></span></td>
                        <td>
                            <?php if ($pedido["situacao_prazo"] === "atrasado"): ?>
                                <span class="workhub-status workhub-status--danger">Atrasado</span>
                            <?php else: ?>
                                <span class="workhub-status workhub-status--warning">Próximo do vencimento</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">
                                Detalhes
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$alertasPrazo): ?>
                    <tr><td class="workhub-empty-state" colspan="6">Não há alertas de prazo.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
