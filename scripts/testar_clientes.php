<?php
require __DIR__ . "/../controller/cliente_controller.php";
function afirmar_clientes($c, $m, $x)
{
    if (!$c) {
        mysqli_rollback($x);
        mysqli_close($x);
        fwrite(STDERR, "FALHOU: {$m}" . PHP_EOL);
        exit(1);
    }
    echo "OK: {$m}" . PHP_EOL;
}
function cnpj_cliente_teste()
{
    $base = str_pad((string) random_int(0, 999999999999), 12, "0", STR_PAD_LEFT);
    $p1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $p2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $s = 0;
    foreach ($p1 as $i => $p) {
        $s += (int) $base[$i] * $p;
    }
    $d1 = $s % 11 < 2 ? 0 : 11 - ($s % 11);
    $s = 0;
    foreach ($p2 as $i => $p) {
        $s += (int) ($base . $d1)[$i] * $p;
    }
    return $base . $d1 . ($s % 11 < 2 ? 0 : 11 - ($s % 11));
}
$x = conectar_banco_dados();
if ($x === false || !mysqli_begin_transaction($x)) {
    exit(1);
}
$s = bin2hex(random_bytes(5));
iniciar_sessao_segura();
$_SESSION["tokens_csrf"]["cadastro_cliente"] = "token-cliente";
afirmar_clientes(
    !validar_token_csrf("cadastro_cliente", "invalido"),
    "CSRF inválido é rejeitado na escrita de clientes",
    $x,
);
$cnpjA = cnpj_cliente_teste();
$cnpjB = cnpj_cliente_teste();
mysqli_query(
    $x,
    "INSERT INTO empresas (nome_fantasia,razao_social,cnpj,email,telefone) VALUES ('Clientes A','Clientes A LTDA','$cnpjA','a.$s@workhub.test','11999999999')",
);
$a = mysqli_insert_id($x);
mysqli_query(
    $x,
    "INSERT INTO empresas (nome_fantasia,razao_social,cnpj,email,telefone) VALUES ('Clientes B','Clientes B LTDA','$cnpjB','b.$s@workhub.test','11999999998')",
);
$b = mysqli_insert_id($x);
afirmar_clientes(
    count(validar_dados_cliente(["nome" => "", "telefone" => "123", "email" => "invalido"])) > 0,
    "nome, telefone e e-mail inválidos são rejeitados",
    $x,
);
afirmar_clientes(
    cadastrar_cliente($x, $a, "Cliente gerente", "(11) 99999-9999", "cliente.$s@workhub.test"),
    "Proprietário ou Gerente cadastra cliente",
    $x,
);
$id = mysqli_insert_id($x);
afirmar_clientes(
    pode_gerenciar_clientes(["perfil" => "GERENTE"]) &&
        atualizar_cliente_empresa($x, $id, $a, "Cliente gerenciado", "11999999999", null),
    "Gerente pode editar cliente",
    $x,
);
afirmar_clientes(buscar_cliente_empresa($x, $id, $a) !== false, "cliente pertence à empresa correta", $x);
afirmar_clientes(
    atualizar_cliente_empresa($x, $id, $a, "Cliente editado", "11999999999", null),
    "cliente é editado",
    $x,
);
afirmar_clientes(count(listar_clientes_empresa($x, $a, "editado")) === 1, "busca por nome funciona", $x);
afirmar_clientes(alterar_situacao_cliente_empresa($x, $id, $a, 0), "desativação altera ativo para zero", $x);
$cl = buscar_cliente_empresa($x, $id, $a);
afirmar_clientes($cl !== false && (int) $cl["ativo"] === 0, "cliente desativado continua armazenado", $x);
afirmar_clientes(
    !atualizar_cliente_empresa($x, $id, $a, "Cliente indevido", "11999999999", null),
    "cliente desativado não pode ser editado por requisição direta",
    $x,
);
afirmar_clientes(alterar_situacao_cliente_empresa($x, $id, $a, 1), "reativação altera ativo para um", $x);
afirmar_clientes(
    buscar_cliente_empresa($x, $id, $b) === false && !alterar_situacao_cliente_empresa($x, $id, $b, 0),
    "empresa B não consulta nem altera cliente da empresa A",
    $x,
);
afirmar_clientes(
    !verificar_permissao(["PROPRIETARIO", "GERENTE"], ["perfil" => "FUNCIONARIO"]),
    "Funcionário não recebe permissão administrativa",
    $x,
);
mysqli_rollback($x);
mysqli_close($x);
echo "OK: testes de clientes concluídos sem manter dados fictícios." . PHP_EOL;
