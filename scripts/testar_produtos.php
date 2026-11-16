<?php
require __DIR__ . '/../controller/produto_controller.php';
function afirmar_produtos($condicao,$mensagem,$conexao){if(!$condicao){mysqli_rollback($conexao);mysqli_close($conexao);fwrite(STDERR,"FALHOU: {$mensagem}".PHP_EOL);exit(1);}echo "OK: {$mensagem}".PHP_EOL;}
$conexao=conectar_banco_dados(); if($conexao===false||!mysqli_begin_transaction($conexao)){exit(1);}
$sufixo=bin2hex(random_bytes(5)); mysqli_query($conexao,"INSERT INTO empresas (nome_fantasia,razao_social,cnpj,email,telefone) VALUES ('Produtos A','Produtos A LTDA','04252011000110','a.$sufixo@workhub.test','11999999999')");$idEmpresaA=mysqli_insert_id($conexao);mysqli_query($conexao,"INSERT INTO empresas (nome_fantasia,razao_social,cnpj,email,telefone) VALUES ('Produtos B','Produtos B LTDA','40688134000161','b.$sufixo@workhub.test','11999999998')");$idEmpresaB=mysqli_insert_id($conexao);
afirmar_produtos(normalizar_preco('12,5')==='12.50'&&normalizar_preco('-1')===false,'preço é validado sem FLOAT',$conexao);
afirmar_produtos(count(validar_dados_produto(['nome'=>'','descricao'=>'','preco'=>false]))>0,'nome vazio e preço negativo são rejeitados',$conexao);
afirmar_produtos(cadastrar_produto($conexao,$idEmpresaA,'Produto gerente','Descrição','10.00'),'Proprietário ou Gerente cadastra produto',$conexao);$idProduto=mysqli_insert_id($conexao);
afirmar_produtos(buscar_produto_empresa($conexao,$idProduto,$idEmpresaA)!==false,'produto pertence à empresa correta',$conexao);
afirmar_produtos(atualizar_produto_empresa($conexao,$idProduto,$idEmpresaA,'Produto editado','Nova','15.25'),'Produto é editado',$conexao);
afirmar_produtos(count(listar_produtos_empresa($conexao,$idEmpresaA,'editado'))===1,'busca por nome funciona',$conexao);
afirmar_produtos(alterar_situacao_produto_empresa($conexao,$idProduto,$idEmpresaA,0),'desativação altera ativo para zero',$conexao);
$produto=buscar_produto_empresa($conexao,$idProduto,$idEmpresaA);afirmar_produtos($produto!==false&&(int)$produto['ativo']===0,'produto desativado continua existindo',$conexao);
afirmar_produtos(alterar_situacao_produto_empresa($conexao,$idProduto,$idEmpresaA,1),'reativação altera ativo para um',$conexao);
afirmar_produtos(buscar_produto_empresa($conexao,$idProduto,$idEmpresaB)===false&&!alterar_situacao_produto_empresa($conexao,$idProduto,$idEmpresaB,0),'empresa B não consulta ou altera produto da empresa A',$conexao);
afirmar_produtos(!verificar_permissao(['PROPRIETARIO','GERENTE'],['perfil'=>'FUNCIONARIO']),'Funcionário não recebe permissão de gestão',$conexao);
mysqli_rollback($conexao);mysqli_close($conexao);echo "OK: testes de produtos concluídos sem manter dados fictícios.".PHP_EOL;

