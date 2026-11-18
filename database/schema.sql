-- WorkHub - Etapa 2
-- Este script é idempotente para instalação inicial e pode ser aplicado em um
-- banco já inicializado sem remover dados ou volumes.

CREATE TABLE IF NOT EXISTS empresas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_fantasia VARCHAR(150) NOT NULL,
    razao_social VARCHAR(200) NOT NULL,
    cnpj CHAR(14) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_empresas_cnpj (cnpj),
    CONSTRAINT chk_empresas_ativo CHECK (ativo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS administradores_plataforma (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_administradores_plataforma_email (email),
    CONSTRAINT chk_administradores_plataforma_ativo CHECK (ativo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Não armazena e-mail ou endereço IP: a chave é um HMAC calculado pela aplicação.
CREATE TABLE IF NOT EXISTS tentativas_login (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tipo_autenticacao VARCHAR(20) NOT NULL,
    chave CHAR(64) NOT NULL,
    falhas TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_ate DATETIME NULL,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tentativas_login_tipo_chave (tipo_autenticacao, chave),
    KEY idx_tentativas_login_bloqueado_ate (bloqueado_ate),
    CONSTRAINT chk_tentativas_login_tipo CHECK (tipo_autenticacao IN ('empresarial', 'administrativo'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    proprietario_empresa_id INT UNSIGNED GENERATED ALWAYS AS (
        CASE WHEN perfil = 'PROPRIETARIO' THEN empresa_id ELSE NULL END
    ) STORED,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_empresa_id_id (empresa_id, id),
    UNIQUE KEY uq_usuarios_um_proprietario (proprietario_empresa_id),
    KEY idx_usuarios_empresa_ativo (empresa_id, ativo),
    CONSTRAINT fk_usuarios_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas (id),
    CONSTRAINT chk_usuarios_perfil
        CHECK (perfil IN ('PROPRIETARIO', 'GERENTE', 'FUNCIONARIO')),
    CONSTRAINT chk_usuarios_ativo CHECK (ativo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(12, 2) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_produtos_empresa_id_id (empresa_id, id),
    KEY idx_produtos_empresa_ativo (empresa_id, ativo),
    CONSTRAINT fk_produtos_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas (id),
    CONSTRAINT chk_produtos_preco CHECK (preco >= 0),
    CONSTRAINT chk_produtos_ativo CHECK (ativo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_empresa_id_id (empresa_id, id),
    KEY idx_clientes_empresa_ativo (empresa_id, ativo),
    CONSTRAINT fk_clientes_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas (id),
    CONSTRAINT chk_clientes_ativo CHECK (ativo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_entrega DATE NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'RECEBIDO',
    valor_total DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pedidos_empresa_id_id (empresa_id, id),
    KEY idx_pedidos_empresa_status_entrega (empresa_id, status, data_entrega),
    KEY idx_pedidos_cliente (cliente_id),
    KEY idx_pedidos_usuario (usuario_id),
    CONSTRAINT fk_pedidos_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas (id),
    CONSTRAINT fk_pedidos_cliente_mesma_empresa
        FOREIGN KEY (empresa_id, cliente_id) REFERENCES clientes (empresa_id, id),
    CONSTRAINT fk_pedidos_usuario_mesma_empresa
        FOREIGN KEY (empresa_id, usuario_id) REFERENCES usuarios (empresa_id, id),
    CONSTRAINT chk_pedidos_status
        CHECK (status IN ('RECEBIDO', 'EM_PRODUCAO', 'PRONTO', 'ENTREGUE', 'CANCELADO')),
    CONSTRAINT chk_pedidos_valor_total CHECK (valor_total >= 0),
    CONSTRAINT chk_pedidos_ativo CHECK (ativo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pedido_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    quantidade INT UNSIGNED NOT NULL,
    preco_unitario DECIMAL(12, 2) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_itens_pedido_pedido_ativo (pedido_id, ativo),
    KEY idx_itens_pedido_produto (produto_id),
    CONSTRAINT fk_itens_pedido_mesma_empresa
        FOREIGN KEY (empresa_id, pedido_id) REFERENCES pedidos (empresa_id, id),
    CONSTRAINT fk_itens_produto_mesma_empresa
        FOREIGN KEY (empresa_id, produto_id) REFERENCES produtos (empresa_id, id),
    CONSTRAINT chk_itens_pedido_quantidade CHECK (quantidade > 0),
    CONSTRAINT chk_itens_pedido_preco_unitario CHECK (preco_unitario >= 0),
    CONSTRAINT chk_itens_pedido_subtotal CHECK (subtotal >= 0),
    CONSTRAINT chk_itens_pedido_ativo CHECK (ativo IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historico_status (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pedido_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    status_anterior VARCHAR(20) NULL,
    status_novo VARCHAR(20) NOT NULL,
    data_alteracao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_historico_status_pedido_data (pedido_id, data_alteracao),
    KEY idx_historico_status_usuario (usuario_id),
    CONSTRAINT fk_historico_pedido_mesma_empresa
        FOREIGN KEY (empresa_id, pedido_id) REFERENCES pedidos (empresa_id, id),
    CONSTRAINT fk_historico_usuario_mesma_empresa
        FOREIGN KEY (empresa_id, usuario_id) REFERENCES usuarios (empresa_id, id),
    CONSTRAINT chk_historico_status_anterior
        CHECK (
            status_anterior IS NULL
            OR status_anterior IN ('RECEBIDO', 'EM_PRODUCAO', 'PRONTO', 'ENTREGUE', 'CANCELADO')
        ),
    CONSTRAINT chk_historico_status_novo
        CHECK (status_novo IN ('RECEBIDO', 'EM_PRODUCAO', 'PRONTO', 'ENTREGUE', 'CANCELADO'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
