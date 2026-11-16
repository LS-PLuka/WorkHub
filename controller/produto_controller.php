<?php
require_once __DIR__ . '/../config/sessao.php';
require_once __DIR__ . '/../model/produto.php';
require_once __DIR__ . '/empresa_controller.php';

function normalizar_preco($preco)
{
    $preco = trim($preco);
    if (!preg_match('/^\d+(?:[,.]\d{1,2})?$/', $preco)) { return false; }
    $partes = preg_split('/[,.]/', $preco);
    $inteiro = ltrim($partes[0], '0');
    $inteiro = $inteiro === '' ? '0' : $inteiro;
    if (strlen($inteiro) > 10) { return false; }
    $decimal = isset($partes[1]) ? str_pad($partes[1], 2, '0') : '00';
    return $inteiro . '.' . $decimal;
}
function formatar_preco($preco)
{
    $partes = explode('.', (string) $preco);
    $inteiro = $partes[0];
    $grupos = [];
    while (strlen($inteiro) > 3) {
        array_unshift($grupos, substr($inteiro, -3));
        $inteiro = substr($inteiro, 0, -3);
    }
    array_unshift($grupos, $inteiro);
    return implode('.', $grupos) . ',' . str_pad($partes[1] ?? '00', 2, '0');
}
function validar_dados_produto($dadosProduto)
{
    $erros = [];
    if ($dadosProduto['nome'] === '') { $erros[] = 'Nome do produto é obrigatório.'; }
    if (strlen($dadosProduto['nome']) > 150) { $erros[] = 'Nome do produto ultrapassa o tamanho permitido.'; }
    if (strlen($dadosProduto['descricao']) > 65535) { $erros[] = 'Descrição ultrapassa o tamanho permitido.'; }
    if ($dadosProduto['preco'] === false) { $erros[] = 'Informe um preço válido e não negativo.'; }
    return $erros;
}
function pode_gerenciar_produtos($contexto) { return verificar_permissao(['PROPRIETARIO', 'GERENTE'], $contexto); }
function exibir_produtos()
{
    $contexto = exigir_autenticacao();
    $pesquisa = trim($_GET['pesquisa'] ?? '');
    $conexao = conectar_banco_dados();
    $produtos = $conexao === false ? [] : listar_produtos_empresa($conexao, (int) $contexto['empresa_id'], $pesquisa);
    if ($conexao !== false) { mysqli_close($conexao); }
    $tituloPagina = 'Produtos | WorkHub';
    $podeGerenciar = pode_gerenciar_produtos($contexto);
    $mensagem = isset($_GET['cadastro']) ? 'Produto cadastrado com sucesso.' : (isset($_GET['edicao']) ? 'Produto atualizado com sucesso.' : '');
    require __DIR__ . '/../view/produtos.php';
}
function exibir_cadastro_produto()
{
    $contexto = exigir_permissao(['PROPRIETARIO', 'GERENTE']);
    $tituloPagina = 'Cadastrar produto | WorkHub'; $erros = []; $dadosProduto = ['nome'=>'','descricao'=>'','preco'=>''];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dadosProduto = ['nome'=>trim($_POST['nome'] ?? ''),'descricao'=>trim($_POST['descricao'] ?? ''),'preco'=>normalizar_preco($_POST['preco'] ?? '')];
        if (!validar_token_csrf('cadastro_produto', $_POST['token_csrf'] ?? '')) { $erros[]='Não foi possível validar o envio do formulário.'; }
        $erros = array_merge($erros, validar_dados_produto($dadosProduto));
        if (!$erros) {
            $conexao=conectar_banco_dados(); $sucesso=$conexao!==false && cadastrar_produto($conexao,(int)$contexto['empresa_id'],$dadosProduto['nome'],$dadosProduto['descricao'],$dadosProduto['preco']);
            if($conexao!==false){mysqli_close($conexao);} if($sucesso){header('Location: /produtos?cadastro=1');exit;} $erros[]='Não foi possível concluir o cadastro.';
        }
    }
    $tokenCsrf=gerar_token_csrf('cadastro_produto'); require __DIR__ . '/../view/cadastro_produto.php';
}
function exibir_edicao_produto()
{
    $contexto=exigir_permissao(['PROPRIETARIO','GERENTE']); $idProduto=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT); $conexao=conectar_banco_dados();
    $produto=($idProduto && $conexao!==false)?buscar_produto_empresa($conexao,$idProduto,(int)$contexto['empresa_id']):false;
    if($produto===false){if($conexao!==false){mysqli_close($conexao);}http_response_code(404);echo 'Produto não encontrado.';return;}
    $tituloPagina='Editar produto | WorkHub';$erros=[];
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $dadosProduto=['nome'=>trim($_POST['nome']??''),'descricao'=>trim($_POST['descricao']??''),'preco'=>normalizar_preco($_POST['preco']??'')];
        if(!validar_token_csrf('edicao_produto_'.$produto['id'],$_POST['token_csrf']??'')){$erros[]='Não foi possível validar o envio do formulário.';}
        $erros=array_merge($erros,validar_dados_produto($dadosProduto));
        if(!$erros && atualizar_produto_empresa($conexao,(int)$produto['id'],(int)$contexto['empresa_id'],$dadosProduto['nome'],$dadosProduto['descricao'],$dadosProduto['preco']) ){mysqli_close($conexao);header('Location: /produtos?edicao=1');exit;}
        if(!$erros){$erros[]='Não foi possível atualizar o produto.';} $produto=array_merge($produto,$dadosProduto);
    }
    mysqli_close($conexao);$tokenCsrf=gerar_token_csrf('edicao_produto_'.$produto['id']);require __DIR__ . '/../view/edicao_produto.php';
}
function alterar_situacao_produto()
{
    $contexto=exigir_permissao(['PROPRIETARIO','GERENTE']);
    if($_SERVER['REQUEST_METHOD']!=='POST'||!validar_token_csrf('situacao_produto',$_POST['token_csrf']??'')){http_response_code(403);echo 'Solicitação inválida.';return;}
    $idProduto=filter_input(INPUT_POST,'id_produto',FILTER_VALIDATE_INT);$acao=$_POST['acao']??'';
    if(!$idProduto||!in_array($acao,['ativar','desativar'],true)){http_response_code(404);echo 'Produto não encontrado.';return;}
    $conexao=conectar_banco_dados();$produto=$conexao===false?false:buscar_produto_empresa($conexao,$idProduto,(int)$contexto['empresa_id']);
    if($produto===false){if($conexao!==false){mysqli_close($conexao);}http_response_code(404);echo 'Produto não encontrado.';return;}
    alterar_situacao_produto_empresa($conexao,$idProduto,(int)$contexto['empresa_id'],$acao==='ativar'?1:0);mysqli_close($conexao);header('Location: /produtos');exit;
}
