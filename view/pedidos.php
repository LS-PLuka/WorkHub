<?php

$paginaAtual = "pedidos";
require __DIR__ . "/layout_empresa_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Comercial</p>
        <h1>Pedidos</h1>
        <p>Acompanhe os pedidos cadastrados e suas previsões de entrega.</p>
    </div>
    <a class="btn btn-primary" href="/pedidos/cadastrar">Novo pedido</a>
</header>

<?php if ($mensagem): ?>
    <div class="alert alert-success" role="alert"><?= escapar_html($mensagem) ?></div>
<?php endif; ?>

<section class="workhub-panel">
    <form class="row g-3 align-items-end mb-4" method="get" action="/pedidos">
        <div class="col-sm-3 col-lg-2">
            <label class="form-label" for="id">Pedido</label>
            <input class="form-control" id="id" type="number" min="1" name="id" placeholder="ID"
                value="<?= $filtros["id"] === null ? "" : (int) $filtros["id"] ?>">
        </div>
        <div class="col-sm-4 col-lg-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="">Todos os status</option>
                <?php foreach (["RECEBIDO", "EM_PRODUCAO", "PRONTO", "ENTREGUE", "CANCELADO"] as $status): ?>
                    <option value="<?= $status ?>" <?= $filtros["status"] === $status ? "selected" : "" ?>>
                        <?= escapar_html($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-3 col-lg-2">
            <label class="form-label" for="data_inicial">De</label>
            <input class="form-control" id="data_inicial" type="date" name="data_inicial"
                value="<?= escapar_html($filtros["data_inicial"]) ?>">
        </div>
        <div class="col-sm-3 col-lg-2">
            <label class="form-label" for="data_final">Até</label>
            <input class="form-control" id="data_final" type="date" name="data_final"
                value="<?= escapar_html($filtros["data_final"]) ?>">
        </div>
        <div class="col-sm-3 col-lg-auto">
            <button class="btn btn-outline-primary w-100" type="submit">Filtrar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table workhub-table align-middle mb-0">
            <thead>
                <tr><th>Pedido</th><th>Cliente</th><th>Criação</th><th>Entrega</th><th>Status</th><th>Total</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td class="fw-semibold">#<?= (int) $pedido["id"] ?></td>
                        <td><?= escapar_html($pedido["cliente"]) ?></td>
                        <td><?= escapar_html(date("d/m/Y H:i", strtotime($pedido["data_criacao"]))) ?></td>
                        <td><?= $pedido["data_entrega"] ? escapar_html(date("d/m/Y", strtotime($pedido["data_entrega"]))) : "-" ?></td>
                        <td><span class="workhub-status workhub-status--<?= strtolower($pedido["status"]) ?>"><?= escapar_html($pedido["status"]) ?></span></td>
                        <td>R$ <?= escapar_html(formatar_valor_pedido($pedido["valor_total"])) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">
                                Detalhes
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pedidos): ?>
                    <tr><td class="workhub-empty-state" colspan="7">Nenhum pedido encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . "/layout_empresa_fim.php"; ?>
