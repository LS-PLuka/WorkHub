<?php

require __DIR__ . "/../controller/autenticacao_controller.php";

ob_start();

function afirmar_autenticacao($condicao, $mensagem, $conexao)
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
    fwrite(STDERR, "FALHOU: não foi possível iniciar o teste." . PHP_EOL);
    exit(1);
}

$sufixo = bin2hex(random_bytes(6));
$base = str_pad((string) random_int(0, 999999999999), 12, "0", STR_PAD_LEFT);
$pesosUm = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
$pesosDois = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
$soma = 0;
foreach ($pesosUm as $indice => $peso) {
    $soma += (int) $base[$indice] * $peso;
}
$digitoUm = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
$soma = 0;
foreach ($pesosDois as $indice => $peso) {
    $soma += (int) ($base . $digitoUm)[$indice] * $peso;
}
$cnpj = $base . $digitoUm . ($soma % 11 < 2 ? 0 : 11 - ($soma % 11));
mysqli_query(
    $conexao,
    "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES ('Teste autenticação {$sufixo}', 'Teste autenticação LTDA', '{$cnpj}', 'empresa.{$sufixo}@workhub.test', '11999999999')",
);
$idEmpresa = mysqli_insert_id($conexao);
$senha = "senha-segura-123";
$hashSenha = password_hash($senha, PASSWORD_BCRYPT);
$emailUsuario = "usuario.{$sufixo}@workhub.test";
mysqli_query(
    $conexao,
    "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES ({$idEmpresa}, 'Usuário de teste', '{$emailUsuario}', '{$hashSenha}', 'PROPRIETARIO')",
);
$idUsuario = mysqli_insert_id($conexao);

iniciar_sessao_segura();
$idSessaoAnterior = session_id();
afirmar_autenticacao(autenticar_usuario($emailUsuario, $senha, $conexao), "login com credenciais corretas", $conexao);
afirmar_autenticacao(
    session_id() !== $idSessaoAnterior && (int) $_SESSION["id_usuario"] === $idUsuario,
    "sessão regenerada e dados mínimos armazenados",
    $conexao,
);
afirmar_autenticacao(
    !autenticar_usuario($emailUsuario, "senha-incorreta", $conexao),
    "senha incorreta é rejeitada",
    $conexao,
);
afirmar_autenticacao(
    !autenticar_usuario("inexistente.{$sufixo}@workhub.test", $senha, $conexao),
    "e-mail inexistente é rejeitado",
    $conexao,
);
$contexto = buscar_contexto_usuario_ativo($conexao, $idUsuario);
afirmar_autenticacao(
    $contexto !== false && verificar_permissao(["PROPRIETARIO"], $contexto),
    "permissão do Proprietário é autorizada",
    $conexao,
);
afirmar_autenticacao(!verificar_permissao(["GERENTE"], $contexto), "perfil sem permissão é rejeitado", $conexao);

mysqli_query($conexao, "UPDATE usuarios SET ativo = 0 WHERE id = {$idUsuario}");
afirmar_autenticacao(
    !autenticar_usuario($emailUsuario, $senha, $conexao) &&
        buscar_contexto_usuario_ativo($conexao, $idUsuario) === false,
    "usuário desativado é bloqueado",
    $conexao,
);
mysqli_query($conexao, "UPDATE usuarios SET ativo = 1 WHERE id = {$idUsuario}");
mysqli_query($conexao, "UPDATE empresas SET ativo = 0 WHERE id = {$idEmpresa}");
afirmar_autenticacao(
    !autenticar_usuario($emailUsuario, $senha, $conexao) &&
        buscar_contexto_usuario_ativo($conexao, $idUsuario) === false,
    "empresa desativada é bloqueada",
    $conexao,
);
mysqli_query($conexao, "UPDATE empresas SET ativo = 1 WHERE id = {$idEmpresa}");

$tokenCsrf = gerar_token_csrf("teste");
afirmar_autenticacao(
    validar_token_csrf("teste", $tokenCsrf) && !validar_token_csrf("teste", "invalido"),
    "proteção CSRF valida e rejeita token inválido",
    $conexao,
);
$tokenSaida = gerar_token_csrf("saida_teste");
afirmar_autenticacao(validar_token_csrf("saida_teste", $tokenSaida), "token CSRF de saída é validado", $conexao);
encerrar_sessao();
iniciar_sessao_segura();
afirmar_autenticacao(!usuario_autenticado(), "logout invalida a sessão", $conexao);
$_SESSION["id_usuario"] = $idUsuario;
$_SESSION["ultima_atividade"] = time() - 1801;
afirmar_autenticacao(
    obter_contexto_autenticado() === false && !usuario_autenticado(),
    "sessão expirada por inatividade é invalidada",
    $conexao,
);

mysqli_rollback($conexao);
mysqli_close($conexao);
echo "OK: testes de autenticação concluídos sem manter dados fictícios." . PHP_EOL;
ob_end_flush();
