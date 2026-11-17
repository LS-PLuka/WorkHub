<?php

require __DIR__ . "/../controller/administracao_controller.php";
require_once __DIR__ . "/../controller/autenticacao_controller.php";
require_once __DIR__ . "/../model/usuario.php";

ob_start();

function afirmar_administracao($condicao, $mensagem, $conexao)
{
    if (!$condicao) {
        mysqli_rollback($conexao);
        mysqli_close($conexao);
        fwrite(STDERR, "FALHOU: {$mensagem}" . PHP_EOL);
        exit(1);
    }
    echo "OK: {$mensagem}" . PHP_EOL;
}

function inserir_empresa_administracao($conexao, $sufixo, $letra)
{
    $nome = "Empresa {$letra} {$sufixo}";
    $cnpj = str_pad((string) random_int(0, 99999999999999), 14, "0", STR_PAD_LEFT);
    $email = strtolower($letra) . ".{$sufixo}@workhub.test";
    $telefone = "11999999999";
    $comando = mysqli_prepare($conexao, "INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($comando, "sssss", $nome, $nome, $cnpj, $email, $telefone);
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}

function inserir_usuario_administracao($conexao, $idEmpresa, $perfil, $email, $ativo = 1)
{
    $nome = "Usuário {$perfil}";
    $senha = password_hash("Senha123!", PASSWORD_BCRYPT);
    $comando = mysqli_prepare($conexao, "INSERT INTO usuarios (empresa_id, nome, email, senha, perfil, ativo) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($comando, "issssi", $idEmpresa, $nome, $email, $senha, $perfil, $ativo);
    mysqli_stmt_execute($comando);
    mysqli_stmt_close($comando);
    return mysqli_insert_id($conexao);
}

$conexao = conectar_banco_dados();
if ($conexao === false || !mysqli_begin_transaction($conexao)) {
    exit(1);
}
$indicadoresIniciais = obter_indicadores_administracao($conexao);
$sufixo = bin2hex(random_bytes(5));
$empresaA = inserir_empresa_administracao($conexao, $sufixo, "A");
$empresaB = inserir_empresa_administracao($conexao, $sufixo, "B");
$empresaC = inserir_empresa_administracao($conexao, $sufixo, "C");
$emailAdmin = "admin.{$sufixo}@workhub.test";
$senhaAdmin = "SenhaAdmin123!";
$hashAdmin = password_hash($senhaAdmin, PASSWORD_BCRYPT);
afirmar_administracao(cadastrar_administrador_plataforma($conexao, "Admin", $emailAdmin, $hashAdmin), "administrador CLI é criado", $conexao);
$admin = buscar_administrador_por_email($conexao, $emailAdmin);
afirmar_administracao($admin !== false && str_starts_with($admin["senha"], "$2y$") && password_verify($senhaAdmin, $admin["senha"]), "senha administrativa usa bcrypt", $conexao);
$emailA = "proprietario.{$sufixo}@workhub.test";
$usuarioA = inserir_usuario_administracao($conexao, $empresaA, "PROPRIETARIO", $emailA);
$usuarioAInativo = inserir_usuario_administracao($conexao, $empresaA, "FUNCIONARIO", "inativo.{$sufixo}@workhub.test", 0);
$usuarioB = inserir_usuario_administracao($conexao, $empresaB, "PROPRIETARIO", "proprietario.b.{$sufixo}@workhub.test");

iniciar_sessao_segura();
afirmar_administracao(autenticar_administrador($emailAdmin, $senhaAdmin, $conexao) && administrador_autenticado(), "login administrativo válido funciona", $conexao);
afirmar_administracao(!usuario_autenticado() && administrador_autenticado(), "sessão administrativa não concede acesso empresarial", $conexao);
afirmar_administracao(!autenticar_administrador($emailAdmin, "invalida", $conexao), "senha incorreta é rejeitada", $conexao);
mysqli_query($conexao, "UPDATE administradores_plataforma SET ativo = 0 WHERE id = " . (int) $admin["id"]);
afirmar_administracao(!autenticar_administrador($emailAdmin, $senhaAdmin, $conexao), "administrador desativado é bloqueado", $conexao);
mysqli_query($conexao, "UPDATE administradores_plataforma SET ativo = 1 WHERE id = " . (int) $admin["id"]);
afirmar_administracao(autenticar_usuario($emailA, "Senha123!", $conexao) && !administrador_autenticado(), "usuário empresarial não acessa administração", $conexao);
$_SESSION = [];
afirmar_administracao(!administrador_autenticado(), "administração sem sessão é bloqueada", $conexao);
$tokenEntrada = gerar_token_csrf("entrada_administracao");
$tokenSaida = gerar_token_csrf("saida_administracao");
$tokenSituacao = gerar_token_csrf("situacao_empresa_administracao");
afirmar_administracao(!validar_token_csrf("entrada_administracao", "invalido") && !validar_token_csrf("saida_administracao", "invalido") && !validar_token_csrf("situacao_empresa_administracao", "invalido") && validar_token_csrf("entrada_administracao", $tokenEntrada) && validar_token_csrf("saida_administracao", $tokenSaida) && validar_token_csrf("situacao_empresa_administracao", $tokenSituacao), "CSRF inválido é rejeitado", $conexao);

$indicadores = obter_indicadores_administracao($conexao);
afirmar_administracao(
    (int) $indicadores["total_empresas"] === (int) $indicadoresIniciais["total_empresas"] + 3 &&
        (int) $indicadores["empresas_ativas"] === (int) $indicadoresIniciais["empresas_ativas"] + 3 &&
        (int) $indicadores["empresas_desativadas"] === (int) $indicadoresIniciais["empresas_desativadas"],
    "indicadores globais são corretos",
    $conexao,
);
$empresas = listar_empresas_administracao($conexao, "Empresa A {$sufixo}", "1");
afirmar_administracao(count($empresas) === 1 && (int) $empresas[0]["id"] === $empresaA && buscar_empresa_administracao($conexao, $empresaA) !== false, "busca, filtro e consulta cadastral funcionam", $conexao);
$desativacao = alterar_situacao_empresa_administracao($conexao, $empresaA, 0);
afirmar_administracao($desativacao["sucesso"] && buscar_contexto_usuario_ativo($conexao, $usuarioA) === false && buscar_contexto_usuario_ativo($conexao, $usuarioB) !== false, "desativação bloqueia só a empresa afetada", $conexao);
$reativacao = alterar_situacao_empresa_administracao($conexao, $empresaA, 1);
$usuarioInativo = buscar_usuario_empresa($conexao, $usuarioAInativo, $empresaA);
afirmar_administracao($reativacao["sucesso"] && (int) $usuarioInativo["ativo"] === 0, "reativação preserva usuário individualmente inativo", $conexao);
alterar_situacao_empresa_administracao($conexao, $empresaC, 0);
$reativacaoInvalida = alterar_situacao_empresa_administracao($conexao, $empresaC, 1);
$indicadoresFinais = obter_indicadores_administracao($conexao);
afirmar_administracao(
    !$reativacaoInvalida["sucesso"] &&
        (int) $indicadoresFinais["total_empresas"] === (int) $indicadoresIniciais["total_empresas"] + 3,
    "reativação sem Proprietário é bloqueada sem exclusão",
    $conexao,
);
autenticar_administrador($emailAdmin, $senhaAdmin, $conexao);
encerrar_sessao();
iniciar_sessao_segura();
afirmar_administracao(!administrador_autenticado(), "logout administrativo invalida sessão", $conexao);
mysqli_rollback($conexao);
mysqli_close($conexao);
echo "OK: testes de administração concluídos sem manter dados fictícios." . PHP_EOL;
ob_end_flush();
