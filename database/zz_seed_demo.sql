-- WorkHub | carga fictícia de apresentação | versão baseada em database/schema.sql
-- EXECUÇÃO AUTOMÁTICA: database/zz_seed_demo.sql é importado pelo MySQL somente
-- quando o volume do banco é inicializado (após docker compose down -v).
-- Senha de todos os 6 usuários: teste123 (bcrypt PHP password_hash).
-- Credenciais ADMIN: usar admin@teste.com / teste123 no .env e bootstrap da app.
-- E-mails empresa1@teste.com e empresa2@teste.com são CONTATOS, não logins.
-- CNPJs fictícios com dígitos verificadores válidos, SEM situação cadastral verificada.
-- NÃO usar esta carga em produção ou com dados reais.

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO empresas (nome_fantasia, razao_social, cnpj, email, telefone) VALUES
  ('Maré Alta Marcenaria', 'Maré Alta Fabricação de Móveis Ltda.', '64182753000148', 'empresa1@teste.com', '12999991001'),
  ('Costa Sul Confecções', 'Costa Sul Confecções e Uniformes Ltda.', '79521634000140', 'empresa2@teste.com', '12999992002');

SET @empresa1 = (SELECT id FROM empresas WHERE cnpj = '64182753000148');
SET @empresa2 = (SELECT id FROM empresas WHERE cnpj = '79521634000140');
SET @senha_teste = '$2y$12$WPEjnvQIOG99FrP/0/RNGO7/c3dYh3fy4YhgDB4MW0GSYknd1CG7m';

INSERT INTO usuarios (empresa_id, nome, email, senha, perfil) VALUES
  (@empresa1, 'Rafael Oliveira', 'proprietario1@teste.com', @senha_teste, 'PROPRIETARIO'),
  (@empresa1, 'Marina Souza', 'gerente1@teste.com', @senha_teste, 'GERENTE'),
  (@empresa1, 'Lucas Pereira', 'funcionario1@teste.com', @senha_teste, 'FUNCIONARIO'),
  (@empresa2, 'Camila Ferreira', 'proprietario2@teste.com', @senha_teste, 'PROPRIETARIO'),
  (@empresa2, 'Bruno Martins', 'gerente2@teste.com', @senha_teste, 'GERENTE'),
  (@empresa2, 'Julia Almeida', 'funcionario2@teste.com', @senha_teste, 'FUNCIONARIO');

INSERT INTO produtos (empresa_id, nome, descricao, preco) VALUES
  (@empresa1, 'Mesa de Escritório Industrial', 'Mesa de madeira e aço, 140 cm', 890.00),
  (@empresa1, 'Cadeira de Madeira', 'Cadeira artesanal em madeira maciça', 310.00),
  (@empresa1, 'Estante Modular', 'Estante com módulos e prateleiras ajustáveis', 1250.00),
  (@empresa1, 'Balcão Comercial', 'Balcão de atendimento sob medida', 1790.00),
  (@empresa1, 'Prateleira Suspensa', 'Prateleira com acabamento natural', 180.00),
  (@empresa1, 'Armário Duas Portas', 'Armário com duas portas e divisórias', 1420.00),
  (@empresa1, 'Mesa de Reunião', 'Mesa de reunião para até oito pessoas', 2350.00),
  (@empresa1, 'Nicho Decorativo', 'Nicho de parede em madeira tratada', 95.00),
  (@empresa2, 'Camiseta Personalizada', 'Camiseta com estampa digital', 69.90),
  (@empresa2, 'Camisa Polo Corporativa', 'Camisa polo com bordado empresarial', 99.90),
  (@empresa2, 'Uniforme Operacional', 'Conjunto para equipe operacional', 149.90),
  (@empresa2, 'Moletom Bordado', 'Moletom em algodão com bordado', 189.90),
  (@empresa2, 'Boné Personalizado', 'Boné com logo bordado', 39.90),
  (@empresa2, 'Avental Profissional', 'Avental resistente para trabalho', 79.90),
  (@empresa2, 'Jaqueta Corta-Vento', 'Jaqueta leve personalizada', 219.90),
  (@empresa2, 'Ecobag Personalizada', 'Sacola de tecido com estampa', 29.90);

