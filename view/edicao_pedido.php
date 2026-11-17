<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar_html($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="col-lg-9 mx-auto">
        <a href="/pedidos/detalhes?id=<?= (int) $pedido["id"] ?>">← Voltar ao pedido</a>
        <section class="card border-0 shadow-sm mt-3">
            <div class="card-body p-4">
                <h1 class="h3">Editar pedido #<?= (int) $pedido["id"] ?></h1>
                <?php if ($erros): ?>
                    <div class="alert alert-danger"><ul class="mb-0">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= escapar_html($erro) ?></li>
                        <?php endforeach; ?>
                    </ul></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Cliente</label><select class="form-select" name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= (int) $cliente["id"] ?>" <?= (int) $pedido["cliente_id"] === (int) $cliente["id"] ? "selected" : "" ?>><?= escapar_html($cliente["nome"]) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                        <div class="col-md-6"><label class="form-label">Previsão de entrega</label><input class="form-control" type="date" name="data_entrega" value="<?= escapar_html($pedido["data_entrega"]) ?>" required></div>
                    </div>
                    <h2 class="h5 mt-4">Itens</h2>
                    <?php foreach ($itens as $indice => $item): ?>
                        <div class="row g-2 mb-2"><div class="col-md-8"><select class="form-select" name="produto_id[]" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= (int) $produto["id"] ?>" <?= (int) $item["produto_id"] === (int) $produto["id"] ? "selected" : "" ?>><?= escapar_html($produto["nome"]) ?></option>
                            <?php endforeach; ?>
                            <?php if (isset($item["produto_ativo"]) && !(int) $item["produto_ativo"]): ?>
                                <option value="<?= (int) $item["produto_id"] ?>" selected><?= escapar_html($item["produto"]) ?> (inativo, preço preservado)</option>
                            <?php endif; ?>
                        </select></div><div class="col-md-4"><input class="form-control" type="number" min="1" name="quantidade[]" value="<?= (int) $item["quantidade"] ?>" required></div></div>
                    <?php endforeach; ?>
                    <p class="small text-secondary">Adicione quantas linhas forem necessárias antes de salvar.</p>
                    <button class="btn btn-outline-primary" type="submit" name="acao" value="adicionar_item" formnovalidate>Adicionar produto</button>
                    <button class="btn btn-primary" type="submit" name="acao" value="salvar">Salvar alterações</button>
                </form>
            </div>
        </section>
    </div>
</main>
</body>
</html>
