<?php

require __DIR__ . '/../controller/company_controller.php';

function company_test_fail($message, $connection = null)
{
    if ($connection !== null) {
        mysqli_rollback($connection);
        mysqli_close($connection);
    }

    fwrite(STDERR, "FALHOU: {$message}" . PHP_EOL);
    exit(1);
}

function company_test_assert($condition, $message, $connection)
{
    if (!$condition) {
        company_test_fail($message, $connection);
    }

    echo "OK: {$message}" . PHP_EOL;
}

function company_test_valid_cnpj()
{
    $base = str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT) . '0001';
    $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $sum = 0;

    for ($index = 0; $index < 12; $index++) {
        $sum += (int) $base[$index] * $weights[$index];
    }
    $firstDigit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
    $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $sum = 0;

    for ($index = 0; $index < 12; $index++) {
        $sum += (int) $base[$index] * $weights[$index];
    }
    $sum += $firstDigit * $weights[12];
    $secondDigit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

    return $base . $firstDigit . $secondDigit;
}

function company_test_expect_rejection($connection, $sql, $message)
{
    company_test_assert(mysqli_query($connection, $sql) === false, $message, $connection);
}

$connection = database_connect();
if ($connection === false) {
    company_test_fail('conexão com o banco');
}

$cnpj = company_test_valid_cnpj();
$emailSuffix = bin2hex(random_bytes(6));
$company = [
    'nome_fantasia' => 'Empresa de teste',
    'razao_social' => 'Empresa de teste LTDA',
    'cnpj' => $cnpj,
    'email' => "empresa.{$emailSuffix}@workhub.test",
    'telefone' => '11999999999',
];
$owner = [
    'nome' => 'Responsável de teste',
    'email' => "responsavel.{$emailSuffix}@workhub.test",
    'password_hash' => password_hash('senha-segura-123', PASSWORD_BCRYPT),
];

company_test_assert(company_is_valid_cnpj($cnpj), 'CNPJ válido é aceito', $connection);
company_test_assert(!company_is_valid_cnpj('12345678000100'), 'CNPJ inválido é rejeitado', $connection);

$emptyForm = array_fill_keys(['nome_fantasia', 'razao_social', 'cnpj', 'email_empresa', 'telefone', 'nome_responsavel', 'email_responsavel'], '');
company_test_assert(count(company_validate_registration($emptyForm, '', '')) > 0, 'campos obrigatórios são validados', $connection);
company_test_assert(count(company_validate_registration(array_merge($emptyForm, ['cnpj' => $cnpj]), 'senha-segura-123', 'outra-senha')) > 0, 'senhas diferentes são rejeitadas', $connection);

$created = company_create_with_owner($connection, $company, $owner, false);
company_test_assert($created['success'], 'cadastro válido cria empresa e Proprietário na transação', $connection);

$result = mysqli_query($connection, "SELECT perfil, senha, ativo FROM usuarios WHERE id = {$created['owner_id']}");
$user = mysqli_fetch_assoc($result);
mysqli_free_result($result);
company_test_assert($user['perfil'] === 'PROPRIETARIO' && (int) $user['ativo'] === 1, 'Proprietário recebe perfil e status corretos', $connection);
company_test_assert(password_verify('senha-segura-123', $user['senha']) && password_get_info($user['senha'])['algoName'] === 'bcrypt', 'senha é armazenada com hash bcrypt', $connection);

company_test_expect_rejection($connection, "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Duplicada', 'Duplicada LTDA', '{$cnpj}', 'duplicada.{$emailSuffix}@workhub.test', '11999999998')", 'CNPJ duplicado é rejeitado');
company_test_expect_rejection($connection, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$created['company_id']}, 'Duplicado', '{$owner['email']}', 'hash-de-teste', 'GERENTE')", 'e-mail de usuário duplicado é rejeitado');
company_test_expect_rejection($connection, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$created['company_id']}, 'Segundo', 'segundo.{$emailSuffix}@workhub.test', 'hash-de-teste', 'PROPRIETARIO')", 'segundo Proprietário é rejeitado');

mysqli_rollback($connection);

$failedOwner = $owner;
$failedOwner['email'] = str_repeat('a', 145) . '@teste.com';
$failedCnpj = company_test_valid_cnpj();
$failed = company_create_with_owner($connection, array_merge($company, ['cnpj' => $failedCnpj]), $failedOwner, false);
company_test_assert(!$failed['success'], 'falha ao criar usuário é reportada', $connection);

$checkStatement = mysqli_prepare($connection, 'SELECT id FROM empresas WHERE cnpj = ?');
$checkResult = false;
mysqli_stmt_bind_param($checkStatement, 's', $failedCnpj);
if (mysqli_stmt_execute($checkStatement)) {
    mysqli_stmt_store_result($checkStatement);
    $checkResult = mysqli_stmt_num_rows($checkStatement) === 0;
}
mysqli_stmt_close($checkStatement);
company_test_assert($checkResult, 'falha ao criar usuário desfaz a empresa', $connection);

mysqli_close($connection);
echo 'OK: testes finalizados sem manter dados fictícios.' . PHP_EOL;