INSERT INTO clientes (empresa_id, nome, telefone, email) VALUES
  (@empresa1, 'Ana Beatriz Lima', '12981000001', 'cliente11@example.com'),
  (@empresa1, 'Ricardo Carvalho', '12981000002', 'cliente12@example.com'),
  (@empresa1, 'Fernanda Azevedo', '12981000003', 'cliente13@example.com'),
  (@empresa1, 'Escritório Horizonte', '12981000004', 'cliente14@example.com'),
  (@empresa1, 'Patrícia Nogueira', '12981000005', 'cliente15@example.com'),
  (@empresa1, 'Hotel Vista Mar', '12981000006', 'cliente16@example.com'),
  (@empresa1, 'Felipe Andrade', '12981000007', 'cliente17@example.com'),
  (@empresa1, 'Café Praça Central', '12981000008', 'cliente18@example.com'),
  (@empresa2, 'Academia Litoral', '12982000001', 'cliente21@example.com'),
  (@empresa2, 'Restaurante Porto Azul', '12982000002', 'cliente22@example.com'),
  (@empresa2, 'Bianca Rodrigues', '12982000003', 'cliente23@example.com'),
  (@empresa2, 'Clínica Serra Verde', '12982000004', 'cliente24@example.com'),
  (@empresa2, 'Mercado Bela Vista', '12982000005', 'cliente25@example.com'),
  (@empresa2, 'Eduardo Barbosa', '12982000006', 'cliente26@example.com'),
  (@empresa2, 'Pousada Atlântico', '12982000007', 'cliente27@example.com'),
  (@empresa2, 'Studio Movimento', '12982000008', 'cliente28@example.com');

-- Mapas de índices por empresa (na ordem em que produtos e clientes foram criados).
CREATE TEMPORARY TABLE seed_produtos_ref AS
  SELECT empresa_id, id, preco, ROW_NUMBER() OVER (PARTITION BY empresa_id ORDER BY id) AS numero
  FROM produtos WHERE empresa_id IN (@empresa1, @empresa2);
CREATE TEMPORARY TABLE seed_clientes_ref AS
  SELECT empresa_id, id, ROW_NUMBER() OVER (PARTITION BY empresa_id ORDER BY id) AS numero
  FROM clientes WHERE empresa_id IN (@empresa1, @empresa2);

CREATE TEMPORARY TABLE seed_pedidos (
  codigo SMALLINT PRIMARY KEY,
  empresa_num TINYINT UNSIGNED NOT NULL,
  cliente_num TINYINT UNSIGNED NOT NULL,
  perfil VARCHAR(20) NOT NULL,
  produto1_num TINYINT UNSIGNED NOT NULL,
  quantidade1 SMALLINT UNSIGNED NOT NULL,
  produto2_num TINYINT UNSIGNED NULL,
  quantidade2 SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  dias_atras SMALLINT NOT NULL,
  prazo_dias SMALLINT NOT NULL,
  status VARCHAR(20) NOT NULL
) ENGINE=MEMORY;

