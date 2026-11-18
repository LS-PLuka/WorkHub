<?php

$paginaAtual = "producao";
require __DIR__ . "/layout_empresa_inicio.php";

$etapas = ["RECEBIDO", "EM_PRODUCAO", "PRONTO", "ENTREGUE", "CANCELADO"];

?>
<header class="workhub-page-heading">
    <p class="workhub-eyebrow">Operação</p>
    <h1>Acompanhamento da produção</h1>
    <p>Visualize os pedidos por etapa e atualize o andamento quando permitido.</p>
</header>

<div class="workhub-production-stages">
    <?php foreach ($etapas as $status): ?>
        <?php
        $pedidosStatus = array_filter($pedidos, static fn ($pedido) => $pedido["status"] === $status);
        ?>
        <section class="workhub-panel workhub-production-stage">
            <div class="workhub-section-heading">
                <div>
                    <h2><?= escapar_html($status) ?></h2>
                    <p><?= count($pedidosStatus) ?> pedido(s) nesta etapa.</p>
                </div>
                <span class="workhub-status workhub-status--<?= strtolower($status) ?>"><?= count($pedidosStatus) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table workhub-table align-middle mb-0">
                    <thead><tr><th>Pedido</th><th>Cliente</th><th>Entrega</th><th>Prazo</th><th class="text-end">Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($pedidosStatus as $pedido): ?>
                            <tr class="<?= $pedido["indicador_prazo"] === "atrasado" ? "workhub-row--late" : "" ?>">
                                <td class="fw-semibold">#<?= (int) $pedido["id"] ?></td>
                                <td><?= escapar_html($pedido["cliente"]) ?></td>
                                <td><?= escapar_html($pedido["data_entrega"] ?? "-") ?></td>
                                <td>
                                    <?php if ($pedido["indicador_prazo"] === "atrasado"): ?>
                                        <span class="workhub-status workhub-status--danger">Atrasado</span>
                                    <?php elseif ($pedido["indicador_prazo"] === "proximo"): ?>
                                        <span class="workhub-status workhub-status--warning">Próximo do vencimento</span>
                                    <?php else: ?>
                                        <span class="workhub-table-note">Dentro do prazo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="workhub-table-actions">
                                        <a class="btn btn-sm btn-outline-primary" href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">
                                            Detalhes
                                        </a>
                                        <?php if (!in_array($status, ["ENTREGUE", "CANCELADO"], true)): ?>
                                            <form method="post" action="/pedidos/status" class="workhub-status-form">
                                                <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
                                                <input type="hidden" name="id_pedido" value="<?= (int) $pedido["id"] ?>">
                                                <select class="form-select form-select-sm" name="status" aria-label="Novo status do pedido">
                                                    <option value="EM_PRODUCAO">Em produção</option>
                                                    <option value="PRONTO">Pronto</option>
                                                    <option value="ENTREGUE">Entregue</option>
                                                </select>
                                                <button class="btn btn-sm btn-primary" type="submit">Atualizar</button>
                                            </form>
                                            <?php if ($podeCancelar): ?>
                                                <form method="post" action="/pedidos/cancelar">
                                                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
                                                    <input type="hidden" name="id_pedido" value="<?= (int) $pedido["id"] ?>">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit"
                                                        title="O cancelamento impede novas transições deste pedido.">
                                                        Cancelar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$pedidosStatus): ?>
                            <tr><td class="workhub-empty-state" colspan="5">Nenhum pedido nesta etapa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
