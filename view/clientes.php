<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= escapar_html(
    $tituloPagina,
) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5"><div class="d-flex justify-content-between mb-3"><h1 class="h2">Clientes</h1><?php if (
    $podeGerenciar
): ?><a class="btn btn-primary" href="/clientes/cadastrar">Cadastrar cliente</a><?php endif; ?></div><?php if (
    $mensagem
): ?><div class="alert alert-success"><?= escapar_html(
    $mensagem,
) ?></div><?php endif; ?><form class="row g-2 mb-3"><div class="col-sm-8"><input class="form-control" name="pesquisa" value="<?= escapar_html(
    $pesquisa,
) ?>" placeholder="Buscar por nome"></div><div class="col-sm-4"><button class="btn btn-outline-primary">Buscar</button></div></form><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Nome</th><th>Telefone</th><th>E-mail</th><th>Situação</th><th></th></tr></thead><tbody><?php
foreach ($clientes as $cliente): ?><tr><td><?= escapar_html($cliente["nome"]) ?></td><td><?= escapar_html(
    $cliente["telefone"],
) ?></td><td><?= escapar_html($cliente["email"] ?? "") ?></td><td><?= $cliente["ativo"]
    ? "Ativo"
    : "Inativo" ?></td><td class="text-end"><?php if (
    $podeGerenciar
): ?><a class="btn btn-sm btn-outline-primary" href="/clientes/editar?id=<?= (int) $cliente[
    "id"
] ?>">Editar</a><form class="d-inline" method="post" action="/clientes/situacao"><input type="hidden" name="token_csrf" value="<?= escapar_html(
    gerar_token_csrf("situacao_cliente"),
) ?>"><input type="hidden" name="id_cliente" value="<?= (int) $cliente[
    "id"
] ?>"><input type="hidden" name="acao" value="<?= $cliente["ativo"]
    ? "desativar"
    : "ativar" ?>"><button class="btn btn-sm btn-outline-<?= $cliente["ativo"] ? "danger" : "success" ?>"><?= $cliente[
    "ativo"
]
    ? "Desativar"
    : "Ativar" ?></button></form><?php else: ?><a class="btn btn-sm btn-outline-primary" href="/clientes/detalhes?id=<?= (int) $cliente["id"] ?>">Histórico</a><?php endif; ?></td></tr><?php endforeach;
if (
    !$clientes
): ?><tr><td colspan="5" class="text-center text-secondary">Nenhum cliente encontrado.</td></tr><?php endif;
?></tbody></table></div></div><a class="btn btn-link mt-3" href="/painel">Voltar ao painel</a></main></body></html>
