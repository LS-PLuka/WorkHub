<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar_html($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-4 py-md-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-primary text-uppercase fw-semibold mb-1">
                    <?= escapar_html($contexto["nome_empresa"]) ?>
                </p>
                <h1 class="h2 mb-1">Dashboard</h1>
                <p class="text-secondary mb-0">Olá, <?= escapar_html($contexto["nome_usuario"]) ?>.</p>
            </div>
            <form method="post" action="/sair">
                <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSaida) ?>">
                <button class="btn btn-outline-danger" type="submit">Sair</button>
            </form>
        </header>

        <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Navegação principal">
            <a class="btn btn-outline-primary" href="/pedidos">Pedidos</a>
            <a class="btn btn-outline-primary" href="/producao">Acompanhamento da produção</a>
            <a class="btn btn-outline-primary" href="/clientes">Clientes</a>
            <a class="btn btn-outline-primary" href="/produtos">Produtos</a>
            <?php if ($contexto["perfil"] === "PROPRIETARIO"): ?>
                <a class="btn btn-primary" href="/usuarios">Gerenciar usuários</a>
            <?php endif; ?>
        </nav>

        <?php foreach ($erros as $erro): ?>
            <div class="alert alert-danger" role="alert"><?= escapar_html($erro) ?></div>
        <?php endforeach; ?>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Valor dos pedidos por período</h2>
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
                        <button class="btn btn-primary w-100" type="submit">Filtrar</button>
                    </div>
                </form>
                <p class="text-secondary small mb-0 mt-3">Pedidos cadastrados de <?= escapar_html(
                    date("d/m/Y", strtotime($periodo["data_inicial"])),
                ) ?> até <?= escapar_html(date("d/m/Y", strtotime($periodo["data_final"]))) ?>.</p>
            </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Indicadores de pedidos">
            <?php
            $cards = [
                ["Total de pedidos", $indicadores["total_pedidos"], "text-primary"],
                ["Em produção", $indicadores["pedidos_em_producao"], "text-info"],
                ["Prontos", $indicadores["pedidos_prontos"], "text-success"],
                ["Entregues", $indicadores["pedidos_entregues"], "text-secondary"],
                ["Próximos do vencimento", $indicadores["pedidos_proximos_vencimento"], "text-warning"],
                ["Atrasados", $indicadores["pedidos_atrasados"], "text-danger"],
            ];
            foreach ($cards as [$rotulo, $valor, $classe]):
            ?>
                <div class="col-sm-6 col-lg-4">
                    <article class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-2"><?= escapar_html($rotulo) ?></p>
                            <p class="display-6 fw-semibold mb-0 <?= escapar_html($classe) ?>"><?= (int) $valor ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
            <div class="col-sm-6 col-lg-4">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-secondary mb-2">Valor total no período</p>
                        <p class="h3 fw-semibold text-primary mb-0">R$ <?= escapar_html(
                            formatar_valor_dashboard($indicadores["valor_total_periodo"]),
                        ) ?></p>
                    </div>
                </article>
            </div>
        </section>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Pedidos recentes</h2>
                    <a class="btn btn-sm btn-outline-primary" href="/pedidos">Ver pedidos</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr><th>ID</th><th>Cliente</th><th>Criação</th><th>Status</th><th>Valor total</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidosRecentes as $pedido): ?>
                                <tr>
                                    <td>#<?= (int) $pedido["id"] ?></td>
                                    <td><?= escapar_html($pedido["cliente"]) ?></td>
                                    <td><?= escapar_html(date("d/m/Y H:i", strtotime($pedido["data_criacao"]))) ?></td>
                                    <td><?= escapar_html($pedido["status"]) ?></td>
                                    <td>R$ <?= escapar_html(formatar_valor_dashboard($pedido["valor_total"])) ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">Detalhes</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$pedidosRecentes): ?>
                                <tr><td class="text-center text-secondary" colspan="6">Nenhum pedido cadastrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">Alertas de prazo</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr><th>Pedido</th><th>Cliente</th><th>Entrega prevista</th><th>Status</th><th>Situação</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertasPrazo as $pedido): ?>
                                <tr>
                                    <td>#<?= (int) $pedido["id"] ?></td>
                                    <td><?= escapar_html($pedido["cliente"]) ?></td>
                                    <td><?= escapar_html(date("d/m/Y", strtotime($pedido["data_entrega"]))) ?></td>
                                    <td><?= escapar_html($pedido["status"]) ?></td>
                                    <td>
                                        <?php if ($pedido["situacao_prazo"] === "atrasado"): ?>
                                            <span class="badge text-bg-danger">Atrasado</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning">Próximo do vencimento</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">Detalhes</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$alertasPrazo): ?>
                                <tr><td class="text-center text-secondary" colspan="6">Não há alertas de prazo.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
