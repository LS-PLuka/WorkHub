<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= escapar_html(
    $tituloPagina,
) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5"><div class="col-lg-9 mx-auto"><a href="/pedidos">← Voltar aos pedidos</a><section class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3">Pedido #<?= (int) $pedido[
    "id"
] ?></h1><div class="row"><div class="col-md-6"><dl><dt>Cliente</dt><dd><?= escapar_html(
    $pedido["cliente"],
) ?></dd><dt>Responsável pelo cadastro</dt><dd><?= escapar_html(
    $pedido["usuario"],
) ?></dd><dt>Data de criação</dt><dd><?= escapar_html(
    date("d/m/Y H:i", strtotime($pedido["data_criacao"])),
) ?></dd></dl></div><div class="col-md-6"><dl><dt>Previsão de entrega</dt><dd><?= $pedido["data_entrega"]
    ? escapar_html(date("d/m/Y", strtotime($pedido["data_entrega"])))
    : "—" ?></dd><dt>Status</dt><dd><?= escapar_html(
    $pedido["status"],
) ?></dd><dt>Valor total</dt><dd class="fw-bold">R$ <?= escapar_html(
    formatar_valor_pedido($pedido["valor_total"]),
) ?></dd></dl></div></div><h2 class="h5 mt-3">Itens</h2><div class="table-responsive"><table class="table"><thead><tr><th>Produto</th><th>Quantidade</th><th>Preço unitário</th><th>Subtotal</th></tr></thead><tbody><?php foreach (
    $itens
    as $item
): ?><tr><td><?= escapar_html($item["produto"]) ?></td><td><?= (int) $item["quantidade"] ?></td><td>R$ <?= escapar_html(
    formatar_valor_pedido($item["preco_unitario"]),
) ?></td><td>R$ <?= escapar_html(
    formatar_valor_pedido($item["subtotal"]),
) ?></td></tr><?php endforeach; ?></tbody></table></div><?php if ($podeEditar): ?><a class="btn btn-primary" href="/pedidos/editar?id=<?= (int) $pedido["id"] ?>">Editar pedido</a><?php endif; ?><h2 class="h5 mt-4">Histórico de status</h2><table class="table"><thead><tr><th>Anterior</th><th>Novo</th><th>Usuário</th><th>Data</th></tr></thead><tbody><?php foreach ($historico as $alteracao): ?><tr><td><?= escapar_html($alteracao["status_anterior"]) ?></td><td><?= escapar_html($alteracao["status_novo"]) ?></td><td><?= escapar_html($alteracao["usuario"]) ?></td><td><?= escapar_html(date("d/m/Y H:i", strtotime($alteracao["data_alteracao"]))) ?></td></tr><?php endforeach; ?></tbody></table></div></section></div></main></body></html>
