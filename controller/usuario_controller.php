<?php

require_once __DIR__ . '/../config/sessao.php';
require_once __DIR__ . '/../model/usuario.php';
require_once __DIR__ . '/empresa_controller.php';

function validar_dados_usuario($dadosUsuario, $senha = null, $confirmacaoSenha = null)
{
    $erros = [];

    if ($dadosUsuario['nome'] === '') { $erros[] = 'Nome é obrigatório.'; }
    if (strlen($dadosUsuario['nome']) > 150) { $erros[] = 'Nome ultrapassa o tamanho permitido.'; }
    if (filter_var($dadosUsuario['email'], FILTER_VALIDATE_EMAIL) === false || strlen($dadosUsuario['email']) > 150) { $erros[] = 'E-mail é inválido.'; }
    if (!in_array($dadosUsuario['perfil'], ['GERENTE', 'FUNCIONARIO'], true)) { $erros[] = 'Perfil inválido.'; }

    if ($senha !== null) {
        if (strlen($senha) < 8) { $erros[] = 'A senha deve ter pelo menos 8 caracteres.'; }
        if (!hash_equals($senha, $confirmacaoSenha)) { $erros[] = 'A confirmação de senha não confere.'; }
    }

    return $erros;
}

function obter_usuario_da_requisicao($conexao, $contexto)
{
    $idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($idUsuario === false || $idUsuario === null || $idUsuario < 1) {
        return false;
    }

    return buscar_usuario_empresa($conexao, $idUsuario, (int) $contexto['empresa_id']);
}

function exibir_usuarios()
{
    $contexto = exigir_permissao(['PROPRIETARIO']);
    $pesquisa = trim($_GET['pesquisa'] ?? '');
    $conexao = conectar_banco_dados();
    $usuarios = $conexao === false ? [] : listar_usuarios_empresa($conexao, (int) $contexto['empresa_id'], $pesquisa);
    if ($conexao !== false) { mysqli_close($conexao); }

    $tituloPagina = 'Usuários | WorkHub';
    $mensagem = isset($_GET['cadastro']) ? 'Usuário cadastrado com sucesso.' : (isset($_GET['edicao']) ? 'Usuário atualizado com sucesso.' : '');
    require __DIR__ . '/../view/usuarios.php';
}

function exibir_cadastro_usuario()
{
    $contexto = exigir_permissao(['PROPRIETARIO']);
    $tituloPagina = 'Cadastrar usuário | WorkHub';
    $erros = [];
    $dadosUsuario = ['nome' => '', 'email' => '', 'perfil' => 'FUNCIONARIO'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dadosUsuario = [
            'nome' => trim($_POST['nome'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'perfil' => $_POST['perfil'] ?? '',
        ];
        $senha = $_POST['senha'] ?? '';
        $confirmacaoSenha = $_POST['confirmacao_senha'] ?? '';

        if (!validar_token_csrf('cadastro_usuario', $_POST['token_csrf'] ?? '')) {
            $erros[] = 'Não foi possível validar o envio do formulário.';
        }
        $erros = array_merge($erros, validar_dados_usuario($dadosUsuario, $senha, $confirmacaoSenha));

        if (!$erros) {
            $conexao = conectar_banco_dados();
            if ($conexao === false) {
                $erros[] = 'Não foi possível concluir o cadastro.';
            } elseif (email_usuario_existe($conexao, $dadosUsuario['email'])) {
                $erros[] = 'Este e-mail já está cadastrado.';
                mysqli_close($conexao);
            } else {
                $hashSenha = password_hash($senha, PASSWORD_BCRYPT);
                $sucesso = $hashSenha !== false && cadastrar_usuario($conexao, (int) $contexto['empresa_id'], $dadosUsuario['nome'], $dadosUsuario['email'], $hashSenha, $dadosUsuario['perfil']);
                mysqli_close($conexao);

                if ($sucesso) {
                    header('Location: /usuarios?cadastro=1');
                    exit;
                }
                $erros[] = 'Não foi possível concluir o cadastro.';
            }
        }
    }

    $tokenCsrf = gerar_token_csrf('cadastro_usuario');
    require __DIR__ . '/../view/cadastro_usuario.php';
}

function exibir_edicao_usuario()
{
    $contexto = exigir_permissao(['PROPRIETARIO']);
    $conexao = conectar_banco_dados();
    $usuario = $conexao === false ? false : obter_usuario_da_requisicao($conexao, $contexto);

    if ($usuario === false) {
        if ($conexao !== false) { mysqli_close($conexao); }
        http_response_code(404);
        echo 'Usuário não encontrado.';
        return;
    }
    if ($usuario['perfil'] === 'PROPRIETARIO') {
        mysqli_close($conexao);
        http_response_code(403);
        echo 'O Proprietário não pode ser alterado neste módulo.';
        return;
    }

    $tituloPagina = 'Editar usuário | WorkHub';
    $erros = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dadosUsuario = ['nome' => trim($_POST['nome'] ?? ''), 'email' => strtolower(trim($_POST['email'] ?? '')), 'perfil' => $_POST['perfil'] ?? ''];
        if (!validar_token_csrf('edicao_usuario_' . $usuario['id'], $_POST['token_csrf'] ?? '')) {
            $erros[] = 'Não foi possível validar o envio do formulário.';
        }
        $erros = array_merge($erros, validar_dados_usuario($dadosUsuario));

        if (!$erros && email_usuario_existe_para_outro($conexao, $dadosUsuario['email'], (int) $usuario['id'])) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
        if (!$erros) {
            if (atualizar_usuario_empresa($conexao, (int) $usuario['id'], (int) $contexto['empresa_id'], $dadosUsuario['nome'], $dadosUsuario['email'], $dadosUsuario['perfil'])) {
                mysqli_close($conexao);
                header('Location: /usuarios?edicao=1');
                exit;
            }
            $erros[] = 'Não foi possível atualizar o usuário.';
        }
        $usuario = array_merge($usuario, $dadosUsuario);
    }

    mysqli_close($conexao);
    $tokenCsrf = gerar_token_csrf('edicao_usuario_' . $usuario['id']);
    require __DIR__ . '/../view/edicao_usuario.php';
}

function alterar_situacao_usuario()
{
    $contexto = exigir_permissao(['PROPRIETARIO']);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validar_token_csrf('situacao_usuario', $_POST['token_csrf'] ?? '')) {
        http_response_code(403);
        echo 'Solicitação inválida.';
        return;
    }

    $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $acao = $_POST['acao'] ?? '';
    if ($idUsuario === false || $idUsuario === null || !in_array($acao, ['ativar', 'desativar'], true)) {
        http_response_code(404);
        echo 'Usuário não encontrado.';
        return;
    }

    $conexao = conectar_banco_dados();
    $usuario = $conexao === false ? false : buscar_usuario_empresa($conexao, $idUsuario, (int) $contexto['empresa_id']);
    if ($usuario === false || $usuario['perfil'] === 'PROPRIETARIO') {
        if ($conexao !== false) { mysqli_close($conexao); }
        http_response_code(404);
        echo 'Usuário não encontrado.';
        return;
    }

    alterar_situacao_usuario_empresa($conexao, $idUsuario, (int) $contexto['empresa_id'], $acao === 'ativar' ? 1 : 0);
    mysqli_close($conexao);
    header('Location: /usuarios');
    exit;
}