INSERT INTO seed_pedidos
  (codigo, empresa_num, cliente_num, perfil, produto1_num, quantidade1,
   produto2_num, quantidade2, dias_atras, prazo_dias, status) VALUES
  (101, 1, 1, 'PROPRIETARIO', 1, 2, 2, 3, 0, 8, 'RECEBIDO'),
  (102, 1, 2, 'GERENTE', 3, 1, 5, 2, 1, 5, 'RECEBIDO'),
  (103, 1, 3, 'FUNCIONARIO', 6, 1, 8, 4, 1, 7, 'EM_PRODUCAO'),
  (104, 1, 4, 'GERENTE', 4, 1, 5, 3, 2, 2, 'EM_PRODUCAO'),
  (105, 1, 5, 'FUNCIONARIO', 7, 1, NULL, 0, 3, 1, 'PRONTO'),
  (106, 1, 6, 'PROPRIETARIO', 2, 6, NULL, 0, 4, 0, 'PRONTO'),
  (107, 1, 7, 'GERENTE', 1, 1, 2, 4, 5, -1, 'EM_PRODUCAO'),
  (108, 1, 8, 'FUNCIONARIO', 3, 2, NULL, 0, 6, -4, 'EM_PRODUCAO'),
  (109, 1, 1, 'PROPRIETARIO', 5, 8, NULL, 0, 7, -2, 'ENTREGUE'),
  (110, 1, 2, 'GERENTE', 6, 1, 8, 3, 8, -3, 'ENTREGUE'),
  (111, 1, 3, 'PROPRIETARIO', 7, 1, NULL, 0, 9, -5, 'ENTREGUE'),
  (112, 1, 4, 'GERENTE', 2, 10, NULL, 0, 10, 10, 'CANCELADO'),
  (113, 1, 5, 'PROPRIETARIO', 4, 1, 8, 5, 11, 3, 'RECEBIDO'),
  (114, 1, 6, 'FUNCIONARIO', 1, 3, NULL, 0, 12, 1, 'EM_PRODUCAO'),
  (115, 1, 7, 'PROPRIETARIO', 5, 12, 8, 6, 13, 2, 'PRONTO'),
  (116, 1, 8, 'GERENTE', 6, 2, NULL, 0, 14, -7, 'ENTREGUE'),
  (117, 1, 1, 'FUNCIONARIO', 8, 15, NULL, 0, 15, 4, 'RECEBIDO'),
  (118, 1, 2, 'PROPRIETARIO', 3, 2, 5, 5, 16, -3, 'CANCELADO'),
  (119, 1, 3, 'GERENTE', 7, 1, 2, 8, 17, 6, 'EM_PRODUCAO'),
  (120, 1, 4, 'PROPRIETARIO', 4, 2, NULL, 0, 18, -2, 'ENTREGUE'),
  (201, 2, 1, 'PROPRIETARIO', 1, 30, 5, 10, 0, 6, 'RECEBIDO'),
  (202, 2, 2, 'GERENTE', 3, 15, 6, 12, 1, 2, 'EM_PRODUCAO'),
  (203, 2, 3, 'FUNCIONARIO', 4, 8, 2, 12, 2, 4, 'EM_PRODUCAO'),
  (204, 2, 4, 'GERENTE', 7, 6, NULL, 0, 3, 1, 'PRONTO'),
  (205, 2, 5, 'PROPRIETARIO', 1, 60, NULL, 0, 4, 0, 'PRONTO'),
  (206, 2, 6, 'FUNCIONARIO', 5, 50, 8, 30, 5, -1, 'EM_PRODUCAO'),
  (207, 2, 7, 'GERENTE', 2, 12, 3, 10, 6, -3, 'EM_PRODUCAO'),
  (208, 2, 8, 'PROPRIETARIO', 6, 20, NULL, 0, 7, -2, 'ENTREGUE'),
  (209, 2, 1, 'FUNCIONARIO', 1, 24, 8, 12, 8, -4, 'ENTREGUE'),
  (210, 2, 2, 'GERENTE', 7, 3, 5, 12, 9, -3, 'ENTREGUE'),
  (211, 2, 3, 'PROPRIETARIO', 4, 10, NULL, 0, 10, 9, 'CANCELADO'),
  (212, 2, 4, 'FUNCIONARIO', 3, 8, 2, 16, 11, 5, 'RECEBIDO'),
  (213, 2, 5, 'GERENTE', 8, 100, NULL, 0, 12, 7, 'RECEBIDO'),
  (214, 2, 6, 'PROPRIETARIO', 2, 20, 5, 25, 13, 2, 'PRONTO'),
  (215, 2, 7, 'FUNCIONARIO', 6, 15, 1, 5, 14, 1, 'EM_PRODUCAO'),
  (216, 2, 8, 'GERENTE', 3, 12, NULL, 0, 15, -5, 'ENTREGUE'),
  (217, 2, 1, 'FUNCIONARIO', 7, 4, 8, 20, 16, 8, 'EM_PRODUCAO'),
  (218, 2, 2, 'PROPRIETARIO', 1, 18, 4, 5, 17, -2, 'CANCELADO'),
  (219, 2, 3, 'GERENTE', 5, 40, NULL, 0, 18, -6, 'ENTREGUE'),
  (220, 2, 4, 'PROPRIETARIO', 2, 10, 6, 12, 19, 12, 'RECEBIDO');

-- Uma única âncora temporal garante a correspondência dos pedidos e itens.
SET @seed_agora = NOW();

INSERT INTO pedidos (empresa_id, cliente_id, usuario_id, data_criacao, data_entrega, status, valor_total) 
SELECT e.id, c.id, u.id,
       TIMESTAMPADD(MINUTE, -s.codigo, TIMESTAMPADD(DAY, -s.dias_atras, @seed_agora)),
       DATE_ADD(CURDATE(), INTERVAL s.prazo_dias DAY), s.status,
       p1.preco * s.quantidade1 + COALESCE(p2.preco * s.quantidade2, 0)
FROM seed_pedidos s
JOIN empresas e ON e.id = IF(s.empresa_num = 1, @empresa1, @empresa2)
JOIN seed_clientes_ref c ON c.empresa_id = e.id AND c.numero = s.cliente_num
JOIN usuarios u ON u.empresa_id = e.id AND u.perfil = s.perfil
JOIN seed_produtos_ref p1 ON p1.empresa_id = e.id AND p1.numero = s.produto1_num
LEFT JOIN seed_produtos_ref p2 ON p2.empresa_id = e.id AND p2.numero = s.produto2_num
ORDER BY s.codigo;

