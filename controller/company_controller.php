<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/company.php';

function company_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function company_normalize_cnpj($cnpj)
{
    return preg_replace('/\D+/', '', $cnpj);
}

function company_is_valid_cnpj($cnpj)
{
    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }

    $weightsFirstDigit = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $weightsSecondDigit = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $sum = 0;

    for ($index = 0; $index < 12; $index++) {
        $sum += (int) $cnpj[$index] * $weightsFirstDigit[$index];
    }

    $firstDigit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
    $sum = 0;

    for ($index = 0; $index < 12; $index++) {
        $sum += (int) $cnpj[$index] * $weightsSecondDigit[$index];
    }
    $sum += $firstDigit * $weightsSecondDigit[12];
    $secondDigit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

    return (int) $cnpj[12] === $firstDigit && (int) $cnpj[13] === $secondDigit;
}

function company_validate_registration($form, $password, $passwordConfirmation)
{
    $errors = [];
    $requiredLabels = [
        'nome_fantasia' => 'Nome fantasia',
        'razao_social' => 'Razão social',
        'cnpj' => 'CNPJ',
        'email_empresa' => 'E-mail da empresa',
        'telefone' => 'Telefone',
        'nome_responsavel' => 'Nome do responsável',
        'email_responsavel' => 'E-mail do responsável',
    ];

    foreach ($requiredLabels as $field => $label) {
        if ($form[$field] === '') {
            $errors[] = "{$label} é obrigatório.";
        }
    }

    if ($password === '') {
        $errors[] = 'Senha é obrigatória.';
    }
    if ($passwordConfirmation === '') {
        $errors[] = 'Confirmação de senha é obrigatória.';
    }

    $normalizedCnpj = company_normalize_cnpj($form['cnpj']);
    if ($form['cnpj'] !== '' && !company_is_valid_cnpj($normalizedCnpj)) {
        $errors[] = 'Informe um CNPJ válido.';
    }

    foreach (['email_empresa' => 'E-mail da empresa', 'email_responsavel' => 'E-mail do responsável'] as $field => $label) {
        if ($form[$field] !== '' && filter_var($form[$field], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = "{$label} é inválido.";
        }
    }

    $limits = [
        'nome_fantasia' => 150,
        'razao_social' => 200,
        'email_empresa' => 150,
        'telefone' => 20,
        'nome_responsavel' => 150,
        'email_responsavel' => 150,
    ];
    foreach ($limits as $field => $limit) {
        if (strlen($form[$field]) > $limit) {
            $errors[] = 'Um dos campos informados ultrapassa o tamanho permitido.';
            break;
        }
    }

    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
    }
    if ($password !== '' && $passwordConfirmation !== '' && !hash_equals($password, $passwordConfirmation)) {
        $errors[] = 'A confirmação de senha não confere.';
    }

    return $errors;
}

function company_register_show()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['company_register_csrf'])) {
        $_SESSION['company_register_csrf'] = bin2hex(random_bytes(32));
    }

    $pageTitle = 'Cadastrar empresa | WorkHub';
    $errors = [];
    $success = !empty($_SESSION['company_register_success']);
    unset($_SESSION['company_register_success']);
    $form = [
        'nome_fantasia' => '',
        'razao_social' => '',
        'cnpj' => '',
        'email_empresa' => '',
        'telefone' => '',
        'nome_responsavel' => '',
        'email_responsavel' => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form = [
            'nome_fantasia' => trim($_POST['nome_fantasia'] ?? ''),
            'razao_social' => trim($_POST['razao_social'] ?? ''),
            'cnpj' => trim($_POST['cnpj'] ?? ''),
            'email_empresa' => trim($_POST['email_empresa'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'nome_responsavel' => trim($_POST['nome_responsavel'] ?? ''),
            'email_responsavel' => trim($_POST['email_responsavel'] ?? ''),
        ];
        $password = $_POST['senha'] ?? '';
        $passwordConfirmation = $_POST['confirmacao_senha'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!is_string($csrfToken) || !hash_equals($_SESSION['company_register_csrf'], $csrfToken)) {
            $errors[] = 'Não foi possível validar o envio do formulário. Atualize a página e tente novamente.';
        }

        $normalizedCnpj = company_normalize_cnpj($form['cnpj']);
        $errors = array_merge($errors, company_validate_registration($form, $password, $passwordConfirmation));

        if (!$errors) {
            $connection = database_connect();

            if ($connection === false) {
                $errors[] = 'Não foi possível concluir o cadastro neste momento. Tente novamente mais tarde.';
            } else {
                $ownerEmail = strtolower($form['email_responsavel']);

                if (company_cnpj_exists($connection, $normalizedCnpj)) {
                    $errors[] = 'Este CNPJ já está cadastrado.';
                }
                if (company_user_email_exists($connection, $ownerEmail)) {
                    $errors[] = 'Este e-mail de responsável já está cadastrado.';
                }

                if (!$errors) {
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                    if ($passwordHash === false) {
                        $errors[] = 'Não foi possível concluir o cadastro neste momento. Tente novamente mais tarde.';
                    } else {
                        $result = company_create_with_owner($connection, [
                            'nome_fantasia' => $form['nome_fantasia'],
                            'razao_social' => $form['razao_social'],
                            'cnpj' => $normalizedCnpj,
                            'email' => strtolower($form['email_empresa']),
                            'telefone' => $form['telefone'],
                        ], [
                            'nome' => $form['nome_responsavel'],
                            'email' => $ownerEmail,
                            'password_hash' => $passwordHash,
                        ]);

                        if ($result['success']) {
                            mysqli_close($connection);
                            $_SESSION['company_register_success'] = true;
                            header('Location: /cadastro-empresa');
                            exit;
                        }

                        $errors[] = 'Não foi possível concluir o cadastro. Verifique os dados e tente novamente.';
                    }
                }

                mysqli_close($connection);
            }
        }
    }

    require __DIR__ . '/../view/register_company.php';
}
