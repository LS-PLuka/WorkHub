<?php

require __DIR__ . '/../controller/empresa_controller.php';

function encerrar_teste_com_erro($mensagem, $conexao = null)
{
    if ($conexao !== null) { mysqli_rollback($conexao); mysqli_close($conexao); }
    fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
    exit(1);
}

function afirmar_teste($condicao, $mensagem, $conexao)
{
    if (!$condicao) { encerrar_teste_com_erro($mensagem, $conexao); }
    echo "OK: {$mensagem}" . PHP_EOL;
}

function gerar_cnpj_valido_teste()
{
    $baseCnpj = str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT) . '0001';
    $pesos = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    for ($indice = 0; $indice < 12; $indice++) { $soma += (int) $baseCnpj[$indice] * $pesos[$indice]; }
    $primeiroDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    $pesos = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    for ($indice = 0; $indice < 12; $indice++) { $soma += (int) $baseCnpj[$indice] * $pesos[$indice]; }
    $soma += $primeiroDigito * $pesos[12];
    $segundoDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    return $baseCnpj . $primeiroDigito . $segundoDigito;
}

function esperar_rejeicao_teste($conexao, $sql, $mensagem)
{
    afirmar_teste(mysqli_query($conexao, $sql) === false, $mensagem, $conexao);
}

$conexao = conectar_banco_dados();
if ($conexao === false) { encerrar_teste_com_erro('conexão com o banco'); }
$cnpj = gerar_cnpj_valido_teste();
$sufixoEmail = bin2hex(random_bytes(6));
$empresa = ['nome_fantasia' => 'Empresa de teste', 'razao_social' => 'Empresa de teste LTDA', 'cnpj' => $cnpj, 'email' => "empresa.{$sufixoEmail}@workhub.test", 'telefone' => '11999999999'];
$proprietario = ['nome' => 'Responsável de teste', 'email' => "responsavel.{$sufixoEmail}@workhub.test", 'hash_senha' => password_hash('senha-segura-123', PASSWORD_BCRYPT)];

afirmar_teste(cnpj_eh_valido($cnpj), 'CNPJ válido é aceito', $conexao);
afirmar_teste(!cnpj_eh_valido('12345678000100'), 'CNPJ inválido é rejeitado', $conexao);
$formularioVazio = array_fill_keys(['nome_fantasia', 'razao_social', 'cnpj', 'email_empresa', 'telefone', 'nome_responsavel', 'email_responsavel'], '');
afirmar_teste(count(validar_cadastro_empresa($formularioVazio, '', '')) > 0, 'campos obrigatórios são validados', $conexao);
afirmar_teste(count(validar_cadastro_empresa(array_merge($formularioVazio, ['cnpj' => $cnpj]), 'senha-segura-123', 'outra-senha')) > 0, 'senhas diferentes são rejeitadas', $conexao);
$cadastro = cadastrar_empresa_com_proprietario($conexao, $empresa, $proprietario, false);
afirmar_teste($cadastro['sucesso'], 'cadastro válido cria empresa e Proprietário na transação', $conexao);
$resultado = mysqli_query($conexao, "SELECT perfil, senha, ativo FROM usuarios WHERE id = {$cadastro['id_proprietario']}");
$usuario = mysqli_fetch_assoc($resultado);
mysqli_free_result($resultado);
afirmar_teste($usuario['perfil'] === 'PROPRIETARIO' && (int) $usuario['ativo'] === 1, 'Proprietário recebe perfil e status corretos', $conexao);
afirmar_teste(password_verify('senha-segura-123', $usuario['senha']) && password_get_info($usuario['senha'])['algoName'] === 'bcrypt', 'senha é armazenada com hash bcrypt', $conexao);
esperar_rejeicao_teste($conexao, "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Duplicada', 'Duplicada LTDA', '{$cnpj}', 'duplicada.{$sufixoEmail}@workhub.test', '11999999998')", 'CNPJ duplicado é rejeitado');
esperar_rejeicao_teste($conexao, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$cadastro['id_empresa']}, 'Duplicado', '{$proprietario['email']}', 'hash-de-teste', 'GERENTE')", 'e-mail de usuário duplicado é rejeitado');
esperar_rejeicao_teste($conexao, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$cadastro['id_empresa']}, 'Segundo', 'segundo.{$sufixoEmail}@workhub.test', 'hash-de-teste', 'PROPRIETARIO')", 'segundo Proprietário é rejeitado');
mysqli_rollback($conexao);

$proprietarioComFalha = $proprietario;
$proprietarioComFalha['email'] = str_repeat('a', 145) . '@teste.com';
$cnpjComFalha = gerar_cnpj_valido_teste();
$cadastroComFalha = cadastrar_empresa_com_proprietario($conexao, array_merge($empresa, ['cnpj' => $cnpjComFalha]), $proprietarioComFalha, false);
afirmar_teste(!$cadastroComFalha['sucesso'], 'falha ao criar usuário é reportada', $conexao);
$comandoVerificacao = mysqli_prepare($conexao, 'SELECT id FROM empresas WHERE cnpj = ?');
$empresaDesfeita = false;
mysqli_stmt_bind_param($comandoVerificacao, 's', $cnpjComFalha);
if (mysqli_stmt_execute($comandoVerificacao)) { mysqli_stmt_store_result($comandoVerificacao); $empresaDesfeita = mysqli_stmt_num_rows($comandoVerificacao) === 0; }
mysqli_stmt_close($comandoVerificacao);
afirmar_teste($empresaDesfeita, 'falha ao criar usuário desfaz a empresa', $conexao);
mysqli_close($conexao);
echo 'OK: testes finalizados sem manter dados fictícios.' . PHP_EOL;

