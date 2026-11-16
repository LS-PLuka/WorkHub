<?php

function company_cnpj_exists($connection, $cnpj)
{
    $statement = mysqli_prepare($connection, 'SELECT id FROM empresas WHERE cnpj = ? LIMIT 1');

    if ($statement === false) {
        return false;
    }

    mysqli_stmt_bind_param($statement, 's', $cnpj);
    $executed = mysqli_stmt_execute($statement);
    mysqli_stmt_store_result($statement);
    $exists = $executed && mysqli_stmt_num_rows($statement) > 0;
    mysqli_stmt_close($statement);

    return $exists;
}

function company_user_email_exists($connection, $email)
{
    $statement = mysqli_prepare($connection, 'SELECT id FROM usuarios WHERE email = ? LIMIT 1');

    if ($statement === false) {
        return false;
    }

    mysqli_stmt_bind_param($statement, 's', $email);
    $executed = mysqli_stmt_execute($statement);
    mysqli_stmt_store_result($statement);
    $exists = $executed && mysqli_stmt_num_rows($statement) > 0;
    mysqli_stmt_close($statement);

    return $exists;
}

/**
 * Creates an active company and its active Proprietário atomically.
 * The optional commit flag lets the terminal test script inspect data before
 * explicitly rolling its transaction back.
 */
function company_create_with_owner($connection, $company, $owner, $commitTransaction = true)
{
    if (mysqli_begin_transaction($connection) === false) {
        return ['success' => false, 'reason' => 'transaction'];
    }

    $companyStatement = mysqli_prepare(
        $connection,
        'INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone, ativo) VALUES (?, ?, ?, ?, ?, 1)'
    );

    if ($companyStatement === false) {
        mysqli_rollback($connection);
        return ['success' => false, 'reason' => 'company'];
    }

    mysqli_stmt_bind_param(
        $companyStatement,
        'sssss',
        $company['nome_fantasia'],
        $company['razao_social'],
        $company['cnpj'],
        $company['email'],
        $company['telefone']
    );

    if (mysqli_stmt_execute($companyStatement) === false) {
        mysqli_stmt_close($companyStatement);
        mysqli_rollback($connection);
        return ['success' => false, 'reason' => 'company'];
    }

    $companyId = mysqli_insert_id($connection);
    mysqli_stmt_close($companyStatement);

    $ownerStatement = mysqli_prepare(
        $connection,
        "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil, ativo) VALUES (?, ?, ?, ?, 'PROPRIETARIO', 1)"
    );

    if ($ownerStatement === false) {
        mysqli_rollback($connection);
        return ['success' => false, 'reason' => 'owner'];
    }

    mysqli_stmt_bind_param(
        $ownerStatement,
        'isss',
        $companyId,
        $owner['nome'],
        $owner['email'],
        $owner['password_hash']
    );

    if (mysqli_stmt_execute($ownerStatement) === false) {
        mysqli_stmt_close($ownerStatement);
        mysqli_rollback($connection);
        return ['success' => false, 'reason' => 'owner'];
    }

    $ownerId = mysqli_insert_id($connection);
    mysqli_stmt_close($ownerStatement);

    if ($commitTransaction) {
        if (mysqli_commit($connection) === false) {
            mysqli_rollback($connection);
            return ['success' => false, 'reason' => 'commit'];
        }
    }

    return ['success' => true, 'company_id' => $companyId, 'owner_id' => $ownerId];
}
