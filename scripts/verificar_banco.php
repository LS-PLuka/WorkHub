<?php

require __DIR__ . '/../config/banco_dados.php';

function encerrar_com_erro($mensagem, $conexao = null)
{
    if ($conexao !== null) {
        mysqli_close($conexao);
    }
    fwrite(STDERR, $mensagem . PHP_EOL);
    exit(1);
}

function executar_ou_encerrar($conexao, $sql)
{
    if (mysqli_query($conexao, $sql) === false) {
        encerrar_com_erro('Falha no SQL: ' . mysqli_error($conexao), $conexao);
    }
}

function esperar_rejeicao($conexao, $sql, $rotulo)
{
    if (mysqli_query($conexao, $sql) !== false) {
        encerrar_com_erro('A restrição não rejeitou o teste: ' . $rotulo, $conexao);
    }
    echo 'OK: ' . $rotulo . PHP_EOL;
}

$conexao = conectar_banco_dados();
if ($conexao === false) {
    encerrar_com_erro('Falha ao conectar ao MySQL: ' . mysqli_connect_error());
}

$colunasObrigatorias = [
    'empresas' => ['id', 'nome_fantasia', 'razao_social', 'cnpj', 'ativo', 'data_cadastro'],
    'usuarios' => ['id', 'empresa_id', 'email', 'senha', 'perfil', 'ativo'],
    'administradores_plataforma' => ['id', 'nome', 'email', 'senha', 'ativo'],
    'produtos' => ['id', 'empresa_id', 'nome', 'preco', 'ativo'],
    'clientes' => ['id', 'empresa_id', 'nome', 'ativo'],
    'pedidos' => ['id', 'empresa_id', 'cliente_id', 'usuario_id', 'status', 'valor_total', 'ativo'],
    'itens_pedido' => ['id', 'pedido_id', 'produto_id', 'quantidade', 'preco_unitario', 'subtotal', 'ativo'],
    'historico_status' => ['id', 'pedido_id', 'usuario_id', 'status_anterior', 'status_novo', 'data_alteracao'],
];

foreach ($colunasObrigatorias as $tabela => $colunas) {
    $resultado = mysqli_query($conexao, "SHOW COLUMNS FROM `{$tabela}`");
    if ($resultado === false) {
        encerrar_com_erro("Tabela ausente ou inacessível: {$tabela}", $conexao);
    }

    $colunasEncontradas = [];
    while ($coluna = mysqli_fetch_assoc($resultado)) {
        $colunasEncontradas[] = $coluna['Field'];
    }
    mysqli_free_result($resultado);

    foreach ($colunas as $coluna) {
        if (!in_array($coluna, $colunasEncontradas, true)) {
            encerrar_com_erro("Coluna ausente: {$tabela}.{$coluna}", $conexao);
        }
    }
}

echo 'OK: tabelas e colunas principais encontradas.' . PHP_EOL;
if (mysqli_begin_transaction($conexao) === false) {
    encerrar_com_erro('Não foi possível iniciar a transação de teste.', $conexao);
}

executar_ou_encerrar($conexao, "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Teste WorkHub', 'Teste WorkHub LTDA', '12345678000199', 'teste@workhub.local', '11999999999')");
$idEmpresa = mysqli_insert_id($conexao);
esperar_rejeicao($conexao, "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Duplicada', 'Duplicada LTDA', '12345678000199', 'duplicada@workhub.local', '11999999998')", 'CNPJ duplicado');
esperar_rejeicao($conexao, "INSERT INTO produtos (empresa_id, nome, preco) VALUES ({$idEmpresa}, 'Preço inválido', -0.01)", 'preço negativo');
esperar_rejeicao($conexao, "INSERT INTO produtos (empresa_id, nome, preco) VALUES (4294967295, 'Empresa inválida', 10.00)", 'chave estrangeira inválida');
executar_ou_encerrar($conexao, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$idEmpresa}, 'Proprietário de teste', 'proprietario.teste@workhub.local', 'hash-nao-real', 'PROPRIETARIO')");
$idUsuario = mysqli_insert_id($conexao);
esperar_rejeicao($conexao, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$idEmpresa}, 'E-mail duplicado', 'proprietario.teste@workhub.local', 'hash-nao-real', 'GERENTE')", 'e-mail de usuário duplicado');
esperar_rejeicao($conexao, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$idEmpresa}, 'Segundo proprietário', 'segundo.proprietario@workhub.local', 'hash-nao-real', 'PROPRIETARIO')", 'segundo Proprietário da mesma empresa');
executar_ou_encerrar($conexao, "INSERT INTO clientes (empresa_id, nome, telefone) VALUES ({$idEmpresa}, 'Cliente de teste', '11999999997')");
$idCliente = mysqli_insert_id($conexao);
executar_ou_encerrar($conexao, "INSERT INTO produtos (empresa_id, nome, preco) VALUES ({$idEmpresa}, 'Produto de teste', 10.00)");
$idProduto = mysqli_insert_id($conexao);
executar_ou_encerrar($conexao, "INSERT INTO pedidos (empresa_id, cliente_id, usuario_id) VALUES ({$idEmpresa}, {$idCliente}, {$idUsuario})");
$idPedido = mysqli_insert_id($conexao);
esperar_rejeicao($conexao, "INSERT INTO itens_pedido (pedido_id, produto_id, empresa_id, quantidade, preco_unitario, subtotal) VALUES ({$idPedido}, {$idProduto}, {$idEmpresa}, 0, 10.00, 0.00)", 'quantidade igual a zero');

if (mysqli_rollback($conexao) === false) {
    encerrar_com_erro('Falha ao desfazer os dados de teste.', $conexao);
}
mysqli_close($conexao);
echo 'OK: testes de integridade concluídos e transação revertida.' . PHP_EOL;
