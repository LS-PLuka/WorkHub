<?php

require_once __DIR__ . "/../config/banco_dados.php";
require_once __DIR__ . "/../config/sessao.php";
require_once __DIR__ . "/../model/empresa.php";

function escapar_html($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function normalizar_cnpj($cnpj)
{
    return preg_match('/^\d{14}$/', $cnpj) ? $cnpj : preg_replace('/\D/', '', $cnpj);
}

function cnpj_tem_formato_aceito($cnpj)
{
    return preg_match('/^(\d{14}|\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2})$/', $cnpj) === 1;
}

function formatar_cnpj($cnpj)
{
    $cnpj = preg_replace('/\D/', '', (string) $cnpj);

    if (strlen($cnpj) !== 14) {
        return $cnpj;
    }

    return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/'
        . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
}

function telefone_tem_formato_aceito($telefone)
{
    return preg_match('/^(\d{11}|\(\d{2}\) \d{5}-\d{4})$/', $telefone) === 1;
}

function normalizar_telefone($telefone)
{
    return preg_replace('/\D/', '', $telefone);
}

function ddd_brasileiro_eh_valido($ddd)
{
    $dddsValidos = [
        '11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28',
        '31', '32', '33', '34', '35', '37', '38', '41', '42', '43', '44', '45', '46', '47', '48', '49',
        '51', '53', '54', '55', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73', '74',
        '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94',
        '95', '96', '97', '98', '99',
    ];

    return in_array($ddd, $dddsValidos, true);
}

function telefone_celular_eh_valido($telefone)
{
    if (!telefone_tem_formato_aceito($telefone)) {
        return false;
    }

    $telefoneNormalizado = normalizar_telefone($telefone);

    return ddd_brasileiro_eh_valido(substr($telefoneNormalizado, 0, 2)) && $telefoneNormalizado[2] === '9';
}

function formatar_telefone($telefone)
{
    $telefone = normalizar_telefone((string) $telefone);

    if (strlen($telefone) !== 11) {
        return $telefone;
    }

    return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
}

function senha_tem_tamanho_valido($senha)
{
    return strlen($senha) >= 8 && strlen($senha) <= 72;
}

function cnpj_eh_valido($cnpj)
{
    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }

    $pesosPrimeiroDigito = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $pesosSegundoDigito = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;

    for ($indice = 0; $indice < 12; $indice++) {
        $soma += (int) $cnpj[$indice] * $pesosPrimeiroDigito[$indice];
    }

    $primeiroDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    $soma = 0;

    for ($indice = 0; $indice < 12; $indice++) {
        $soma += (int) $cnpj[$indice] * $pesosSegundoDigito[$indice];
    }
    $soma += $primeiroDigito * $pesosSegundoDigito[12];
    $segundoDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);

    return (int) $cnpj[12] === $primeiroDigito && (int) $cnpj[13] === $segundoDigito;
}

function validar_cadastro_empresa($formulario, $senha, $confirmacaoSenha)
{
    $erros = [];
    $rotulosObrigatorios = [
        "nome_fantasia" => "Nome fantasia",
        "razao_social" => "Razão social",
        "cnpj" => "CNPJ",
        "email_empresa" => "E-mail da empresa",
        "telefone" => "Telefone",
        "nome_responsavel" => "Nome do responsável",
        "email_responsavel" => "E-mail do responsável",
    ];

    foreach ($rotulosObrigatorios as $campo => $rotulo) {
        if ($formulario[$campo] === "") {
            $erros[] = "{$rotulo} é obrigatório.";
        }
    }

    if ($senha === "") {
        $erros[] = "Senha é obrigatória.";
    }
    if ($confirmacaoSenha === "") {
        $erros[] = "Confirmação de senha é obrigatória.";
    }

    $cnpjNormalizado = normalizar_cnpj($formulario["cnpj"]);
    if ($formulario["cnpj"] !== "" && !cnpj_tem_formato_aceito($formulario["cnpj"])) {
        $erros[] = "Informe o CNPJ com 14 dígitos ou no formato XX.XXX.XXX/XXXX-XX.";
    } elseif ($formulario["cnpj"] !== "" && !cnpj_eh_valido($cnpjNormalizado)) {
        $erros[] = "Informe um CNPJ válido.";
    }

    if ($formulario["telefone"] !== "" && !telefone_celular_eh_valido($formulario["telefone"])) {
        $erros[] = "Informe um celular brasileiro válido no formato (99) 99999-9999.";
    }

    foreach (
        ["email_empresa" => "E-mail da empresa", "email_responsavel" => "E-mail do responsável"]
        as $campo => $rotulo
    ) {
        if ($formulario[$campo] !== "" && filter_var($formulario[$campo], FILTER_VALIDATE_EMAIL) === false) {
            $erros[] = "{$rotulo} é inválido.";
        }
    }

    $limites = [
        "nome_fantasia" => 150,
        "razao_social" => 200,
        "email_empresa" => 150,
        "telefone" => 20,
        "nome_responsavel" => 150,
        "email_responsavel" => 150,
    ];
    foreach ($limites as $campo => $limite) {
        if (strlen($formulario[$campo]) > $limite) {
            $erros[] = "Um dos campos informados ultrapassa o tamanho permitido.";
            break;
        }
    }

    if ($senha !== "" && !senha_tem_tamanho_valido($senha)) {
        $erros[] = "A senha deve ter entre 8 e 72 caracteres.";
    }
    if ($senha !== "" && $confirmacaoSenha !== "" && !hash_equals($senha, $confirmacaoSenha)) {
        $erros[] = "A confirmação de senha não confere.";
    }

    return $erros;
}

