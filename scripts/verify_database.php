<?php

require __DIR__ . '/../config/database.php';

function fail($message, $connection = null)
{
    if ($connection !== null) {
        mysqli_close($connection);
    }

    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function execute_or_fail($connection, $sql)
{
    if (mysqli_query($connection, $sql) === false) {
        fail('Falha no SQL: ' . mysqli_error($connection), $connection);
    }
}

function expect_rejection($connection, $sql, $label)
{
    if (mysqli_query($connection, $sql) !== false) {
        fail('A restrição não rejeitou o teste: ' . $label, $connection);
    }

    echo 'OK: ' . $label . PHP_EOL;
}

$connection = database_connect();

if ($connection === false) {
    fail('Falha ao conectar ao MySQL: ' . mysqli_connect_error());
}

$requiredColumns = [
    'empresas' => ['id', 'nome_fantasia', 'razao_social', 'cnpj', 'ativo', 'data_cadastro'],
    'usuarios' => ['id', 'empresa_id', 'email', 'senha', 'perfil', 'ativo'],
    'administradores_plataforma' => ['id', 'nome', 'email', 'senha', 'ativo'],
    'produtos' => ['id', 'empresa_id', 'nome', 'preco', 'ativo'],
    'clientes' => ['id', 'empresa_id', 'nome', 'ativo'],
    'pedidos' => ['id', 'empresa_id', 'cliente_id', 'usuario_id', 'status', 'valor_total', 'ativo'],
    'itens_pedido' => ['id', 'pedido_id', 'produto_id', 'quantidade', 'preco_unitario', 'subtotal', 'ativo'],
    'historico_status' => ['id', 'pedido_id', 'usuario_id', 'status_anterior', 'status_novo', 'data_alteracao'],
];

foreach ($requiredColumns as $table => $columns) {
    $result = mysqli_query($connection, "SHOW COLUMNS FROM `{$table}`");

    if ($result === false) {
        fail("Tabela ausente ou inacessível: {$table}", $connection);
    }

    $foundColumns = [];
    while ($column = mysqli_fetch_assoc($result)) {
        $foundColumns[] = $column['Field'];
    }
    mysqli_free_result($result);

    foreach ($columns as $column) {
        if (!in_array($column, $foundColumns, true)) {
            fail("Coluna ausente: {$table}.{$column}", $connection);
        }
    }
}

echo 'OK: tabelas e colunas principais encontradas.' . PHP_EOL;

if (mysqli_begin_transaction($connection) === false) {
    fail('Não foi possível iniciar a transação de teste.', $connection);
}

execute_or_fail($connection, "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Teste WorkHub', 'Teste WorkHub LTDA', '12345678000199', 'teste@workhub.local', '11999999999')");
$empresaId = mysqli_insert_id($connection);

expect_rejection($connection, "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Duplicada', 'Duplicada LTDA', '12345678000199', 'duplicada@workhub.local', '11999999998')", 'CNPJ duplicado');
expect_rejection($connection, "INSERT INTO produtos (empresa_id, nome, preco) VALUES ({$empresaId}, 'Preço inválido', -0.01)", 'preço negativo');
expect_rejection($connection, "INSERT INTO produtos (empresa_id, nome, preco) VALUES (4294967295, 'Empresa inválida', 10.00)", 'chave estrangeira inválida');

execute_or_fail($connection, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$empresaId}, 'Proprietário de teste', 'proprietario.teste@workhub.local', 'hash-nao-real', 'PROPRIETARIO')");
$usuarioId = mysqli_insert_id($connection);

expect_rejection($connection, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$empresaId}, 'E-mail duplicado', 'proprietario.teste@workhub.local', 'hash-nao-real', 'GERENTE')", 'e-mail de usuário duplicado');
expect_rejection($connection, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$empresaId}, 'Segundo proprietário', 'segundo.proprietario@workhub.local', 'hash-nao-real', 'PROPRIETARIO')", 'segundo Proprietário da mesma empresa');

execute_or_fail($connection, "INSERT INTO clientes (empresa_id, nome, telefone) VALUES ({$empresaId}, 'Cliente de teste', '11999999997')");
$clienteId = mysqli_insert_id($connection);
execute_or_fail($connection, "INSERT INTO produtos (empresa_id, nome, preco) VALUES ({$empresaId}, 'Produto de teste', 10.00)");
$produtoId = mysqli_insert_id($connection);
execute_or_fail($connection, "INSERT INTO pedidos (empresa_id, cliente_id, usuario_id) VALUES ({$empresaId}, {$clienteId}, {$usuarioId})");
$pedidoId = mysqli_insert_id($connection);

expect_rejection($connection, "INSERT INTO itens_pedido (pedido_id, produto_id, empresa_id, quantidade, preco_unitario, subtotal) VALUES ({$pedidoId}, {$produtoId}, {$empresaId}, 0, 10.00, 0.00)", 'quantidade igual a zero');

if (mysqli_rollback($connection) === false) {
    fail('Falha ao desfazer os dados de teste.', $connection);
}

mysqli_close($connection);
echo 'OK: testes de integridade concluídos e transação revertida.' . PHP_EOL;
