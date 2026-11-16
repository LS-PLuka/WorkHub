<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= escapar_html(
    $tituloPagina,
) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5"><div class="d-flex justify-content-between mb-3"><h1 class="h2">Produtos</h1><?php if (
    $podeGerenciar
): ?><a class="btn btn-primary" href="/produtos/cadastrar">Cadastrar produto</a><?php endif; ?></div><?php if (
    $mensagem
): ?><div class="alert alert-success"><?= escapar_html(
    $mensagem,
) ?></div><?php endif; ?><form class="row g-2 mb-3" method="get"><div class="col-sm-8"><input class="form-control" name="pesquisa" placeholder="Buscar por nome" value="<?= escapar_html(
    $pesquisa,
) ?>"></div><div class="col-sm-4"><button class="btn btn-outline-primary">Buscar</button></div></form><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Nome</th><th>Descrição</th><th>Preço</th><th>Situação</th><th class="text-end">Ações</th></tr></thead><tbody><?php
foreach ($produtos as $produto): ?><tr><td><?= escapar_html($produto["nome"]) ?></td><td><?= escapar_html(
    $produto["descricao"] ?? "",
) ?></td><td>R$ <?= escapar_html(formatar_preco($produto["preco"])) ?></td><td><?= $produto["ativo"]
    ? "Ativo"
    : "Inativo" ?></td><td class="text-end"><?php if (
    $podeGerenciar
): ?><a class="btn btn-sm btn-outline-primary" href="/produtos/editar?id=<?= (int) $produto[
    "id"
] ?>">Editar</a><form class="d-inline" method="post" action="/produtos/situacao"><input type="hidden" name="token_csrf" value="<?= escapar_html(
    gerar_token_csrf("situacao_produto"),
) ?>"><input type="hidden" name="id_produto" value="<?= (int) $produto[
    "id"
] ?>"><input type="hidden" name="acao" value="<?= $produto["ativo"]
    ? "desativar"
    : "ativar" ?>"><button class="btn btn-sm btn-outline-<?= $produto["ativo"] ? "danger" : "success" ?>"><?= $produto[
    "ativo"
]
    ? "Desativar"
    : "Ativar" ?></button></form><?php else: ?><span class="text-secondary">Consulta</span><?php endif; ?></td></tr><?php endforeach;
if (
    !$produtos
): ?><tr><td colspan="5" class="text-center text-secondary">Nenhum produto encontrado.</td></tr><?php endif;
?></tbody></table></div></div><a class="btn btn-link mt-3" href="/painel">Voltar ao painel</a></main></body></html>
