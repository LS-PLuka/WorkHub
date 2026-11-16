<?php
require __DIR__.'/../controller/cliente_controller.php';
function afirmar_clientes($c,$m,$x){if(!$c){mysqli_rollback($x);mysqli_close($x);fwrite(STDERR,"FALHOU: {$m}".PHP_EOL);exit(1);}echo "OK: {$m}".PHP_EOL;}
$x=conectar_banco_dados();if($x===false||!mysqli_begin_transaction($x))exit(1);$s=bin2hex(random_bytes(5));
mysqli_query($x,"INSERT INTO empresas (nome_fantasia,razao_social,cnpj,email,telefone) VALUES ('Clientes A','Clientes A LTDA','04252011000110','a.$s@workhub.test','11999999999')");$a=mysqli_insert_id($x);mysqli_query($x,"INSERT INTO empresas (nome_fantasia,razao_social,cnpj,email,telefone) VALUES ('Clientes B','Clientes B LTDA','40688134000161','b.$s@workhub.test','11999999998')");$b=mysqli_insert_id($x);
afirmar_clientes(count(validar_dados_cliente(['nome'=>'','telefone'=>'123','email'=>'invalido']))>0,'nome, telefone e e-mail inválidos são rejeitados',$x);
afirmar_clientes(cadastrar_cliente($x,$a,'Cliente gerente','(11) 99999-9999',"cliente.$s@workhub.test"),'Proprietário ou Gerente cadastra cliente',$x);$id=mysqli_insert_id($x);
afirmar_clientes(buscar_cliente_empresa($x,$id,$a)!==false,'cliente pertence à empresa correta',$x);
afirmar_clientes(atualizar_cliente_empresa($x,$id,$a,'Cliente editado','11999999999',null),'cliente é editado',$x);
afirmar_clientes(count(listar_clientes_empresa($x,$a,'editado'))===1,'busca por nome funciona',$x);
afirmar_clientes(alterar_situacao_cliente_empresa($x,$id,$a,0),'desativação altera ativo para zero',$x);$cl=buscar_cliente_empresa($x,$id,$a);afirmar_clientes($cl!==false&&(int)$cl['ativo']===0,'cliente desativado continua armazenado',$x);
afirmar_clientes(alterar_situacao_cliente_empresa($x,$id,$a,1),'reativação altera ativo para um',$x);
afirmar_clientes(buscar_cliente_empresa($x,$id,$b)===false&&!alterar_situacao_cliente_empresa($x,$id,$b,0),'empresa B não consulta nem altera cliente da empresa A',$x);
afirmar_clientes(!verificar_permissao(['PROPRIETARIO','GERENTE'],['perfil'=>'FUNCIONARIO']),'Funcionário não recebe permissão administrativa',$x);
mysqli_rollback($x);mysqli_close($x);echo "OK: testes de clientes concluídos sem manter dados fictícios.".PHP_EOL;