function exibir_cadastro_empresa()
{
    iniciar_sessao_segura();

    if (empty($_SESSION["cadastro_empresa_token_csrf"])) {
        $_SESSION["cadastro_empresa_token_csrf"] = bin2hex(random_bytes(32));
    }

    $tituloPagina = "Cadastrar empresa | WorkHub";
    $erros = [];
    $cadastroConcluido = !empty($_SESSION["cadastro_empresa_concluido"]);
    unset($_SESSION["cadastro_empresa_concluido"]);
    $formulario = [
        "nome_fantasia" => "",
        "razao_social" => "",
        "cnpj" => "",
        "email_empresa" => "",
        "telefone" => "",
        "nome_responsavel" => "",
        "email_responsavel" => "",
    ];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        foreach ($formulario as $campo => $valor) {
            $formulario[$campo] = trim($_POST[$campo] ?? "");
        }
        $senha = $_POST["senha"] ?? "";
        $confirmacaoSenha = $_POST["confirmacao_senha"] ?? "";
        $tokenCsrf = $_POST["token_csrf"] ?? "";

        if (!is_string($tokenCsrf) || !hash_equals($_SESSION["cadastro_empresa_token_csrf"], $tokenCsrf)) {
            $erros[] = "Não foi possível validar o envio do formulário. Atualize a página e tente novamente.";
        }

        $cnpjNormalizado = normalizar_cnpj($formulario["cnpj"]);
        $telefoneNormalizado = normalizar_telefone($formulario["telefone"]);
        $erros = array_merge($erros, validar_cadastro_empresa($formulario, $senha, $confirmacaoSenha));

        if (!$erros) {
            $conexao = conectar_banco_dados();

            if ($conexao === false) {
                $erros[] = "Não foi possível concluir o cadastro neste momento. Tente novamente mais tarde.";
            } else {
                $emailProprietario = strtolower($formulario["email_responsavel"]);

                if (cnpj_empresa_existe($conexao, $cnpjNormalizado)) {
                    $erros[] = "Este CNPJ já está cadastrado.";
                }
                if (email_usuario_existe($conexao, $emailProprietario)) {
                    $erros[] = "Este e-mail de responsável já está cadastrado.";
                }

                if (!$erros) {
                    $hashSenha = password_hash($senha, PASSWORD_BCRYPT);

                    if ($hashSenha === false) {
                        $erros[] = "Não foi possível concluir o cadastro neste momento. Tente novamente mais tarde.";
                    } else {
                        $resultado = cadastrar_empresa_com_proprietario(
                            $conexao,
                            [
                                "nome_fantasia" => $formulario["nome_fantasia"],
                                "razao_social" => $formulario["razao_social"],
                                "cnpj" => $cnpjNormalizado,
                                "email" => strtolower($formulario["email_empresa"]),
                                "telefone" => $telefoneNormalizado,
                            ],
                            [
                                "nome" => $formulario["nome_responsavel"],
                                "email" => $emailProprietario,
                                "hash_senha" => $hashSenha,
                            ],
                        );

                        if ($resultado["sucesso"]) {
                            mysqli_close($conexao);
                            $_SESSION["cadastro_empresa_concluido"] = true;
                            header("Location: /cadastro-empresa");
                            exit();
                        }

                        $erros[] = "Não foi possível concluir o cadastro. Verifique os dados e tente novamente.";
                    }
                }

                mysqli_close($conexao);
            }
        }
    }

    require __DIR__ . "/../view/cadastro_empresa.php";
}
