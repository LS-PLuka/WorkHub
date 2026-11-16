<?php
require __DIR__ . "/../controller/usuario_controller.php";
function cnpj_usuario_teste()
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

function afirmar_usuarios($condicao, $mensagem, $conexao)
{
    if (!$condicao) {
        mysqli_rollback($conexao);
        mysqli_close($conexao);
        fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
        exit(1);
    }
    echo "OK: {$mensagem}" . PHP_EOL;
}

$conexao = conectar_banco_dados();
if ($conexao === false || mysqli_begin_transaction($conexao) === false) {
    exit(1);
}
$sufixo = bin2hex(random_bytes(5));
$cnpjA = cnpj_usuario_teste();
$cnpjB = cnpj_usuario_teste();
mysqli_query(
    $conexao,
    "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Empresa A {$sufixo}', 'Empresa A LTDA', '$cnpjA', 'a.{$sufixo}@workhub.test', '11999999999')",
);
$idEmpresaA = mysqli_insert_id($conexao);
mysqli_query(
    $conexao,
    "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Empresa B {$sufixo}', 'Empresa B LTDA', '$cnpjB', 'b.{$sufixo}@workhub.test', '11999999998')",
);
$idEmpresaB = mysqli_insert_id($conexao);
$hashSenha = password_hash("senha-segura-123", PASSWORD_BCRYPT);
mysqli_query(
    $conexao,
    "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$idEmpresaA}, 'Proprietário A', 'proprietario.a.{$sufixo}@workhub.test', '{$hashSenha}', 'PROPRIETARIO')",
);
$idProprietario = mysqli_insert_id($conexao);
mysqli_query(
    $conexao,
    "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$idEmpresaB}, 'Proprietário B', 'proprietario.b.{$sufixo}@workhub.test', '{$hashSenha}', 'PROPRIETARIO')",
);
$gerenteEmail = "gerente.{$sufixo}@workhub.test";
$funcionarioEmail = "funcionario.{$sufixo}@workhub.test";
afirmar_usuarios(
    cadastrar_usuario($conexao, $idEmpresaA, "Gerente teste", $gerenteEmail, $hashSenha, "GERENTE"),
    "Proprietário cadastra Gerente",
    $conexao,
);
$idGerente = mysqli_insert_id($conexao);
afirmar_usuarios(
    cadastrar_usuario($conexao, $idEmpresaA, "Funcionário teste", $funcionarioEmail, $hashSenha, "FUNCIONARIO"),
    "Proprietário cadastra Funcionário",
    $conexao,
);
$idFuncionario = mysqli_insert_id($conexao);
afirmar_usuarios(
    !cadastrar_usuario($conexao, $idEmpresaA, "Duplicado", $gerenteEmail, $hashSenha, "GERENTE"),
    "e-mail duplicado é rejeitado",
    $conexao,
);
afirmar_usuarios(
    count(validar_dados_usuario(["nome" => "", "email" => "invalido", "perfil" => "PROPRIETARIO"], "senha", "outra")) >
        0,
    "dados inválidos, senha divergente e Proprietário são rejeitados",
    $conexao,
);
afirmar_usuarios(
    atualizar_usuario_empresa(
        $conexao,
        $idGerente,
        $idEmpresaA,
        "Gerente editado",
        "gerente.editado.{$sufixo}@workhub.test",
        "FUNCIONARIO",
    ),
    "Proprietário edita usuário",
    $conexao,
);
afirmar_usuarios(
    alterar_situacao_usuario_empresa($conexao, $idFuncionario, $idEmpresaA, 0) &&
        alterar_situacao_usuario_empresa($conexao, $idFuncionario, $idEmpresaA, 1),
    "usuário é desativado e reativado",
    $conexao,
);
afirmar_usuarios(
    !alterar_situacao_usuario_empresa($conexao, $idProprietario, $idEmpresaA, 0),
    "Proprietário não pode ser desativado",
    $conexao,
);
afirmar_usuarios(
    buscar_usuario_empresa($conexao, $idGerente, $idEmpresaB) === false &&
        !alterar_situacao_usuario_empresa($conexao, $idGerente, $idEmpresaB, 0),
    "empresa B não acessa ou altera usuário da empresa A",
    $conexao,
);
afirmar_usuarios(
    count(listar_usuarios_empresa($conexao, $idEmpresaA)) === 3,
    "listagem retorna somente usuários da empresa autenticada",
    $conexao,
);
afirmar_usuarios(
    !verificar_permissao(["PROPRIETARIO"], ["perfil" => "GERENTE"]) &&
        !verificar_permissao(["PROPRIETARIO"], ["perfil" => "FUNCIONARIO"]),
    "Gerente e Funcionário não recebem permissão administrativa",
    $conexao,
);
mysqli_rollback($conexao);
mysqli_close($conexao);
echo "OK: testes de usuários concluídos sem manter dados fictícios." . PHP_EOL;