CREATE TEMPORARY TABLE seed_pedido_refs AS
SELECT s.codigo, p.id AS pedido_id, p.empresa_id, p.usuario_id, p.data_criacao,
       p.status, s.produto1_num, s.quantidade1, s.produto2_num, s.quantidade2
FROM seed_pedidos s
JOIN pedidos p ON p.empresa_id = IF(s.empresa_num = 1, @empresa1, @empresa2)
 AND p.data_criacao = TIMESTAMPADD(MINUTE, -s.codigo, TIMESTAMPADD(DAY, -s.dias_atras, @seed_agora));

-- Itens: preços e subtotais derivam do cadastro de cada produto.
INSERT INTO itens_pedido (pedido_id, produto_id, empresa_id, quantidade, preco_unitario, subtotal)
SELECT r.pedido_id, p.id, r.empresa_id, r.quantidade1, p.preco, r.quantidade1 * p.preco
FROM seed_pedido_refs r
JOIN seed_produtos_ref p ON p.empresa_id = r.empresa_id AND p.numero = r.produto1_num;

INSERT INTO itens_pedido (pedido_id, produto_id, empresa_id, quantidade, preco_unitario, subtotal)
SELECT r.pedido_id, p.id, r.empresa_id, r.quantidade2, p.preco, r.quantidade2 * p.preco
FROM seed_pedido_refs r
JOIN seed_produtos_ref p ON p.empresa_id = r.empresa_id AND p.numero = r.produto2_num
WHERE r.produto2_num IS NOT NULL;

-- Histórico coerente: RECEBIDO -> EM_PRODUCAO -> PRONTO -> ENTREGUE
-- ou RECEBIDO -> CANCELADO, com horários posteriores à abertura do pedido.
INSERT INTO historico_status (pedido_id, usuario_id, empresa_id, status_anterior, status_novo, data_alteracao)
SELECT pedido_id, usuario_id, empresa_id, NULL, 'RECEBIDO', data_criacao
FROM seed_pedido_refs;

INSERT INTO historico_status (pedido_id, usuario_id, empresa_id, status_anterior, status_novo, data_alteracao)
SELECT pedido_id, usuario_id, empresa_id, 'RECEBIDO', 'EM_PRODUCAO', TIMESTAMPADD(HOUR, 1, data_criacao)
FROM seed_pedido_refs WHERE status IN ('EM_PRODUCAO','PRONTO','ENTREGUE');

INSERT INTO historico_status (pedido_id, usuario_id, empresa_id, status_anterior, status_novo, data_alteracao)
SELECT pedido_id, usuario_id, empresa_id, 'EM_PRODUCAO', 'PRONTO', TIMESTAMPADD(HOUR, 2, data_criacao)
FROM seed_pedido_refs WHERE status IN ('PRONTO','ENTREGUE');

INSERT INTO historico_status (pedido_id, usuario_id, empresa_id, status_anterior, status_novo, data_alteracao)
SELECT pedido_id, usuario_id, empresa_id, 'PRONTO', 'ENTREGUE', TIMESTAMPADD(HOUR, 3, data_criacao)
FROM seed_pedido_refs WHERE status = 'ENTREGUE';

INSERT INTO historico_status (pedido_id, usuario_id, empresa_id, status_anterior, status_novo, data_alteracao)
SELECT pedido_id, usuario_id, empresa_id, 'RECEBIDO', 'CANCELADO', TIMESTAMPADD(HOUR, 1, data_criacao)
FROM seed_pedido_refs WHERE status = 'CANCELADO';

COMMIT;
DROP TEMPORARY TABLE seed_pedido_refs;
DROP TEMPORARY TABLE seed_pedidos;
DROP TEMPORARY TABLE seed_clientes_ref;
DROP TEMPORARY TABLE seed_produtos_ref;

-- Resumo da carga para conferência nos logs do MySQL.
SELECT e.nome_fantasia AS empresa,
       (SELECT COUNT(*) FROM usuarios u WHERE u.empresa_id = e.id) AS usuarios,
       (SELECT COUNT(*) FROM produtos pr WHERE pr.empresa_id = e.id) AS produtos,
       (SELECT COUNT(*) FROM clientes c WHERE c.empresa_id = e.id) AS clientes,
       (SELECT COUNT(*) FROM pedidos p WHERE p.empresa_id = e.id) AS pedidos
FROM empresas e
WHERE e.id IN (@empresa1, @empresa2)
ORDER BY e.id;
